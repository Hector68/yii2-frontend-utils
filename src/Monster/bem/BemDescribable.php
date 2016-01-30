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
class BemDescribable extends Object implements \JsonSerializable
{
    /** @var string Description of object -- @desc directive */
    public $description = '';

    /** @var MonsterGroup[] Array of references to groups that this object can affect - @groups directive */
    public $groups = [];

    public $serializationGroupMapper = [];

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
        $groupNames = is_array($value) === true ? $value : explode(',', $value);
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

    public function getGroupMapper()
    {
        foreach ($this->groups as $group) {
            yield $group->name;
        }
    }

    /**
     * Array of values for json serialization
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'class' => $this->className(),
            'description' => $this->description,
            'groupMapper' => $this->groupMapper,
        ];
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        $this->serializationGroupMapper = iterator_to_array($this->getGroupMapper());

        return [
            'description',
            'serializationGroupMapper',
        ];
    }

    public function __wakeup()
    {
        $this->setGroupMapper($this->serializationGroupMapper);
    }

}
