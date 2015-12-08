<?php

namespace DevGroup\Frontend\assets;

use yii\web\AssetBundle;

class FrontendMonster extends AssetBundle
{
    public $sourcePath = '@bower/frontend-monster/dist';

    public $depends = [
        'yii\web\JqueryAsset',
        // !!! This is a dependency to CurrentTranslation !!!
        'DevGroup\Polyglot\CurrentTranslation',
    ];

    public function init ()
    {
        parent::init();

        if (YII_ENV_DEV) {
            $this->css = [
                'styles/main.css',
                'styles/libs.css',
            ];
            $this->js = [
                'scripts/app.js',
                'scripts/libs.js',
            ];
        } else {
            $this->css = [
                'styles/main.min.css',
                'styles/libs.min.css',
            ];
            $this->js = [
                'scripts/app.min.js',
                'scripts/libs.min.js',
            ];
        }
    }

}