<?php

namespace DevGroup\Frontend\Universal;

use yii;

class ActionData
{
    public $entities = [];
    public $result = [];

    public $viewFile;

    public $content;

    public $cacheKey = '';
    public $cacheLifetime = false;
    public $cacheDependencies = [];
    
    public function __construct($viewFile)
    {
        $this->entities = Yii::$app->request->get('entities', []);
        $this->viewFile = $viewFile;
    }
}
