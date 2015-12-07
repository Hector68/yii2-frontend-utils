<?php

namespace DevGroup\Frontend\assets;

use yii\web\AssetBundle;

class FrontendMonster extends AssetBundle
{
    public $sourcePath = '@bower/frontend-monster/dist';

    public function init ()
    {
        parent::init();

        $this->css = [
            YII_ENV_DEV ? 'styles/main.css' : 'styles/main.min.css',
        ];

        if (YII_ENV_DEV) {
            $this->js = [
                'scripts/app.js',
                'scripts/libs.js',
            ];
        } else {
            $this->js = [
                'scripts/app.min.js',
                'scripts/libs.min.js',
            ];
        }

    }
}