<?php

namespace app\controllers;

use app\modules\chat\components\RedisServiceComponent;
use yii\web\Controller;
use app\models\ContactForm;


class SiteController extends Controller {
    public function actions() {
        return ['error' => ['class' => 'yii\web\ErrorAction',], 'captcha' => ['class' => 'yii\captcha\CaptchaAction', 'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,],];
    }

    public function actionIndex() {

        $event = [
            'type' => 'test-event',
            'data' => [
                '1' => 1,
                '2' => 2
            ]
        ];

        /** @var \Yii::$app->redis RedisServiceComponent */
        \Yii::$app->redis->publishEventToWs($event);

        echo "Send to Redis: <br>";
        debug($event);

        die;

        return $this->render('index');
    }
}
