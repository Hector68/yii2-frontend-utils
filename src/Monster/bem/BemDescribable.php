<?php

namespace DevGroup\Frontend\Monster\bem;

use Yii;
use yii\base\Object;

/**
 * Class BemDescribable is a base class for objects in scss(bem entities, variables)
 * that can be described with common directives in SCSS comments.
 *
 * @package DevGroup\Frontend\Monster\bem
 */
class BemDescribable extends Object
{
    /** @var string Description of object -- @desc directive */
    public $description = '';

    /** @var MonsterGroup[] Array of references to groups that this object can affect - @groups directive */
    public $groups = [];

    public function mapCommentsToProperties()
    {
        return [
            'desc' => 'description',
            'group' => 'groupMapper',
        ];
    }

    public function processComments($comments)
    {
        $mapping = static::mapCommentsToProperties();
        preg_match_all('#@(?P<key>[^ \t]*)[ \t]+(?P<value>.*)$#muS', $comments, $m, PREG_SET_ORDER);
        foreach ($m as $match) {
            if (isset($mapping[$match['key']])) {
                $match['key'] = $mapping[$match['key']];
            }
            if ($this->canSetProperty($match['key'])) {
                $this->{$match['key']} = $match['value'];
            }
        }
    }

    public function setGroupMapper($value)
    {
        $groupNames = explode(',', $value);
        foreach ($groupNames as $name) {
            $name = trim($name);
            $instance = MonsterGroup::instance($name);
            if ($instance === null) {
                $instance = new MonsterGroup([
                    'name' => $name,
                ]);
            }
            MonsterGroup::$globalIdentityMap[$name] = &$instance;
            $this->groups[] = &$instance;
        }
    }
}
