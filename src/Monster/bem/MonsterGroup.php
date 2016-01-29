<?php

namespace DevGroup\Frontend\Monster\bem;

use Yii;
use yii\base\Object;

/**
 * Class MonsterGroup is a representation of virtual group for monster bem entities.
 *
 * @package DevGroup\Frontend\Monster\bem
 */
class MonsterGroup extends Object
{
    /** @var string Name of group */
    public $name = '';

    /**
     * @var bool If this group global. For group named 'global' is set automatically.
     */
    public $isGlobal = false;

    /**
     * @var bool If this group is used for customization of look'n'feel of design.
     *           For group named 'custom' is set automatically.
     */
    public $isCustomization = false;

    /**
     * @var MonsterGroup[] Global entity map for all MonsterGroup objects. Array keys - group name.
     */
    public static $globalIdentityMap = [];

    public function init()
    {
        parent::init();
        $this->isGlobal = $this->name === 'global';
        $this->isCustomization = $this->name === 'custom';
    }

    /**
     * Returns MonsterGroup instance on name if exists in global identity map
     * @param string $name
     *
     * @return \DevGroup\Frontend\Monster\bem\MonsterGroup|null
     */
    public static function instance($name)
    {
        if (isset(static::$globalIdentityMap[$name])) {
            return static::$globalIdentityMap[$name];
        }
        return null;
    }
}
