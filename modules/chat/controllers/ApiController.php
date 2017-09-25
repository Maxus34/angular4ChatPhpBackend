<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 25.08.2017
 * Time: 17:14
 */

namespace app\modules\chat\controllers;

use yii\filters\{ Cors, auth\QueryParamAuth};
use yii\helpers\Json;
use yii\web\Controller;

use app\modules\chat\services\api\DialogsApiService;
use app\models\User;
use yii\web\HttpException;

class ApiController extends Controller {

    protected $apiObject;
    protected $apiMethod;
    protected $apiClassesNamespace = "app\modules\chat\services\api\\";


    public function behaviors(){
        return ['corsFilter' => ['class' => Cors::class,], 'authenticator' => ['class' => QueryParamAuth::class, 'except' => ['login', 'check-access']],];
    }


    public function beforeAction ($action) {
        $this->enableCsrfValidation = false;

        if (parent::beforeAction($action)) {

           if ($action->id == 'login' || $action->id == 'check-access'){
               return true;
           }


            $this->apiObject = \Yii::$app->request->get('apiObject', null);
            $this->apiMethod = \Yii::$app->request->get('apiMethod', null);

            $this->apiObject = $this->apiClassesNamespace.ucfirst($this->apiObject)."ApiService";

            if (!class_exists($this->apiObject) || !method_exists($this->apiObject, $this->apiMethod)){
                throw new HttpException(405, "Method is not allowed by current api version");
            }

            return true;
        }

        return false;
    }


    public function afterAction($action, $result) {
        return Json::encode($result);
    }


    public function actionIndex() {

        try{
            $result = call_user_func(
                [(new $this->apiObject), $this->apiMethod],
                \Yii::$app->request->getBodyParams()
            );
        }
        catch (\yii\base\Exception $e){
            throw $e;
        }
        catch (\Exception $e){
            return [
                'error' => $e->getMessage()
            ];
        }

        return $result;
    }


    public function actionLogin(){

        $request = \Yii::$app->request->getBodyParams();

        if (empty($request['username']) || empty($request['password']))
            return [
                'error' => 'Empry username && password got'
            ];

        $user = User::findByUsername($request['username']);

        if ($user && $user->validatePassword($request['password'])){
            return [
                'response' => [
                    'status' => 'success',
                    'item'   => $user->getAsArray(true)
                ]
            ];
        } else {
            return [
                'error' => 'Wrong username or password'
            ];
        }
    }


    public function actionCheckAccess(){

        $token = \Yii::$app->request->get('access-token');

        if (empty($token))
            throw new \Exception('Empty token got!');

        $user = User::findIdentityByAccessToken($token);

        if (!empty($user)){
            return [
                'response' => [
                    'status' => 'success',
                    'item'   => $user->getAsArray(true)
                ]
            ];
        } else {
            return [
                'response' => [
                    'status' => 'fail',
                ]
            ];
        }
    }
}