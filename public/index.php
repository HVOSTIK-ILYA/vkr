<?php
session_start();
$message      = '';
$showCode     = false;  // окно кода из СМС (по долгу)
$codeError    = '';
$showMaxCode  = false;  // окно кода из приложения МАКС
$maxCodeError = '';
$showDownload = false;  // сообщение "скачайте МАКС"

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Код из СМС (по долгу) ---
    if (isset($_POST['sms_code'])) {
        if ($_POST['sms_code'] === '0000') {
            header('Location: payment.html');
            exit;
        }
        $showCode  = true;
        $codeError = 'Неверный код';
    }
    // --- Код из приложения МАКС ---
    elseif (isset($_POST['max_code'])) {
        if ($_POST['max_code'] === '0000') {
            header('Location: account.php'); // верный код — в личный кабинет
            exit;
        }
        $showMaxCode  = true;
        $maxCodeError = 'Неверный код';
    }
    // --- Проверка телефона в таблице users ---
    elseif (isset($_POST['phone'])) {
        $conn = mysqli_connect('MariaDB-11.4', 'root', '', 'vkr');
        if (!$conn) { die('Ошибка подключения: ' . mysqli_connect_error()); }
        mysqli_set_charset($conn, 'utf8mb4');

        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE phone = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $_POST['phone']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $userId);
        $found = mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        if ($found) {
            $_SESSION['user_id'] = $userId;  // запомнили, кто вошёл
            $showMaxCode = true;
        } else {
            $showDownload = true;
        }
    }
    // --- Данные форм оплаты (адрес / лицевой счёт) ---
    else {
        $conn = mysqli_connect('MariaDB-11.4', 'root', '', 'vkr');
        if (!$conn) { die('Ошибка подключения: ' . mysqli_connect_error()); }
        mysqli_set_charset($conn, 'utf8mb4');

        $debt = null;

        if (isset($_POST['account_number'])) {
            $stmt = mysqli_prepare($conn,
                "SELECT debt FROM payments_ls WHERE district = ? AND account_number = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, 'ss', $_POST['district'], $_POST['account_number']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $debt);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);
        } elseif (isset($_POST['address'])) {
            $stmt = mysqli_prepare($conn,
                "SELECT debt FROM payments_address WHERE address = ? AND apartment = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, 'ss', $_POST['address'], $_POST['apartment']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $debt);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);
        }

        mysqli_close($conn);

        if ($debt !== null && $debt > 0) {
            $showCode = true;
        } else {
            $message = 'Задолженности нет';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новосибирскэнергосбыт</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="site-header">
        <div class="logo-area">
            <img src="images/logo.png" alt="Логотип" class="logo-img">
            <span class="logo-text">Новосибирскэнергосбыт</span>
        </div>
        <div class="popout-container">
            <button id="accountBtn" class="account-Btn">Личный кабинет</button>            
        </div>        
    </header>
    <main>
        <div class="display-1">
            <section class="block-1">
                <h1 class="block-1-text">В мобильном приложении «Платосфера» теперь доступна новая услуга - «Автоплатеж за свет»</h1>
                <img src="images/auto-plata.png" class="auto-plata">
            </section>
            <section class="info-area">
                <button class="info-btn" id="info-btn-1">Для физических лиц</button>
                <button class="info-btn" id="info-btn-2">Для представителей органов власти</button>
                <button class="info-btn" id="info-btn-3">Для юридических лиц</button>
                <button class="info-btn" id="info-btn-4">Сфера энергии</button>
            </section>
        </div>
        <div class="display-2">
            <section class="block-2">
                <h1 class="h1">По адресу</h1>
                <img class="img-block" src="images/by-adress.png">
            </section>
            <form class="payment-area-1" id="form-adress" method="POST">
                <div class="columns">
                    <section class="column-1">
                        <img class="img-column-1" src="images/punkt-1.png">
                        <label for="addressInput" class="label-for-form">Населенный пункт, улица (микрорайон, квартал, ДНП, иное), номер дома</label>
                        <input class="forma" type="text" id="addressInput" name="address" placeholder="Введите адрес" required>
                    </section>
                    <section class="column-2">
                        <img class="img-column-1" src="images/number-appartment.png">
                        <label for="number-appartment-Input" class="label-for-form">Номер квартиры (если есть)</label>
                        <input class="forma" type="text" id="number-appartment-Input" name="apartment" placeholder="Введите номер квартиры" maxlength="3" inputmode="numeric" pattern="\d{1,3}" required>
                    </section>
                </div>
                <div class="btn">
                    <button class="btn-1" type="submit">Оплатить</button>
                </div>                
            </form>            
        </div>
        <div class="display-3">
            <section class="block-3">
                <h1 class="h1">По лицевому счету</h1>
                <img class="img-block" src="images/by-ls.png">
            </section>
            <form class="payment-area-2" id="form-ls"  method="POST">
                <div class="columns">
                    <section class="column-1">
                        <img class="img-column-1" src="images/distrikt.png">
                        <label for="districtInput" class="label-for-form">Район</label>
                        <select class="forma" id="districtInput" name="district" required>
                            <option value="">Выберите район</option>
                            <option value="Октябрьский">Октябрьский</option>
                            <option value="Ленинский">Ленинский</option>
                            <option value="Кировский">Кировский</option>
                            <option value="Советский">Советский</option>
                            <option value="Первомайский">Первомайский</option>
                            <option value="Центральный">Центральный</option>
                            <option value="Железнодорожный">Железнодорожный</option>
                            <option value="Заельцовский">Заельцовский</option>
                            <option value="Калининский">Калининский</option>
                            <option value="Дзержинский">Дзержинский</option>
                        </select>
                    </section>
                    <section class="column-2">
                        <img class="img-column-1" src="images/number-ls.png">
                        <label for="lsInput" class="label-for-form">Номер лицевого счета</label>
                        <input class="forma" type="text" id="lsInput" name="account_number" placeholder="Введите 6 цифр" maxlength="6" pattern="\d{6}" required>
                    </section>
                </div>
                <div class="btn">
                    <button class="btn-1" type="submit">Оплатить</button>
                </div>                
            </form>            
        </div>
    </main>
    <footer></footer>
    <div class="info-overlay" id="modal-1">
        <div class="info-box">
            <h2>Для физических лиц мы предоставляем:</h2>
            <ol>
                <li>Детализацию счета</li>
                <li>Поддержку если звонят по чужому адресу</li>
            </ol>
        </div>
    </div>
    <div class="info-overlay" id="modal-2">
        <div class="info-box">
            <h2>Для представителей органов власти мы предоставляем:</h2>
            <ol>
                <li>Обещанный платеж</li>
                <li>Электронный документооборот</li>
                <li>Калькулятор расчета стоимости внедрения ФСКУЭ</li>
            </ol>
        </div>
    </div>
    <div class="info-overlay" id="modal-3">
        <div class="info-box">
            <h2>Для юридических лиц мы предоставляем:</h2>
            <ol>
                <li>Заключить договор</li>
                <li>Личный кабинет</li>
                <li>Направить обращение</li>
                <li>Установить электрозарядную станцию</li>
            </ol>
        </div>
    </div>
    <div class="info-overlay" id="modal-4">
        <div class="info-box">
            <h2>В сфере энергии мы предоставляем:</h2>
            <ol>
                <li>Направление обращения от участника СППС</li>
                <li>Размещение рекламы в мобильном приложении «Платосфера»</li>
                <li>Система платежей и переводов «Сфера энергии»</li>
            </ol>
        </div>
    </div>
    <div class="info-overlay" id="modal-phone">
        <div class="info-box">
            <h2>Вход в личный кабинет</h2>
            <form method="POST">
                <input type="text" id="phoneInput" name="phone"
                    placeholder="+7 (000) 000-00-00" required
                    style="font-size:20px; padding:10px; width:230px; text-align:center;">
                <br>
                <button type="submit" class="modal-submit">Продолжить</button>
            </form>
        </div>
    </div>

    <!-- Окно: код из приложения МАКС (номер найден) -->
    <?php if ($showMaxCode): ?>
    <div class="info-overlay open">
        <div class="info-box">
            <h2>Код из приложения МАКС</h2>
            <form method="POST">
                <input type="text" name="max_code" maxlength="4" pattern="\d{4}"
                    placeholder="0000" required autofocus
                    style="font-size:26px; padding:10px; width:140px; text-align:center; letter-spacing:8px;">
                <?php if ($maxCodeError): ?>
                    <p style="color:#f88; margin:10px 0 0;"><?= $maxCodeError ?></p>
                <?php endif; ?>
                <br>
                <button type="submit" class="modal-submit">Подтвердить</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Окно: предложение скачать МАКС (номер не найден) -->
    <?php if ($showDownload): ?>
    <div class="info-overlay open">
        <div class="info-box">
            <h2>Установите приложение МАКС</h2>
            <p>Вашего номера нет в системе.<br>Скачайте МАКС, чтобы войти в личный кабинет.</p>
            <a href="https://max.ru" target="_blank" class="modal-submit">Скачать МАКС</a>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
        document.getElementById('accountBtn').onclick = () => document.getElementById('modal-phone').classList.add('open');

        // маска телефона: +7 (000) 000-00-00
        const phoneInput = document.getElementById('phoneInput');
        phoneInput.addEventListener('input', function () {
            let d = this.value.replace(/\D/g, '');          // только цифры
            if (d[0] === '7' || d[0] === '8') d = d.slice(1); // убрать ведущую 7/8
            d = d.slice(0, 10);                              // максимум 10 цифр
            let r = '+7';
            if (d.length >= 1) r += ' (' + d.substring(0, 3);
            if (d.length >= 4) r += ') ' + d.substring(3, 6);
            if (d.length >= 7) r += '-' + d.substring(6, 8);
            if (d.length >= 9) r += '-' + d.substring(8, 10);
            this.value = r;
        });
    document.getElementById('info-btn-1').onclick = () => document.getElementById('modal-1').classList.add('open');
    document.getElementById('info-btn-2').onclick = () => document.getElementById('modal-2').classList.add('open');
    document.getElementById('info-btn-3').onclick = () => document.getElementById('modal-3').classList.add('open');
    document.getElementById('info-btn-4').onclick = () => document.getElementById('modal-4').classList.add('open');

    // закрытие: по кнопке "Закрыть" и по клику на тёмный фон
    document.querySelectorAll('.info-overlay').forEach(overlay => {        
        overlay.onclick = e => { if (e.target === overlay) overlay.classList.remove('open'); };
    });
    </script>
    <script>
const addressInput = document.getElementById('addressInput');


addressInput.addEventListener('input', function () {
    // словарь: что ввёл -> на что заменить
    const map = {
        'улица': 'ул. ',
        'микрорайон': 'мкр-н. ',
        'квартал': 'кв-л. '
    };

    // 1. Первое слово + пробел -> сокращение (если слово есть в словаре)
    const m = this.value.match(/^([А-Яа-яЁёA-Za-z]+)\s/);
    if (m) {
        const word = m[1].toLowerCase(); // чтобы "Улица" и "улица" работали одинаково
        if (map[word]) {
            this.value = this.value.replace(/^[А-Яа-яЁёA-Za-z]+\s/, map[word]);
        }
    }

    // 2. После запятой — первая буква названия заглавная
    if (this.value.includes(',')) {
        this.value = this.value.replace(/^(ул\. |мкр-н\. |кв-л\. )(\p{L})/u, (s, prefix, letter) => prefix + letter.toUpperCase());
    }
    if (this.value.includes(',')) {
        this.value = this.value.replace(/^(ул\. )(\p{L})/u, (m, prefix, letter) => prefix + letter.toUpperCase());
    }
});

// Проверка формата при отправке формы адреса
document.getElementById('form-adress').addEventListener('submit', function (e) {
    // формат: ул. Название, номер дома (цифры или цифры+буквы)
    const pattern = /^ул\.\s*.+,\s*\d+[А-Яа-яA-Za-z]*$/;
    if (!pattern.test(addressInput.value.trim())) {
        e.preventDefault();
        alert('Адрес должен быть в формате: ул. Название, 12 или 12А');
    }
});
</script>
</body>
<?php if ($showCode): ?>
    <div style="position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.6);
                display:flex; align-items:center; justify-content:center;">
        <div style="background: linear-gradient(135deg, #b948b5, #2575fc);; padding:40px 60px; border-radius:12px; text-align:center;
                    font-family:sans-serif; box-shadow:0 10px 40px rgba(0,0,0,0.3);">
            <p style="font-size:24px; margin:0 0 20px; color:white">Введите код из приложения MAX</p>
            <form method="POST">
                <input type="text" name="sms_code" maxlength="4" pattern="\d{4}"
                       placeholder="" required autofocus
                       style="font-size:28px; text-align:center; width:150px; letter-spacing:10px;
                              padding:10px; border:1px solid #ccc; border-radius:8px;">
                <?php if ($codeError): ?>
                    <p style="color:#c00; font-size:16px; margin:12px 0 0;"><?= $codeError ?></p>
                <?php endif; ?>
                <br>
                <button type="submit" style="margin-top:20px; font-size:18px; padding:10px 30px;
                        background:#0066cc; color:#fff; border:none; border-radius:8px; cursor:pointer;">
                    Подтвердить
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>
<?php if ($message): ?>
    <div style="position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.6);
                display:flex; align-items:center; justify-content:center;">
        <div style="background:#fff; padding:40px 60px; border-radius:12px; text-align:center;
                    font-family:sans-serif; box-shadow:0 10px 40px rgba(0,0,0,0.3);">
            <p style="font-size:28px; color:#c00; margin:0 0 20px;"><?= $message ?></p>
            <a href="index.php" style="font-size:18px; color:#0066cc;">Закрыть</a>
        </div>
    </div>
<?php endif; ?>
</html>