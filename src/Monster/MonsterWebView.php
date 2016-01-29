<?php

namespace DevGroup\Frontend\Monster;

use DevGroup\Frontend\Monster\bem\BemBlock;
use DevGroup\Frontend\Monster\bem\BemElement;
use SplStack;
use Yii;
use yii\web\View;

class MonsterWebView extends View
{
    /** @var SplStack */
    public $bemBlockStack = null;

    public $nestingStackSize = 0;
    public $nestedBemOutput = '    ';

    const RENDER_MODE_FRONTEND = 'frontend';
    const RENDER_MODE_EDIT = 'edit';

    private $renderMode = MonsterWebView::RENDER_MODE_EDIT;

    public $renderers = [
        'twig' => [
            'class' => 'yii\twig\ViewRenderer',
            // set cachePath to false in order to disable template caching
            'cachePath' => '@runtime/Twig/cache',
            // Array of twig options:
            'options' => [
                'auto_reload' => true,
            ],
            'globals' => [
                'html' => '\yii\helpers\Html',
                'url' => '\yii\helpers\Url',
            ],
        ],
    ];

    /**
     * @var MonsterBemBh
     */
    public $bh = null;

    public function init()
    {
        parent::init();
        $this->bemBlockStack = new SplStack();
        $this->bh = new MonsterBemBh();
    }

    public function beginMonster()
    {
        echo "\n";
        return $this->beginContent(
            '@vendor/devgroup/yii2-frontend-utils/src/monster/views/monster-partial.php'
        );
    }

    public function endMonster()
    {
        $this->endContent();
    }

    public function beginBem()
    {
        echo "\n";
        return $this->beginContent(
            '@vendor/devgroup/yii2-frontend-utils/src/monster/views/bem-bh.php',
            [
                'bh' => $this->bh,
            ]
        );
    }

    public function endBem()
    {
        $this->endContent();
    }

    public function bemBlock($blockName, $modifiers = [], $additionalClasses = '')
    {
        BemBlock::begin([
            'blockName' => $blockName,
            'modifiers' => $modifiers,
            'additionalClasses' => $additionalClasses,
        ]);
    }

    public function endBemBlock()
    {
        $this->nestingStackSize++;
        BemBlock::end();
        $this->nestingStackSize--;
        echo "\n";
    }

    public function bemElement($elementName, $modifiers = [], $additionalClasses = '', $blockName = false)
    {
        BemElement::begin([
            'elementName' => $elementName,
            'blockName' => $blockName,
            'modifiers' => $modifiers,
            'additionalClasses' => $additionalClasses,
        ]);
    }

    public function endBemElement()
    {
        $this->nestingStackSize++;
        BemElement::end();
        $this->nestingStackSize--;
        echo "\n";
    }

    public function nestedOutput($content)
    {
        if ($this->nestedBemOutput) {
            $pad = str_repeat($this->nestedBemOutput, $this->nestingStackSize);
            echo preg_replace('/^/m', $pad, $content);
        } else {
            echo $content;
        }
    }

    /**
     * Setter for renderMode
     * @param $renderMode
     */
    public function setRenderMode($renderMode) {
        $this->renderMode = $renderMode;
    }

    /**
     * Getter for renderMode
     *
     * @return string
     */
    public function getRenderMode() {
        return $this->renderMode;
    }
}
