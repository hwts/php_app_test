<?php
require_once '/usr/share/php/PhpAmqpLib/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Redis;

// Подключение к RabbitMQ
$connection = new AMQPStreamConnection('192.168.2.227', 5672, 'hwts', 'mypass123');
$channel = $connection->channel();

// Объявление очереди, из которой будут получаться данные
$queue_name = 'my_queue';
$channel->queue_declare($queue_name, false, false, false, false);

// Подключение к Redis
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$redis->auth('mypass123');

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

// Функция обработки сообщения
$callback = function ($msg) use ($redis) {
    $data = json_decode($msg->body, true);
    foreach ($data as $name => $email) {
        $redis->hset('users', $name, $email);
        echo " [x] Saved $name with email $email to Redis\n";
    }
};

// Получение сообщений из очереди
$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

// Закрытие соединений
$redis->close();
$channel->close();
$connection->close();
?>
