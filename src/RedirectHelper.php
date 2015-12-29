<?php

namespace DevGroup\Frontend;

use Yii;
use yii\helpers\Url;

class RedirectHelper
{

    public static function getPostedReturnUrl()
    {
        $returnUrl = Yii::$app->request->get('returnUrl', '');
        if ($returnUrl !== '') {
            return $returnUrl;
        }

        $returnUrl = Yii::$app->request->post('returnUrl', '');
        if ($returnUrl !== '') {
            return $returnUrl;
        }
        $session = Yii::$app->getSession();
        $returnUrl = $session->get(Yii::$app->user->returnUrlParam, '');
        if ($returnUrl !== '') {
            return $returnUrl;
        }
        return '';
    }

    public static function getReturnUrl()
    {

        $returnUrl = self::getPostedReturnUrl();
        if ($returnUrl !== '') {
            return $returnUrl;
        }


        $returnUrl = Yii::$app->request->getReferrer();

        return $returnUrl === null ? Url::to() : $returnUrl;
    }
}