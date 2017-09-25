<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 25.08.2017
 * Time: 19:05
 */

namespace app\modules\chat\services\api;


use app\models\User;

class UsersApiService {
    protected $request;

    public function __construct() {
        $this->request = \Yii::$app->request->getBodyParams();
    }

    public function get(){
        /**
         *  in = []; Поиск сразу нескольких пользователей
         *
         *  limit, offset
         */

        $query = User::find();

        $users = [];

        if (isset($this->request['in'])){
            $users = $query->where(['id ' => $this->request['in']])->all();
        } else {

            if (isset($this->request['offset'])){
                $query = $query->offset($this->request['offset']);
            }

            if (isset($this->request['limit'])){
                $query = $query->offset($this->request['limit']);
            }

            $users = $query->all();
        }

        $responseArray = [];

        foreach ($users as $user){
            $responseArray[] = $user->getAsArray();
        }

        return [
            'response' => [
                'count' => count($responseArray),
                'items' => $responseArray
            ]
        ];
    }

    public function getById() {
        $id = $this->request['id'];
        if (empty ($id))
            throw new \Exception("Empty userID has been got");

        $user = User::findOne($id);

        if (empty($user))
            throw new \Exception("User ${$id} does not exists");

        return [
            'response' => [
                'count' => 1,
                'item' => $user->getAsArray()
            ]
        ];
    }

    public function getByAccessToken(){

    }
}