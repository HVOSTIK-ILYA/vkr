<?php
session_start();
session_destroy();        // завершаем сессию
header('Location: index.php');
exit;
?>