<?php
// test commit for branch slim2
require 'vendor/autoload.php';



$app = new \Slim\Slim(array(
    'mode' => 'development',
    'debug' => true,
    'log.enabled' => true,
    ));


$app->post('/api', function() use ($app){
//echo "test";
    $request = $app->request();

    $publicHash  = $request->headers('X-Public');
    //echo $publicHash;
    $contentHash = $request->headers('X-Hash');
    $privateHash  = 'e249c439ed7697df2a4b045d97d4b9b7e1854c3ff8dd668c779013653913572e';
    $content     = $request->getBody();
    print_r($content);
    $hash = hash_hmac('sha256', $content, $privateHash);
    //if(hash_equals($hash, $contentHash)) echo 'equals';
    if ($hash ===$contentHash){
        echo "match!\n";
    } else {
        echo "not match!\n";
    }
});




$app->run();