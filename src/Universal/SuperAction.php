<?php

namespace DevGroup\Frontend\Universal;

use yii;
use yii\web\Response;

class SuperAction extends yii\base\Action
{
    public $actions = [];

    //@todo Implement cache as it is in MonsterContent
    public $cacheKey = '';
    public $cacheLifetime = false;
    public $cacheDependencies = [];

    public $jsonpAttribute = 'callback';
    public $enableXml = false;
    public $view;
    
    public function run()
    {
        $actionData = new ActionData($this->view);
        $actionData->controller = &$this->controller;

        foreach ($this->actions as $index => $action) {
            $profileKey = "SuperAction: $index";
            Yii::beginProfile($profileKey);
            if (is_array($action)) {
                $action = Yii::createObject($action);
            }
            /** @var UniversalAction $action */
            $action->run($actionData);
            Yii::endProfile($profileKey);
        }

        $result = null;
        Yii::beginProfile('SuperAction: render');
        if ($this->isJsonRequested()) {
            Yii::$app->response->format =
                Yii::$app->request->get($this->jsonpAttribute)
                    ? Response::FORMAT_JSONP
                    : Response::FORMAT_JSON;

            $result = $actionData->result;
        } elseif ($this->enableXml && $this->isXmlRequested()) {
            Yii::$app->response->format = Response::FORMAT_XML;

            $result = $actionData->result;
        } elseif ($actionData->content !== null) {
            $result = $this->controller->renderContent($actionData->content);
        } else {
            if ($actionData->viewFile === null) {
                $actionData->viewFile = $this->id;
            }
            $result = $this->controller->render($actionData->viewFile, $actionData->result);
        }
        Yii::endProfile('SuperAction: render');
        return $result;
    }

    protected function isJsonRequested()
    {
        return in_array('application/json', Yii::$app->request->acceptableContentTypes, true)
            || in_array('application/javascript', Yii::$app->request->acceptableContentTypes, true);
    }

    protected function isXmlRequested()
    {
        return in_array('text/xml', Yii::$app->request->acceptableContentTypes, true)
            || in_array('application/xml', Yii::$app->request->acceptableContentTypes, true);
    }
}
