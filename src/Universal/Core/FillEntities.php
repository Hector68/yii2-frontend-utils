<?php

namespace DevGroup\Frontend\Universal\Core;

use DevGroup\Frontend\Universal\ActionData;
use DevGroup\Frontend\Universal\UniversalAction;
use DevGroup\TagDependencyHelper\TagDependencyTrait;
use yii;
use yii\helpers\ArrayHelper;

class FillEntities extends UniversalAction
{
    /**
     * @var array Redefine `loadModel` parameters on each model class name
     */
    public $entitiesLoadSettings = [
//        'app\models\Page' => [
//            'useCache' => true,
//            'cacheLifetime' => 86400,
//            'throwException' => false,
//            'useIdentityMap' => true,
//        ]
    ];

    public $entitiesMapping = [
        'app\models\Page' => 'pages',
    ];

    public $convertOneModelToArray = false;
    public $nullOnEmptyArray = false;

    const FILL_ENTITIES_ADD = 'fill-entities-add';
    const FILL_ENTITIES_REPLACE = 'fill-entities-replace';
    const FILL_RESULT = 'fill-result';

    /**
     * @var string Strategy of filling entities
     */
    public $fill = self::FILL_ENTITIES_REPLACE;
    /**
     * @param ActionData $actionData
     *
     * @return void
     */
    public function run(&$actionData)
    {
        foreach ($actionData->entities as $className => $ids) {
            if (class_exists($className)) {
                $ids = (array) $ids;
                $models = [];
                $mapping = ArrayHelper::getValue($this->entitiesMapping, $className, $className);

                $modelLoadSettings = ArrayHelper::merge(
                    [
                        'useCache' => true,
                        'cacheLifetime' => 86400,
                        'throwException' => false,
                        'useIdentityMap' => true,
                    ],
                    ArrayHelper::getValue($this->entitiesLoadSettings, $className, [])
                );

                foreach ($ids as $id) {
                    /** @var TagDependencyTrait $className */
                    $models[$id] = $className::loadModel(
                        $id,
                        false,
                        $modelLoadSettings['useCache'],
                        $modelLoadSettings['cacheLifetime'],
                        $modelLoadSettings['throwException'],
                        $modelLoadSettings['useIdentityMap']
                    );
                }
                if ($this->convertOneModelToArray === false && count($models) === 1) {
                    $models = reset($models);
                }
                if ($this->nullOnEmptyArray === true && count($models) === 0) {
                    $models = null;
                }
                /** @var string $className */
                // replace with models
                switch ($this->fill) {
                    case static::FILL_ENTITIES_ADD:
                        $actionData->entities[$mapping] = $models;
                        break;
                    case static::FILL_ENTITIES_REPLACE:
                        unset($actionData->entities[$className]);
                        $actionData->entities[$mapping] = $models;
                        break;
                    case static::FILL_RESULT:
                    default:
                        $actionData->result[$mapping] = $models;
                        break;
                }
            }
        }
    }
}
