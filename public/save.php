<?php
$host = '127.0.1.30';
$db = 'vkr_db';
$user = 'root';
$pass = '';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_errno) {
    die("Ошибка подключения к базе данных: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");

$district = $_POST['district'] ?? '';
$account_number = $_POST['account_number'] ?? '';

if ($district === '' || $account_number === '') {
    die("Ошибка: заполните все поля.");
}

if (!preg_match('/^\d{6}$/', $account_number)) {
    die("Ошибка: номер лицевого счета должен содержать ровно 6 цифр.");
}

$allowed_districts = [
    'Октябрьский',
    'Ленинский',
    'Кировский',
    'Советский',
    'Первомайский',
    'Центральный',
    'Железнодорожный',
    'Заельцовский',
    'Калининский',
    'Дзержинский'
];

if (!in_array($district, $allowed_districts)) {
    die("Ошибка: выбран неверный район.");
}

$stmt = $mysqli->prepare("INSERT INTO by_ls (district, account_number) VALUES (?, ?)");

if ($stmt === false) {
    die("Ошибка подготовки запроса: " . $mysqli->error);
}

$stmt->bind_param("ss", $district, $account_number);

if ($stmt->execute()) {
    echo "✓ Данные успешно сохранены в базу данных!<br>";
    echo "Район: " . htmlspecialchars($district) . "<br>";
    echo "Номер лицевого счета: " . htmlspecialchars($account_number) . "<br>";
    echo "<a href='index.html'>Вернуться на главную</a>";
} else {
    echo "Ошибка при сохранении: " . $stmt->error;
}

$stmt->close();
$mysqli->close();
