<?php
// Подключение к базе данных
$host = '127.0.1.30';
$db = 'vkr_db';
$user = 'root';
$pass = '';

$mysqli = new mysqli($host, $user, $pass, $db);

// Проверка подключения
if ($mysqli->connect_errno) {
    die("Ошибка подключения к базе данных: " . $mysqli->connect_error);
}

// Установка кодировки
$mysqli->set_charset("utf8mb4");

// Получение данных из формы
$district = $_POST['district'] ?? '';
$account_number = $_POST['account_number'] ?? '';

// Валидация
if ($district === '' || $account_number === '') {
    die("Ошибка: заполните все поля.");
}

// Проверка, что номер счета - это ровно 6 цифр
if (!preg_match('/^\d{6}$/', $account_number)) {
    die("Ошибка: номер лицевого счета должен содержать ровно 6 цифр.");
}

// Список допустимых районов
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

// Проверка, что район из списка
if (!in_array($district, $allowed_districts)) {
    die("Ошибка: выбран неверный район.");
}

// Подготовка SQL-запроса (защита от SQL-инъекций)
$stmt = $mysqli->prepare("INSERT INTO applications (district, account_number) VALUES (?, ?)");

if ($stmt === false) {
    die("Ошибка подготовки запроса: " . $mysqli->error);
}

// Привязка параметров (s = string)
$stmt->bind_param("ss", $district, $account_number);

// Выполнение запроса
if ($stmt->execute()) {
    echo "✓ Данные успешно сохранены в базу данных!<br>";
    echo "Район: " . htmlspecialchars($district) . "<br>";
    echo "Номер лицевого счета: " . htmlspecialchars($account_number) . "<br>";
    echo "<a href='index.html'>Вернуться на главную</a>";
} else {
    echo "Ошибка при сохранении: " . $stmt->error;
}

// Закрытие подготовленного выражения
$stmt->close();

// Закрытие соединения
$mysqli->close();
