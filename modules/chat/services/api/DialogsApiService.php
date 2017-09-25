<?php
namespace app\modules\chat\services\api;

use app\models\User;
use app\modules\chat\components\RedisServiceComponent;
use app\modules\chat\models\DialogProperties;
use app\modules\chat\services\DialogRepository;
use app\modules\chat\models\DialogN;

class DialogsApiService {

    protected $request;

    /** @var DialogRepository  */
    protected $dialogRepository;


    /** @var  RedisServiceComponent */
    protected $redisService;

    public function __construct() {
        $this->request = \Yii::$app->request->getBodyParams();
        $this->dialogRepository = DialogRepository::getInstance();
        $this->redisService = \Yii::$app->redis;
    }


    public function get () {
        $dialogs = $this->dialogRepository->findDialogsByConditions(
            $this->request['offset'] ?? null,
            $this->request['limit'] ?? null
        );

        $dialogsArray = [];
        foreach ($dialogs as $dialog){
            /** @var DialogN $dialog */
            $dialogAsArray = $dialog->getAsArray(false);

            if (!empty($this->request['withLastMessage'])) {

                $lastMessages = $dialog->messageRepository->findMessagesByConditions(-1);
                if (count($lastMessages) > 0) {
                    $dialogAsArray['lastMessage'] = $lastMessages[0]->getAsArray();
                }
            }

            $dialogsArray[] = $dialogAsArray;
        }

        return [
            "response" => [
                    'count' => count($dialogsArray),
                    'items' => $dialogsArray
            ]
        ];
    }


    public function getById(){
        $id = $this->request['id'] ?? false;

        if (!$id) throw new \Exception("Empty dialogID has been got.");

        $dialog = $this->dialogRepository->findDialogById($id);

        return [
            "response" => [
                'count' => 1,
                "item"  => $dialog->getAsArray()
            ]
        ];
    }


    public function delete(){
        $id = $this->request['id'] ?? false;

        if (!$id) throw new \Exception("Empty dialogID has been got.");

        $dialog = $this->dialogRepository->findDialogById($id);

        $redisEvent = [
            'event' => 'dialog.deleted',
            'data' => [
                'from' => \Yii::$app->user->getId(),
                'item' => [
                    'id' => $dialog->getId(),
                ]
            ]
        ];

        $this->dialogRepository->deleteDialog($dialog);
        $this->redisService->publishEventToWs($redisEvent);

        return [
            "response" => [
                "result" => "success",
            ]
        ];
    }


    public function create(){
        $model = new DialogProperties();
        $model->title = $this->request['title'] ?? "";
        $model->users = $this->request['users'] ?? [];

        if ($model->title == "")
            throw new \Exception("Empty title got!");

        if ($model->validate()){
            /** @var DialogN $dialog */
            $dialog = $this->dialogRepository->getDialogFactory()->createNewDialog();
            $dialog->dialogHandler->applyDialogProperties($model);
            $this->dialogRepository->saveDialog($dialog);
        } else {
            throw new \Exception("Some errors in dialogProperties.");
        }

        $dialogAsArray = $dialog->getAsArray();

        $redisEvent = [
            'event' => 'dialog.created',
            'data'  => [
                'from' => \Yii::$app->user->getId(),
                'item' => $dialogAsArray
            ]
        ];

        $this->redisService->publishEventToWs($redisEvent);

        return [
            "response" => [
                "result" => "success",
                "count" => 1,
                "item"  => $dialogAsArray
            ]
        ];
    }


    public function update(){

        $id = $this->request['id'] ?? false;
        if (!$id) throw new \Exception("Empty dialogID has been got.");

        $title = $this->request['title'] ?? "";
        $users = $this->request['users'] ?? [];

        $dialogProperties = new DialogProperties();
        $dialogProperties->title = $title;
        $dialogProperties->users = $users;

        $dialog = $this->dialogRepository->findDialogById($id);
        $dialog->dialogHandler->applyDialogProperties($dialogProperties);
        $this->dialogRepository->saveDialog($dialog);


        $dialogAsArray = $dialog->getAsArray();

        $redisEvent = [
            'event' => 'dialog.updated',
            'data'  => [
                'from' => \Yii::$app->user->getId(),
                'item' => $dialogAsArray
            ]
        ];

        $this->redisService->publishEventToWs($redisEvent);

        return [
            'response' => [
                'result' => 'success',
                'item'   => $dialogAsArray
            ]
        ];
    }

}