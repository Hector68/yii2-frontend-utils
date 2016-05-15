<?php

namespace DevGroup\Frontend\Universal;

use yii;

abstract class UniversalAction
{
    /**
     * @param ActionData $actionData
     *
     * @return void
     */
    abstract public function run(&$actionData);
}
