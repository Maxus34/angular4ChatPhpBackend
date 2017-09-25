<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'layout' => 'app_layout',
    'modules' => [
        'admin' => [
            'class' => 'app\modules\admin\Module',
        ],
        'chat' => [
            'class' => 'app\modules\chat\Module',
            'defaultRoute' => 'default/index',
            'layout' => 'chat_layout',
            'components' => [

            ]
        ],
    ],
	'components' => require(__DIR__ . '/components.php'),
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
		'allowedIPs' => ['::1', '127.0.0.1', '192.168.33.*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
		'allowedIPs' => ['::1', '127.0.0.1', '192.168.33.*'],
    ];
}

return $config;
