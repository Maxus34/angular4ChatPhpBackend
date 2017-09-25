<?php

namespace app\modules\chat\services;

use app\modules\chat\models\DialogN;
use app\modules\chat\records\ { MessageRecord, MessageReferenceRecord };
use yii\web\HttpException;

class DialogRepository {

    protected static $dialogs = [];

    protected static $instance = null;

    protected $dialogFactory;



    public static function getInstance() :DialogRepository{
        if ( empty(static::$instance) ) {
            static::$instance = new static();
        }

        return static::$instance;
    }


    private function __construct() {
        $this->dialogFactory = DialogFactory::getInstance();
    }


    public function findDialogById(int $id) :DialogN{
        if ( !isset(static::$dialogs[$id]) ){

            $dialog = $this->dialogFactory->getDialogInstanceById($id);

            if ( !empty($dialog) ){
                static::$dialogs[$id] = $dialog;

            } else {
                throw new \Exception("Dialog #{$id} has not been founded.");
            }

        }

        return static::$dialogs[$id];
    }


    public function findDialogsByConditions(int $offset = null, int $limit = null, array $condition = null){

        if ( empty($condition) ){
            $dialogs = $this->dialogFactory->getDialogInstances($offset, $limit);

        } else {
            $dialogs = $this->dialogFactory->getDialogInstancesByCondition($offset, $limit, $condition);
        }

        foreach ($dialogs as $dialog){
            static::$dialogs[$dialog->id] = $dialog;
        }

        return $dialogs;
    }


    public function saveDialog(DialogN $dialog){
        $dialog -> dialogRecord -> save();
        foreach ($dialog->dialogReferences as $reference){
            $reference->save();
        }
    }


    public function deleteDialog(DialogN $dialog){
        $references = $dialog->getReferences();

        // Delete only for current user
        if (count($references) > 1){
            $dialog -> dialogReferences[$dialog->getUserId()] -> delete();

            $messageReferences = MessageReferenceRecord::findAll(['dialogId' => $dialog->getId(), 'userId' => $dialog->getUserId()]);
            foreach ($messageReferences as $reference) {
                $reference->delete();
            }

        // Delete all dialog
        } else {
            $messages = MessageRecord::findAll(['dialogId' => $dialog->getId()]);

            $dialog -> dialogReferences[ $dialog->getUserId() ] -> delete();
            $dialog -> dialogRecord -> delete();

            foreach ($messages as $message) {
                $message->delete();
            }
        }
    }


    public function getDialogFactory(){
        return $this->dialogFactory;
    }

}