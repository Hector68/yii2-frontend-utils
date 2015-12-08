<?php

namespace DevGroup\Frontend\controllers;

use yii\base\ViewEvent;
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