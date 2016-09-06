<?php

namespace DevGroup\Frontend\helpers;

use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class RequestHelper
{
    public static function isJsonRequested()
    {
        return array_key_exists('application/json', Yii::$app->request->acceptableContentTypes)
        || array_key_exists('application/javascript', Yii::$app->request->acceptableContentTypes);
    }

    public static function isXmlRequested()
    {
        return array_key_exists('text/xml', Yii::$app->request->acceptableContentTypes)
        || array_key_exists('application/xml', Yii::$app->request->acceptableContentTypes);
    }

    /**
     * Throws exception if non ajax request
     * @throws \yii\web\BadRequestHttpException
     */
    public static function allowAjaxOnly()
    {
        if (Yii::$app->request->isAjax === false) {
            throw new BadRequestHttpException();
        }
    }

    /**
     * Throws exception if non-json requested, sets format to json|jsonp
     * @param string $jsonpAttribute
     *
     * @throws \yii\web\BadRequestHttpException
     */
    public static function allowOnlyJsonRequest($jsonpAttribute = 'callback')
    {
        if (static::isJsonRequested() === false) {
            throw new BadRequestHttpException();
        }
        Yii::$app->response->format =
            Yii::$app->request->get($jsonpAttribute)
                ? Response::FORMAT_JSONP
                : Response::FORMAT_JSON;
    }
}
