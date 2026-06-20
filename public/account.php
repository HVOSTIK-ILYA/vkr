<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {   // не вошёл — на главную
        header('Location: index.php');
        exit;
    }

    require 'db.php';

    // нажата кнопка "Оплатить" в карточке — готовим данные и идём на payment.php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_id'])) {
        $stmt = mysqli_prepare($conn,
            "SELECT details, amount FROM payments WHERE id = ? AND user_id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'ii', $_POST['pay_id'], $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $payDetails, $payAmount);
        if (mysqli_stmt_fetch($stmt)) {
            mysqli_stmt_close($stmt);
            $_SESSION['pay'] = [
                'type'    => 'account',
                'details' => $payDetails,
                'debt'    => $payAmount,
            ];
            header('Location: payment.php');
            exit;
        }
        mysqli_stmt_close($stmt);
    }

    $stmt = mysqli_prepare($conn,
        "SELECT id, details, amount FROM payments WHERE user_id = ? AND amount > 0 ORDER BY id");
    mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет — Новосибирскэнергосбыт</title>
    <link rel="icon" type="image/png" href="images/logo.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="site-header">
        <div class="logo-area" onclick="location.href='index.php'">
            <img src="images/logo.png" alt="Логотип" class="logo-img">
            <span class="logo-text">Новосибирскэнергосбыт</span>
        </div>
        <div class="popout-container">
            <a href="logout.php" class="account-Btn">Выйти</a>
        </div>
    </header>

    <main class="account-main">
        <h1 class="account-title">Ваши задолженности</h1>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="debt-cards">
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="debt-card">
                        <p class="debt-info"><?= htmlspecialchars($row['details']) ?></p>
                        <p class="debt-amount"><?= number_format($row['amount'], 2, '.', ' ') ?> ₽</p>
                        <form method="POST">
                            <input type="hidden" name="pay_id" value="<?= (int)$row['id'] ?>">
                            <button type="submit" class="btn-1">Оплатить</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="no-debt">Задолженностей нет</p>
        <?php endif; ?>
    </main>

    <?php include 'footer.php'; ?>

<?php mysqli_close($conn); ?>
</body>
</html>