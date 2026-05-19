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

$district       = $_POST['district']       ?? '';
$account_number = $_POST['account_number'] ?? '';

if ($district === '' || $account_number === '') {
    echo json_encode(['status' => 'error', 'message' => 'Заполните все поля']);
    exit;
}

if (!preg_match('/^\d{6}$/', $account_number)) {
    echo json_encode(['status' => 'error', 'message' => 'Некорректный номер лицевого счета']);
    exit;
}

$allowed_districts = [
    'Октябрьский','Ленинский','Кировский','Советский','Первомайский',
    'Центральный','Железнодорожный','Заельцовский','Калининский','Дзержинский'
];

if (!in_array($district, $allowed_districts)) {
    echo json_encode(['status' => 'error', 'message' => 'Некорректный район']);
    exit;
}

$stmt = $mysqli->prepare(
    "SELECT amount FROM by_ls WHERE account_number = ? AND district = ? LIMIT 1"
);
$stmt->bind_param("ss", $account_number, $district);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $_SESSION['debt']           = $row['amount'];
    $_SESSION['account_number'] = $account_number;
    $_SESSION['district']       = $district;
    echo json_encode(['status' => 'found']);
} else {
    echo json_encode(['status' => 'nodebts']);
}

$stmt->close();
$mysqli->close();
