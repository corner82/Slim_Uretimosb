<?php
// test commit for branch slim2
require 'vendor/autoload.php';



/*$app = new \Slim\Slim(array(
    'mode' => 'development',
    'debug' => true,
    'log.enabled' => true,
    ));*/

$app = new \Slim\SlimExtended(array(
    'mode' => 'development',
    'debug' => true,
    'log.enabled' => true,
    ));

$app->post('/invalid', function () {
    echo "Invalid request url parameter format";
});

$app->get('/invalid', function () {
    echo "Invalid request url parameter format";
});

$app->put('/invalid', function () {
    echo "Invalid request url parameter format";
});

$app->delete('/invalid', function () {
    echo "Invalid request url parameter format";
});


$app->run();