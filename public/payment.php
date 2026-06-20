<?php
session_start();
$pay = $_SESSION['pay'] ?? null;
if (!$pay) {                 // нет данных об оплате — на главную
    header('Location: index.php');
    exit;
}

$paid      = false;
$cardError = '';

// --- Обработка оплаты картой ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['card_number'])) {
    $num = preg_replace('/\D/', '', $_POST['card_number']);
    $exp = trim($_POST['card_exp']);
    $cvc = preg_replace('/\D/', '', $_POST['card_cvc']);

    if (strlen($num) === 16 && preg_match('#^\d{2}/\d{2}$#', $exp) && strlen($cvc) === 3) {
        $paid = true;
        unset($_SESSION['pay']);   // оплата прошла — очищаем
    } else {
        $cardError = 'Проверьте данные карты';
    }
}

$amount = number_format($pay['debt'], 2, ',', ' ') . ' ₽';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оплата — Новосибирскэнергосбыт</title>
    <link rel="icon" type="image/png" href="images/logo.png">
    <link rel="stylesheet" href="style.css">
    <style>
        .site-header { justify-content: center; }
        .pay-wrap {
            max-width: 600px;
            margin: 50px auto;
            border: 1px solid #000000;
            border-radius: 0px;
            padding: 40px 50px;
            text-align: center;
            font-family: sans-serif;
        }
        .pay-title { font-size: 28px; font-weight: bold; margin: 0 0 24px; }
        .pay-row { font-size: 17px; color: #555; margin: 4px 0; }
        .pay-row b { color: #000; }
        .pay-label { color: #888; font-size: 16px; margin: 24px 0 6px; }
        .pay-amount { font-size: 40px; font-weight: bold; margin: 0 0 28px; }
        .pay-field { margin: 0 0 14px; }
        .pay-field .label-for-form { display: block; margin-bottom: 6px; }
        .pay-field .forma { width: 100%; box-sizing: border-box; }
        .pay-cols { display: flex; gap: 14px; }
        .pay-cols .label-for-form { min-height: 2.4em; display: flex; align-items: center; justify-content: center; }
        .pay-cols .pay-field { flex: 1; }
        .pay-error { color: #c00; font-size: 16px; margin: 6px 0 0; }
        .pay-wrap .btn-1 { width: 100%; margin-top: 18px; }
        .pay-back { display: inline-block; margin-top: 20px; color: #555; font-size: 15px; }
        .pay-ok { font-size: 22px; color: green; font-weight: bold; margin: 20px 0; }
        @media (max-width: 700px) {
            .pay-wrap { margin: 50px 20px; }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="logo-area" onclick="location.href='index.php'">
            <img src="images/logo.png" alt="Логотип" class="logo-img">
            <span class="logo-text">Новосибирскэнергосбыт</span>
        </div>
    </header>

    <main>
        <div class="pay-wrap">
            <h1 class="pay-title">Подтверждение оплаты</h1>

            <?php if ($pay['type'] === 'ls'): ?>
                <p class="pay-row">Район: <b><?= htmlspecialchars($pay['district']) ?></b></p>
                <p class="pay-row">Лицевой счёт: <b><?= htmlspecialchars($pay['account']) ?></b></p>
            <?php elseif ($pay['type'] === 'address'): ?>
                <p class="pay-row">Адрес: <b><?= htmlspecialchars($pay['address']) ?></b></p>
                <p class="pay-row">Квартира: <b><?= htmlspecialchars($pay['apartment']) ?></b></p>
            <?php else: ?>
                <p class="pay-row"><b><?= htmlspecialchars($pay['details']) ?></b></p>
            <?php endif; ?>

            <p class="pay-label">Сумма задолженности</p>
            <p class="pay-amount"><?= $amount ?></p>

            <?php if ($paid): ?>
                <p class="pay-ok">Оплата прошла успешно</p>
            <?php else: ?>
                <form method="POST">
                    <div class="pay-field">
                        <label for="cardNumber" class="label-for-form">Номер карты</label>
                        <input class="forma" type="text" id="cardNumber" name="card_number"
                               placeholder="0000 0000 0000 0000" inputmode="numeric" required autofocus>
                    </div>
                    <div class="pay-cols">
                        <div class="pay-field">
                            <label for="cardExp" class="label-for-form">Срок действия</label>
                            <input class="forma" type="text" id="cardExp" name="card_exp"
                                   placeholder="ММ/ГГ" inputmode="numeric" maxlength="5" required>
                        </div>
                        <div class="pay-field">
                            <label for="cardCvc" class="label-for-form">CVC</label>
                            <input class="forma" type="text" id="cardCvc" name="card_cvc"
                                   placeholder="000" inputmode="numeric" maxlength="3" required>
                        </div>
                    </div>
                    <?php if ($cardError): ?>
                        <p class="pay-error"><?= $cardError ?></p>
                    <?php endif; ?>
                    <button type="submit" class="btn-1"><span style="white-space:nowrap">Оплатить</span> <span style="white-space:nowrap"><?= $amount ?></span></button>
                </form>
            <?php endif; ?>

            <a href="index.php" class="pay-back">Вернуться на главную</a>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        // форматирование номера карты по 4 цифры
        const num = document.getElementById('cardNumber');
        if (num) {
            num.addEventListener('input', function () {
                let d = this.value.replace(/\D/g, '').slice(0, 16);
                this.value = d.replace(/(.{4})/g, '$1 ').trim();
            });
        }
        // срок действия ММ/ГГ
        const exp = document.getElementById('cardExp');
        if (exp) {
            exp.addEventListener('input', function () {
                let d = this.value.replace(/\D/g, '').slice(0, 4);
                if (d.length >= 3) d = d.slice(0, 2) + '/' + d.slice(2);
                this.value = d;
            });
        }
        // CVC только цифры
        const cvc = document.getElementById('cardCvc');
        if (cvc) {
            cvc.addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '').slice(0, 3);
            });
        }
    </script>
</body>
</html>
