<?php
// Подключаем библиотеку phpredis
require_once  '/usr/share/php/PhpAmqpLib/autoload.php';

// Создаем объект Redis и подключаемся к серверу
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$redis->auth('mypass123');

// Получаем все элементы из хеш-таблицы "users"
$data = $redis->hgetall('users');

// Выводим данные на страницу
echo '<table>';
echo '<tr><th>Name</th><th>Email</th></tr>';
foreach ($data as $name => $email) {
    echo "<tr><td>$name</td><td>$email</td></tr>";
}
echo '</table>';

// Закрываем соединение
$redis->close();
?>
