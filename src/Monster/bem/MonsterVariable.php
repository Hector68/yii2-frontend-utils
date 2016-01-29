<?php

namespace DevGroup\Frontend\Monster\bem;

use Yii;

/**
 * Class MonsterVariable is a representation of SCSS variable that affects bem entities or look'n'feel of design.
 *
 * @package DevGroup\Frontend\Monster\bem
 */
class MonsterVariable extends BemDescribable
{
    /** @var string Name of variable */
    public $name = '';

    /** @var mixed Value of this variable */
    public $value = self::VALUE_UNDEFINED;

    /** Special possible value for variable meaning we haven't found declaration. */
    const VALUE_UNDEFINED = '¯\_(ツ)_/¯';

    /** Measure in some units, ie. `2px` */
    const TYPE_MEASURE = 'measure';
    /** Double measure in some units, ie. `2px 10px` */
    const TYPE_MEASURE_2 = 'measure-2';
    /** Triple measure in some units, ie. `2px 10px 7px` */
    const TYPE_MEASURE_3 = 'measure-3';
    /** Quad measure in some units, ie. `2px 10px 7px 0` */
    const TYPE_MEASURE_4 = 'measure-4';

    /** Color - will be rendered in rgba format */
    const TYPE_COLOR = 'color';

    /** Some string, ie font name */
    const TYPE_STRING = 'string';

    /** Some complex expression, ie. `$var * 2` */
    const TYPE_EXPRESSION = 'expression';

    /** URL expression, ie. `url:(http://some.com/img.gif)` */
    const TYPE_URL = 'url';

    /**
     * @var string Type of this variable in CSS
     *             Is set in SCSS comment with `@var-type string` directive.
     */
    public $type = self::TYPE_STRING;

    /**
     * @var MonsterVariable[] Global entity map for all MonsterVariable objects. Array keys - variable name.
     *                        Therefore we can't allow local scss variables have the same name as global.
     */
    public static $globalIdentityMap = [];

    /**
     * Returns MonsterVariable instance on name if exists in global identity map
     * @param string $name
     *
     * @return \DevGroup\Frontend\Monster\bem\MonsterVariable|null
     */
    public static function instance($name)
    {
        if (isset(static::$globalIdentityMap[$name])) {
            return static::$globalIdentityMap[$name];
        }
        return null;
    }

    /**
     * @param BemEntity $entity
     */
    public static function processEntityVariables(&$entity)
    {
        $variables = static::retrieveVariables($entity->innerExclusive, $entity->bemSelector);
        foreach ($variables as &$var) {
            $entity->variablesUsed[] = $var;
        }
    }

    /**
     * Returns monster variables in content
     * @param string $content
     * @param string $bemSelector Bem selector for loggin errors
     *
     * @return MonsterVariable[]
     */
    public static function retrieveVariables($content, $bemSelector = '¯\_(ツ)_/¯')
    {
        $variables = [];
        // find defined and used variables https://regex101.com/r/zE6yK9/2
        $definedRegexp = '#(?<comment>^\s*(?U:\/\/[^\r\n]*[\r\n]|\/\*.*\*\/\s)*+)' .
            '^\s*\$(?<name>[^\s:]*)(\s*:\s*[\'"]?(?<value>[^\'";\r\n]*)[\'"]?);\s*$#musS';

        $content = preg_replace_callback(
            $definedRegexp,
            function ($match) use ($variables, $bemSelector) {
                static::processMatch($match, $variables, $bemSelector);
                return '';
            },
            $content
        );

        if (trim($content) !== '') {
            preg_match_all(
                '#\$(?<name>[\w\d\-_]+)#uS',
                $content,
                $usedVariables,
                PREG_SET_ORDER
            );

            foreach ($usedVariables as $match) {
                if (isset($match['name'])) {
                    static::processMatch($match, $variables, $bemSelector);
                }
            }
        }

        return $variables;
    }

    public static function processMatch($match, &$variables, $bemSelector = '')
    {
        $variable = static::instance($match['name']);
        if ($variable === null) {
            $variable = new static([
                'name' => trim($match['name']),
            ]);
            self::$globalIdentityMap[$variable->name] = &$variable;
        }

        if (isset($match['value'])) {
            if ($variable->value !== static::VALUE_UNDEFINED) {
                Yii::warning("SCSS variable {$variable->name} redefined in BEM Entity: " . $bemSelector);
            }
            $variable->value = trim($match['value']);
        }
        if (isset($match['comment'])) {
            $variable->processComments($match['comment']);
        }

        $variables[] = &$variable;
    }
}
