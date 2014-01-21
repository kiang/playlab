<?php

$loader = include 'vendor/rickysu/phpsocket.io/autoload.php.dist';
$loader->add('PHPSocketIO', __DIR__ . '/vendor/rickysu/phpsocket.io/src');

use PHPSocketIO\SocketIO;
use PHPSocketIO\Connection;
use PHPSocketIO\Response\Response;
use PHPSocketIO\Event;

$socketio = new SocketIO();
$counter = array();
$chat = $socketio
        ->getSockets()
        ->on('addme', function(Event\MessageEvent $messageEvent) use (&$chat) {
            $messageEvent->getConnection()->emit(
                    'update', array('msg' => "Welcome {$messageEvent->getMessage()}")
            );
            $chat->emit('update', array('msg' => "{$messageEvent->getMessage()} is coming."));
        })
        ->on('msg', function(Event\MessageEvent $messageEvent) use (&$chat) {
    $message = $messageEvent->getMessage();
    $chat->emit('update', $message);
});
$socketio
        ->listen(8080)
        ->onConnect(function(Connection $connection) use ($socketio) {
            list($host, $port) = $connection->getRemote();
            echo "connected $host:$port\n";
        })
        ->onRequest('/', function($connection, \EventHttpRequest $request) {
            $response = new Response(file_get_contents(__DIR__ . '/web/index.html'));
            $response->setContentType('text/html', 'UTF-8');
            $connection->sendResponse($response);
        })
        ->onRequest('/socket.io.js', function($connection, \EventHttpRequest $request) {
            $response = new Response(file_get_contents(__DIR__ . '/web/socket.io.js'));
            $response->setContentType('text/javascript', 'UTF-8');
            $connection->sendResponse($response);
        })
        ->onRequest('/jquery.min.js', function($connection, \EventHttpRequest $request) {
            $response = new Response(file_get_contents(__DIR__ . '/web/jquery.min.js'));
            $response->setContentType('text/javascript', 'UTF-8');
            $connection->sendResponse($response);
        })
        ->onRequest('/bootstrap.min.css', function($connection, \EventHttpRequest $request) {
            $response = new Response(file_get_contents(__DIR__ . '/web/bootstrap.min.css'));
            $response->setContentType('text/javascript', 'UTF-8');
            $connection->sendResponse($response);
        })
        
        ->onRequest('/count', function($connection, \EventHttpRequest $request) use (&$socketio) {
            global $counter;
            list($host, $port) = $connection->getRemote();
            if (isset($counter[$host])) {
                ++$counter[$host];
            } else {
                $counter[$host] = 1;
            }
            $response = new Response('');
            $response->setContentType('text/html', 'UTF-8');
            $connection->sendResponse($response);
            $message = date('Y-m-d H:i:s') . '<br />';
            foreach($counter AS $ip => $cnt) {
                $message .= '> ' . $ip . ' : ' . $cnt . '<br />';
            }
            $socketio->emit('update', array('msg' => $message, 'counter' => $counter));
        })
        ->dispatch();
