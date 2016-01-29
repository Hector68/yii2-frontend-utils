<?php

namespace DevGroup\Frontend\Monster\bem;

use Yii;

/**
 * Class BemBlock represents BEM block.
 * In our convention each logic block is stored in separate scss file.
 * Each logic block is described through BEMTREE in separate bem json file.
 *
 * @package DevGroup\Frontend\Monster\bem
 */
class BemBlock extends BemEntity
{
    /** @var MonsterGroup[] Array of references to groups that this bem entity exists in */
    public $groups = [];

    /**
     * @var string Path to base bemjson file.
     *             Is set in SCSS comment with `@bemjson path/to/bemjson.json` directive.
     *             For `BaseMaterial` widget bemjson path is relative to
     *             `@vendor/devgroup/yii2-frontend-utils/src/monster/materials/`.
     */
    public $bemJson = '';

    /**
     * @var string Class name of widget that renders this BEM block.
     *             Default is `DevGroup\Frontend\Monster\materials\BaseMaterial`.
     *             Widget must extend `DevGroup\Frontend\Monster\MonsterBlockWidget`.
     *             Is set in SCSS comment with `@widget Namespace\Class\Name` directive.
     */
    public $widget = 'DevGroup\Frontend\Monster\materials\BaseMaterial';

    /**
     * @var string Serialized json config for widget.
     *             This options are used as base widget configuration and overrides widget defaults.
     *             Context-specific options are merged with this options.
     *             Is set in SCSS comment with `@widget-config {"foo":"bar"}` directive.
     *             WARNING! Directive value must be set on single line as we don't support multi-line values currently.
     */
    public $widgetConfig = '[]';

    /** @var BemModifier[] Modifiers for this BEM Block */
    public $modifiers = [];

    /** @var BemElement[] Elements for this BEM Block */
    public $elements = [];
}
