<?php
include 'config.php';
$message = "Войдите или зарегистрируйтесь";
if ($_POST) {
    $login = trim(htmlspecialchars(stripslashes(mb_strtolower($_POST['login']))));
    $pass = trim(htmlspecialchars(stripslashes(md5(mb_strtolower($_POST['password'])))));
    $us_pass_q = $db->prepare("SELECT id, login, password FROM user WHERE login = ?");
    $us_pass_q->execute([$login]);
    $login_pass_fetch = $us_pass_q->fetch(PDO::FETCH_ASSOC);
    $id_user = $login_pass_fetch['id'];
    if (isset($_POST['enter'])) {
        if ($login_pass_fetch['password'] === $pass) {
            setcookie("login", $login, time() + 3600);
            setcookie("id_user", $id_user, time() + 3600);
            header('Location: index.php');
        } else {
            $message = 'Вы указали неверные логин или пароль';
        }
    } elseif (isset($_POST['registration']) AND !empty($_POST['login']) AND !empty($_POST['password'])) {
        if ($login_pass_fetch['login'] === $login) {
            $message = 'Пользователь с таким именем уже существует';
        } elseif (empty($_POST['login']) || empty($_POST['password'])) {
            $message = 'Введите данные.';
        } elseif (!empty($_POST['login']) AND !empty($_POST['password'])) {
            $user_insert = $db->prepare("INSERT INTO user (login, password) VALUES (?, ?)");
            $user_insert->execute([$login, $pass]);
            $message = "Вы зарегистрированы!";

        }
    } else {
        $message = "Введите данные";
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Страница регистрации</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<p><?= $message ?></p>
<form action="" method="post">
    <input class="input_reg" type="text" name="login" placeholder="Логин">
    <input class="input_reg" type="password" name="password" placeholder="Пароль">
    <input class="input_reg" type="submit" name="enter" value="Вход">
    <input class="input_reg" type="submit" name="registration" value="Регистрация">
</form>

</body>
</html>