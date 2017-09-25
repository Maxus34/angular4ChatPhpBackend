<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 26.08.2017
 * Time: 18:54
 */

namespace app\modules\chat\services\api;


use app\modules\chat\components\RedisServiceComponent;
use app\modules\chat\models\DialogN;
use app\modules\chat\models\MessageN;
use app\modules\chat\services\{DialogRepository, MessageRepository};

class MessagesApiService {

    /** @var  MessageRepository */
    protected $messageRepository;

    /** @var  DialogRepository */
    protected $dialogRepository;

    /** @var  RedisServiceComponent */
    protected $redisService;

    /** @var  DialogN */
    protected $dialog;

    protected $request;

    public function __construct() {
        $this->request = \Yii::$app->request->getBodyParams();

        $dialogId = $this->request['dialogId'] ?? false;

        if ($dialogId){
            $this->redisService      = \Yii::$app->redis;
            $this->dialogRepository  = DialogRepository::getInstance();
            $this->dialog            = $this->dialogRepository->findDialogById($dialogId);
            $this->messageRepository = $this->dialog->getMessageRepository();

        }else {
            throw new \Exception('Empty dialog id has been got {MessageApiService');
        }
    }

    public function get(){
        $messages = $this->messageRepository->findMessagesByConditions(
            $this->request['offset'] ?? -20,
            $this->request['limit'] ?? null
        );

        $responseArray = [];

        foreach ($messages as $message) {
            /** @var $message MessageN */
            $responseArray[] = $message->getAsArray();
        }

        return [
            'response' => [
                'count' => count($responseArray),
                'items' => $responseArray
            ]
        ];
    }

    public function getById(){

        $id = $this->request['id'];
        if (empty ($id)) throw new \Exception("Empty messageID has been got");

        /** @var MessageN $message */
        $message = $this->messageRepository->findById($id);

        if (empty($message)) throw new \Exception("Message ${$id} not founded");


        return [
            'response' => [
                'count' => 1,
                'item' => $message->getAsArray()
            ]
        ];

    }

    public function getBeforeId(){
        $id = $this->request['id'];
        $count = $this->request['count'] ?? 10;

        if (empty($id))
            throw new \Exception("Empty id got");

        $messages = $this->messageRepository
            ->findMessagesByConditions(-$count, null, [
                ["<", "messageId", $id]
            ]);

        $responseArray = [];
        foreach ($messages as $message) {
            /** @var $message MessageN */
            $responseArray[] = $message->getAsArray();
        }

        return [
            'response' => [
                'count' => count($responseArray),
                'items' => $responseArray
            ]
        ];
    }

    public function delete(){

        $id = $this->request['id'] ?? null;
        $ids = $this->request['ids'] ?? null;

        if (empty($id) && empty($ids))
            throw new \Exception("Empty request got");

        if (!empty($id)){
            $message = $this->messageRepository->findById($id);
            $deletedMessageId = $this->messageRepository->deleteMessage($message);

            return [
                "response" => [
                    "result" => $deletedMessageId
                ]
            ];

        } else {
            $messages = $this->messageRepository->findById($ids);

            $result = [];

            foreach ($messages as $message){
                $result[] = $this->messageRepository->deleteMessage($message);
            }

            return [
                "response" => [
                    "result" => $result,
                ]
            ];
        }
    }

    public function send(){
        $content = $this->request['content'];
        $attachment = $this->request['attachment'] ?? [];

        if(empty($content) && empty($attachment))
            throw new \Exception("Empty content got");

        $message = $this->dialog->messageHandler->addMessageToTheDialog($content, $attachment);

        $messageAsArray = $message->getAsArray();

        $redisEvent = [
            'event' => 'message.created',
            'data'  => [
                'from'  => \Yii::$app->user->getId(),
                'dialogId' => $this->dialog->getId(),
                'item'     => $messageAsArray
            ]
        ];
        $this->redisService->publishEventToWs($redisEvent);

        return [
            "response" => [
                'item' => $messageAsArray
            ]
        ];
    }

    public function see(){
        $messageId = $this->request['messageId'] ?? false;

        if (!$messageId)
            throw new \Exception('Empty messageId got in message.see');

        /** @var MessageN $message */
        $message = $this->messageRepository->findById($messageId);

        $this->dialog->messageHandler->setMessageSeen($message);
        $messageAsArray = $message->getAsArray();

        $redisEvent = [
            'event' => 'message.updated',
            'data'  => [
                'from'  => \Yii::$app->user->getId(),
                'dialogId' => $this->dialog->getId(),
                'item'     => $messageAsArray
            ]
        ];

        $this->redisService->publishEventToWs($redisEvent);

        return [
            "response" => [
                'item' => $messageAsArray
            ]
        ];
    }
}