<?php

namespace DevGroup\Frontend\Monster\bem;

use Yii;
use yii\helpers\ArrayHelper;

class BemElement extends BemEntity
{
    /** @var BemModifier[] Modifiers for this BEM Element */
    public $modifiers = [];

    /** @inheritdoc */
    public function jsonSerialize()
    {
        return ArrayHelper::merge(
            parent::jsonSerialize(),
            [
                'modifiers' => $this->modifiers,
            ]
        );
    }

    /** @inheritdoc */
    public function __sleep()
    {
        return ArrayHelper::merge(
            parent::__sleep(),
            [
                'modifiers',
            ]
        );
    }
}
