<?php

namespace DevGroup\Frontend\Monster;

use BEM\BH;
use BEM\Context;
use BEM\Json;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

class MonsterBemBh extends Component
{
    /** @var \BEM\BH */
    public $bh = null;

    public $formatHtml = '  ';

    public $customization = [];

    public $modsDelimiter = '--';

    public function init()
    {
        parent::init();
        $this->bh = new BH();
        $this->initBH($this->bh);
    }


    /**
     * Configures BEM\BH class with our needed matchers
     * @param \BEM\BH $bh
     */
    public function initBH(BH &$bh)
    {
        $bh->setOptions([
            'modsDelimiter' => $this->modsDelimiter,
            'indent' => $this->formatHtml,
        ]);
        $bh->match([
            '$after' => function (Context $ctx, Json $json) {

                $clsAdd = [];
                $attrs = $ctx->attrs();

                if (isset($json->editable)) {
                    //! @todo if it is frontend rendering mode -> don't add editable!
                    $attrs['data-editable'] = 'true';
                }
                if (isset($json->phpParam)) {
                    $ctx->content('{{phpParam:'.$json->phpParam.'}}');
                }
                if (isset($json->row)) {
                    $clsAdd[] = 'm-row';
                }
                if (isset($json->utils)) {
                    foreach ($json->utils as $util) {
                        $clsAdd[] = 'g__' . $util;
                    }
                }
                // add new classes
                if (count($clsAdd) > 0) {
                    $ctx->cls(implode(' ', $clsAdd) . ' ' . $ctx->cls(), true);
                }

                if ($json->block) {
                    $attrs['data-bem-match'] = $json->block . ($json->elem ? '__' . $json->elem : '');
                }
                $ctx->attrs($attrs, true);
            },
            'button' => function (Context $ctx) {
                $ctx->tag('button');
            },

        ]);

    }

    /**
     * @param string $bemJson
     * @param array  $params
     * @return string
     */
    public function apply($bemJson, $params = [])
    {
        Yii::beginProfile('BEM BH');

        //! @todo Think about adding cache here
        /*
         * bh->apply takes about 13-17 ms to compile simple block
         * html formatter takes about 0.7-1 ms to format it
         * preg_replace with params is just only about 0.1ms
         *
         * Also, we can try twig here without preg_replace ^_^
         */
        Yii::beginProfile('apply');
        $output = $this->bh->apply($bemJson);
        Yii::endProfile('apply');

        Yii::beginProfile('Replace php params');
        $output = preg_replace_callback(
            '/{{phpParam:([^}]*)}}/U',
            function ($match) use ($params) {
                if (isset($params[$match[1]])) {
                    return $params[$match[1]];
                }
                return "<!-- unknown php param $match[1] -->";
            },
            $output
        );
        Yii::endProfile('Replace php params');

        Yii::endProfile('BEM BH');

        return $output;
    }

    public function customize($customization)
    {
        $this->customization = $customization;
        foreach ($customization as $bemSelector => $instructions) {
            $this->bh->match(
                $bemSelector,
                function (Context $ctx, Json $json) use($instructions) {
                    $this->processInstructions($ctx, $json, $instructions);
                }
            );
        }
    }

    public function processInstructions(Context $ctx, Json $json, $instructions)
    {
        Yii::beginProfile("Process instructions");
        foreach ($instructions as $what => $how) {
            switch ($what) {
                case 'mods':
                    $ctx->mods($how);
                    break;
                default:
                    if (isset($json->$what)) {
                        if (is_array($json->$what)) {
                            $json->$what = ArrayHelper::merge($json->$what, $how);
                        } elseif (is_string($json->$what)) {
                            $json->$what .= " $how";
                        } else {
                            Yii::warning(
                                "Unknown json->what[$what] type, need to implement it. Current: " .
                                VarDumper::dumpAsString($json->$what) . " Customization: " .
                                VarDumper::dumpAsString($how)
                            );
                            $json->$what = $how;
                        }
                    } else {
                        $json->$what = $how;
                    }
            }
        }
        Yii::endProfile("Process instructions");
    }
}
