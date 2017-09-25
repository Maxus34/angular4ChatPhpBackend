<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 17.09.2017
 * Time: 17:00
 */

namespace app\modules\chat\services\api;


use app\modules\chat\services\DialogRepository;

class WsApiService {
    public function getUserInfo(){
        $user = \Yii::$app->user->identity->getAsArray();

        $dialogRepository = DialogRepository::getInstance();

        $dialogs = $dialogRepository->findDialogsByConditions();


        $dialogsArray = [];
        foreach ($dialogs as $dialog){
            $dialogsArray[] = $dialog->getAsArray();
        }


        return [
            "response" => [
                'user' => $user,
                'dialogs' => $dialogsArray
            ]
        ];
    }
}