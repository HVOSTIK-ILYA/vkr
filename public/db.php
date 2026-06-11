<?php
$conn = mysqli_connect('MariaDB-11.4', 'root', '', 'vkr');
if (!$conn) {
    die('Ошибка подключения: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');
?>