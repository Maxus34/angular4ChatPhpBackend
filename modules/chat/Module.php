<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 15.01.2017
 * Time: 14:27
 */

namespace app\modules\chat;
use yii\filters\AccessControl;
use yii\filters\auth\QueryParamAuth;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();


        \Yii::setAlias("@chat", __DIR__);
    }
}