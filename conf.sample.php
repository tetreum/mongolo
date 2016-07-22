<?php

ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

define('APP_ROOT', __DIR__ . DIRECTORY_SEPARATOR);

$currentDomain = "mongo.dev";

return [
    /*
    // Enable if mongo hasnt any password set
    "local_auth" => [
        "salt" => "32423fg556h67u5y4t3-.-,.rf",
        "users" => [
                "admin" => "da3bb1f6a609d3d225e59bfbc19b57f6"
        ],
    ],*/
    "mongo" => [
        "ip" => "MONGO_IP",
        "options" => [
            "connectTimeoutMS" => 150,
            "connect" => false,
        ]
    ],
    'mode' => 'production',
    'displayErrorDetails' => false,
    'debug' => false,
    'cookies.encrypt' => true,
    'cookies.secret_key' => '45r67tuhw34567itukw45ye689087iutrhg-.khj',
    'cookies.cipher' => MCRYPT_RIJNDAEL_256,
    'cookies.cipher_mode' => MCRYPT_MODE_CBC,
    'cookies.path' => '/',
    'cookies.domain' => '.' . $currentDomain,
];
