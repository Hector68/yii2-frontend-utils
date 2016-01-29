<?php

namespace DevGroup\Frontend\Monster\bem;

use Yii;
use yii\base\Component;

class Annotator extends Component
{
    /**
     * @var array List of processed/processing files for not getting into infinite loops.
     *            Filename is stored in key of an array.
     */
    public $processedFiles = [];

    // https://regex101.com/r/xO4iB5/3
    const SCSS_REGEXP = '#(?U:(?<comment>(^\s*\/\/[^\r\n]*[\r\n]|^\s*\/\*.*\*\/[\r\n])*+)\s*+)(?<definition>([^\n\r]*)){(?P<inner>(?:[^{}]+|(?R))*)}#musS';

    public function annotate($filename, $workingDirectory = './')
    {
        if ($this->isFileProcessed($filename)) {
            return [];
        }
        $this->putProcessedFile($filename);

        if (file_exists($workingDirectory . $filename) === false) {
            $filename = dirname($filename) . "/_" . basename($filename);
        }
        $filename = $workingDirectory . $filename;
        if (file_exists($filename) === false) {
            var_dump($filename, $workingDirectory, $this->processedFiles);die();
        }

        $workingDirectory = dirname($filename) . '/';



        $content = file_get_contents($filename);

        Yii::beginProfile('Recursive annotate ' . $filename);

        Yii::beginProfile('Find global variables definitions');

        MonsterVariable::retrieveVariables(
            preg_replace(static::SCSS_REGEXP, '', $content)
        );

        Yii::endProfile('Find global variables definitions');

        $tree = $this->recursiveAnnotate($content, '', $workingDirectory);

        Yii::endProfile('Recursive annotate ' . $filename);

        return $tree;

    }

    /**
     * @param string      $content
     * @param null|string $parentBemSelector
     *
     * @return BemEntity[]
     */
    public function recursiveAnnotate($content, $parentBemSelector = '', $workingDirectory = './')
    {
        $result = [];

        // first find all nested files
        // yes, that's not good idea for parsing scss
        // but for our needs of retrieving meta data it's ok
        // https://regex101.com/r/aI7yL9/1
        preg_match_all(
            '#^\s*@import\s+[\'"](?U:(?<file>[^\r\n]+))(?:\.scss)?[\'"];$#musS',
            $content,
            $imports,
            PREG_SET_ORDER
        );
        foreach ($imports as $import) {
            $importedTree = $this->annotate($import['file'] . '.scss', $workingDirectory);
            foreach ($importedTree as $item) {
                $result [] = $item;
            }
        }

        preg_match_all(
            static::SCSS_REGEXP,
            $content,
            $m,
            PREG_SET_ORDER
        );

        foreach ($m as $match) {
            foreach ($match as $key => &$value) {
                // @todo this is just for debug - remove this condition later
                if (is_numeric($key)) {
                    unset($match[$key]);
                    continue;
                }
                $value = trim($value);
            }
            // originalInner is used for finding nested scss sections
            $originalInner = $match['inner'];
            $match['inner'] = $this->trimAllLines($match['inner']);
            static::findRelatedStuff($match['comment'], $match);
            $this->parseDefinition($match['definition'], $match);

            $instance = BemEntity::unpack($match, $parentBemSelector);


            if (strlen($originalInner) > 0
                && ($instance instanceof BemBlock || $instance instanceof BemElement)
            ) {
                /** @var BemBlock|BemElement $instance */
                $children = $this->recursiveAnnotate($originalInner, $instance->bemSelector, $workingDirectory);
                foreach ($children as $child) {
                    if ($child instanceof BemModifier) {
                        $instance->modifiers[$child->name] = $child;
                    } else {
                        $instance->elements[$child->name] = $child;
                    }
                }
            }

            if ($instance !== null) {
                $result[] = $instance;
            } elseif (strlen($originalInner) > 0) {
                // current scss section is some non bemmy stuff, just go deeper
                $children = $this->recursiveAnnotate($originalInner, '', $workingDirectory);

                foreach ($children as $child) {
                    $result[] = $child;
                }
            }
        }
        return $result;
    }

    /**
     * Trim trailing and leading spaces on each line of value
     * @param string $content
     *
     * @return mixed
     */
    protected function trimAllLines($content)
    {
        $content = preg_replace('#^\s*#muS', '', $content);
        return preg_replace('#\s*$#muS', '', $content);
    }

    /**
     * Removes nested scss stuff from current scss logic section
     *
     * @param string $content
     *
     * @return string
     */
    protected function cleanNestedSCSS($content)
    {
        return preg_replace(static::SCSS_REGEXP, '', $content);
    }

    /**
     * Fills $result with name and bem-type based on definition
     * @param string $definition
     * @param array $result
     */
    public function parseDefinition($definition, &$result)
    {
        preg_match_all(
            '#^@include (?P<type>block|element|modifier|state)\s*\([\'"]?(?P<name>[^\'"]+)[\'"]?\)$#',
            $definition,
            $matches,
            PREG_SET_ORDER
        );
        if (isset($matches[0]['type'], $matches[0]['name'])) {
            $result['name'] = $matches[0]['name'];
            $result['bem-type'] = $matches[0]['type'];
        }
    }

    public static function findRelatedStuff($comments, &$result)
    {
        preg_match_all('#@(?P<key>[^ \t]*)[ \t]+(?P<value>.*)$#muS', $comments, $m, PREG_SET_ORDER);
        foreach ($m as $match) {
            $result[$match['key']] = trim($match['value']);
        }

        return $result;
    }

    public function putProcessedFile($filename)
    {
        $this->processedFiles[$filename] = true;
    }

    public function isFileProcessed($filename)
    {
        return isset($this->processedFiles[$filename]);
    }
}
