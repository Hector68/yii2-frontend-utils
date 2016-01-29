<?php

namespace DevGroup\Frontend\Monster\materials;

use DevGroup\Frontend\monster\MonsterBlockWidget;
use Yii;

class BaseMaterial extends MonsterBlockWidget
{
    public $params = [];

    /** @inheritdoc */
    public function init()
    {
        // set default bemjson to bemjson/CLASS.json
        $className = explode('\\', $this->className());
        $shortClassName = end($className);
        $this->bemjson = __DIR__ . '/bemjson/' . $shortClassName . '.json';
        parent::init();
    }

    /**
     * Actual widget rendering function you should implement
     *
     * @return string
     */
    public function produceParams()
    {
        $params = $this->params;
        $params['__CLASS__'] = $this->className();
        return $params;
    }
}
