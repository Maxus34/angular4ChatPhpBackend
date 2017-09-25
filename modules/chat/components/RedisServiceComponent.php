<?php

namespace app\modules\chat\components;

use yii\base\Component;
use Predis\Client;
use yii\helpers\Json;

class RedisServiceComponent extends Component {

    public $host;
    public $port;

    protected $redis;

    protected $channelForChatEvents = "chatEvents";

    public function __construct(array $config = []) {
        parent::__construct($config);

        try{
            $this->redis = new Client();
        } catch (\Exception $e) { echo $e; }
    }

    public function publishEventToWs(array $event){

        $message = Json::encode($event);

        $this->redis->publish($this->channelForChatEvents, $message);
    }
}