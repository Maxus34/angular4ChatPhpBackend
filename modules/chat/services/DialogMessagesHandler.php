<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 17.05.2017
 * Time: 16:49
 */

namespace app\modules\chat\services;

use app\modules\chat\models\  { DialogN, MessageN };
use app\modules\chat\records\ { MessageRecord, MessageReferenceRecord };

class DialogMessagesHandler {

    /** @var DialogN  */
    protected $dialog;

    public function __construct(DialogN $dialog) {
        $this -> dialog = $dialog;
    }


    public function getMessagesCount(bool $new = false) {
        $query = MessageRecord :: find()
            -> innerJoin('message_ref', '`message`.`id` = `message_ref`.`messageId`')
            //-> groupBy('`message`.`id`')
            -> where("`message_ref`.`dialogId` = {$this->dialog->getId()} AND `message_ref`.`userId` = {$this->dialog->getUserId()}");

        if ($new){
            $query = $query -> andWhere("`message_ref`.`isNew`=1 AND `message_ref`.`userId`!={$this->dialog->getUserId()}");
        }

        return $query->count();
    }


    public function setMessageSeen(MessageN $message){
        $messageReferences = $message->messageReferences;

        foreach ($messageReferences as $reference){
            /** @var MessageReferenceRecord $reference */
            $reference -> isNew = 0;
            $reference -> save();
        }

        return $message;
    }


    public function addMessageToTheDialog(string $content, array $files = []) :MessageN{
        $messageFactory = new MessageFactory($this->dialog);

        $message = $messageFactory->createNewMessage($content, $files);

        $this->dialog->messageRepository->saveMessage($message);

        return $message;
    }
}