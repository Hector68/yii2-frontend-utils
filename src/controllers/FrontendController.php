<?php

namespace DevGroup\Frontend\controllers;

use Yii;
use yii\base\ViewEvent;
use yii\helpers\Url;
use yii\web\Controller;

class FrontendController extends Controller
{
    const EVENT_BEFORE_RENDER = 'controller-before-render';

    public function render($view, $params = [])
    {
        $event = new ViewEvent();
        $event->viewFile = $view;
        $event->params = $params;

        $this->trigger(self::EVENT_BEFORE_RENDER, $event);

        if (Yii::$app->request->get('returnUrl')) {
            $url = [
                '/'.ltrim(Yii::$app->requestedRoute),
            ];
            $urlParams = Yii::$app->request->get();
            unset($urlParams['returnUrl']);
            foreach ($urlParams as $key => $value) {
                $url[$key] = $value;
            }

            $this->view->registerLinkTag([
                'rel' => 'canonical',
                'href' => Url::to($url, true),
            ]);
        }
        //! @todo Check $event->isValid
        return parent::render($view, $params);
    }

    public function renderAjax($view, $params = [])
    {
        $event = new ViewEvent();
        $event->viewFile = $view;
        $event->params = $params;

        $this->trigger(self::EVENT_BEFORE_RENDER, $event);
        //! @todo Check $event->isValid
        return parent::renderAjax($view, $params);
    }
}