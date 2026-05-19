<?php
session_start();
header('Content-Type: application/json');

$host = '127.0.1.30';
$db   = 'vkr_db';
$user = 'root';
$pass = '';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_errno) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка подключения']);
    exit;
}

$mysqli->set_charset("utf8mb4");

$address   = trim($_POST['address']   ?? '');
$apartment = trim($_POST['apartment'] ?? '');

if ($address === '' || $apartment === '') {
    echo json_encode(['status' => 'error', 'message' => 'Заполните все поля']);
    exit;
}

$stmt = $mysqli->prepare(
    "SELECT amount FROM by_adress WHERE address = ? AND apartment = ? LIMIT 1"
);
$stmt->bind_param("ss", $address, $apartment);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $_SESSION['debt']      = $row['amount'];
    $_SESSION['address']   = $address;
    $_SESSION['apartment'] = $apartment;
    echo json_encode(['status' => 'found']);
} else {
    echo json_encode(['status' => 'nodebts']);
}

$stmt->close();
$mysqli->close();
