<?php

namespace DevGroup\Frontend\traits;

use yii;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\Response;

trait ContentNegotiator
{
    public $requestJson;
    private $negotiatorProcessed = false;
    public $negotiatorFormats = [
        'text/html' => Response::FORMAT_HTML,
        'application/json' => Response::FORMAT_JSON,
        'text/javascript' => Response::FORMAT_JSONP,
        'text/xml' => Response::FORMAT_XML,
    ];

    /**
     * @todo Add checking for correct rights
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function negotiate()
    {
        if ($this->negotiatorProcessed === true) {
            return;
        }
        /** @var ContentNegotiator $negotiator */
        $negotiator = Yii::createObject([
            'class' => yii\filters\ContentNegotiator::class,
            'formats' => $this->negotiatorFormats,
//            @todo implement retrieving languages if yii2-multilingual enabled
//            'languages' => [],
        ]);
        $negotiator->negotiate();
        $format = Yii::$app->response->format;
        if ($format === Response::FORMAT_JSON || $format === Response::FORMAT_JSONP) {
            $rawBody = Yii::$app->request->rawBody;
            if ($rawBody) {
                // try to json-decode
                try {
                    $this->requestJson = Json::decode($rawBody);
                } catch (\Exception $e) {
                    throw new BadRequestHttpException;
                }
            }
        } elseif ($rawBody = Yii::$app->request->post('__json')) {
            try {
                $this->requestJson = Json::decode($rawBody);
            } catch (\Exception $e) {
                throw new BadRequestHttpException;
            }
        }
        $this->negotiatorProcessed = true;
    }
}
