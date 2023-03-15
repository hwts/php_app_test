<?php
// Подключаем библиотеку phpredis
require_once '/usr/share/php/PhpAmqpLib/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Создаем объект Redis и подключаемся к серверу Redis
$redis = new Redis();
$redis->connect('192.168.2.221', 6379);
$redis->auth('mypass123');

// Подключаемся к RabbitMQ
$connection = new AMQPStreamConnection('192.168.2.227', 5672, 'hwts', 'mypass123');
$channel = $connection->channel();

// Объявляем очередь для сообщений
$channel->queue_declare('my_queue', false, false, false, false);
$channel->queue_declare('myapp_elk', false, false, false, false);

// Проверяем, была ли отправлена форма
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получаем данные из формы
    $name = $_POST['name'];
    $email = $_POST['email'];

    // Сохраняем данные в Redis
    $redis->hset('users', $name, $email);
    
    // Получаем данные из Redis
    $data = $redis->hgetall('users');

    // Отправляем данные в RabbitMQ
    $msg = new AMQPMessage(json_encode($data));
    $channel->basic_publish($msg, '', 'my_queue');
    $channel->basic_publish($msg, '', 'myapp_elk');
    $channel->close();

    // Выводим сообщение об успешной отправке данных
    echo "<p>Данные успешно сохранены</p>";
}
$redis->close();
$channel->close();
$connection->close();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Супер приложение</title>
</head>
<body>
    <h1>Очень крутое приложение!!!</h1>

    <form method="post">
        <label>Имя:</label><br>
        <input type="text" name="name"><br>
        <label>Почта:</label><br>
        <input type="text" name="email"><br>
        <button type="submit">Отправить</button>
    </form>
</body>
</html>
