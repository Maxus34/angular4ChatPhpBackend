<?php

return [
    'request' => [
        'baseUrl' => '',
        'cookieValidationKey' => 'qazxswedcdcfvriudcushkcsulkvlvrevrege',
        'parsers' => [
            'application/json' => 'yii\web\JsonParser',
        ]
    ],
    'cache' => [
        'class' => 'yii\caching\FileCache',
    ],
    'user' => [
        'identityClass' => 'app\models\User',
        'enableAutoLogin' => true,
        'enableSession' => false,
        'loginUrl' => ['user/login'],
    ],
    'authManager' => [
        'class' => '\yii\rbac\DbManager',
        'defaultRoles' => ['guest']
    ],
    'errorHandler' => [
        'errorAction' => 'site/error',
    ],
    'mailer' => [
        'class' => 'yii\swiftmailer\Mailer',
        'transport' => [
            'class' => 'Swift_SmtpTransport',
            'host'  => 'smtp.gmail.com',
            'username' => 'mxs34post@gmail.com',
            'password' => 'Mxs34SecretPassword',
            'port'     => '587',
            'encryption' => 'tls'
        ],
        'useFileTransport' => false,
    ],
    'log' => [
        'traceLevel' => YII_DEBUG ? 3 : 0,
        'targets' => [
            [
                'class' => 'yii\log\FileTarget',
                'levels' => ['error', 'warning'],
            ],
        ],
    ],
    'db' => require(__DIR__ . '/db.php'),
    'urlManager' => [
        'enablePrettyUrl' => true,
        'showScriptName' => false,
        'rules' => [
            'chat/api/<apiObject:\w+>.<apiMethod:\w+>' => '/chat/api/index',
            'chat/<id:\d+>' => '/chat/default/view',
            'chat' => '/chat/default',
        ],
    ],
    'redis' => [
        'host' => 'localhost',
        'port' => '6379',
        'class' => app\modules\chat\components\RedisServiceComponent::class,
    ]
];