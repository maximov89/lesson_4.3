<?php
include 'config.php';

if (isset($_COOKIE["login"]))
{
$login = ($_COOKIE["login"]);
$id_user = $_COOKIE["id_user"];
$select = "SELECT * FROM user JOIN task ON user.id=task.user_id WHERE user_id = $id_user ";
$submit = 'Добавить';

$user_prep = $db->prepare("SELECT login FROM user WHERE id = ?");
$user_prep->execute([$id_user]);
$user_login = $user_prep->fetch(PDO::FETCH_ASSOC)['login'];
if (isset($_GET['action'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === (string)'done') {
        $done_task = $db->prepare('UPDATE task SET is_done = TRUE WHERE id = ? LIMIT 1');
        $done_task->execute([$id]);
        $task_description = $done_task->fetch(PDO::FETCH_ASSOC)['description'];
    }

    if ($_GET['action'] === (string)'delete') {
        $del_task = $db->prepare('DELETE FROM task WHERE id = ? LIMIT 1');
        $del_task->execute([$id]);
        $task_description = $del_task->fetch(PDO::FETCH_ASSOC)['description'];
    }

    if ($_GET['action'] === (string)'edit') {
        $edit_task = $db->prepare('SELECT * FROM task WHERE id = ?');
        $edit_task->execute([$id]);
        $task_description = $edit_task->fetch(PDO::FETCH_ASSOC)['description'];
        $submit = 'Сохранить';
    }
}

if (isset($_POST['add']) AND (!empty($_POST['add']))) {
    $desc = $_POST['add'];
    $id = (int)$_POST['id'];
    if (isset($_GET['action']) AND $_GET['action'] === (string)'edit') {
        $id_get = (int)$_GET['id'];
        $rows = $db->prepare('UPDATE task SET description = ? WHERE id = ? LIMIT 1');
        $rows->execute([$desc, $id]);
        header('Refresh: 0; index.php');
    } else {
        $rows = $db->prepare('INSERT INTO task (description, date_added, is_done, assigned_user_id, user_id) VALUES (?, CURRENT_TIMESTAMP, ?, ?, ?)');
        $exec_rows = $rows->execute([$_POST['add'], false, $id_user, $id_user]);
    }
}

if (isset($_POST['sort'])) {
    $sortBy = $_POST['sortBy'];
    $select .= " ORDER BY $sortBy";
}
if (isset($_POST['assign_to'])) {
    $id = (int)$_POST['id'];
    $login_assign_to = $_POST['assign_to'];
    $prepare_assign_user = $db->prepare("SELECT id, login FROM user WHERE login=?");
    $prepare_assign_user->execute([$login_assign_to]);
    $id_assign_user = $prepare_assign_user->fetch(PDO::FETCH_ASSOC)["id"];
    $update_prep_task_user = $db->prepare("UPDATE task SET assigned_user_id=? WHERE id=?");
    $update_prep_task_user->execute([$id_assign_user, $id]);
}

?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1><?= $user_login ?> TODO list</h1>
<div style="display: inline-block;">
    <form method="post">
        <input type="hidden" name="id" value="<?= isset($_GET['id']) ? $_GET['id'] : "" ?>">
        <input type="text" name="add" value="<?= isset($task_description) ? $task_description : ''; ?>"
               placeholder="Введите задание">
        <input type="submit" value="<?= $submit ?>">
    </form>
</div>
<div style="display: inline-block;">
    <form method="post" style="">
        <label for="sort">Сортировать по</label>
        <select name="sortBy" id="sort">
            <option value="date_added">Дата добавления</option>
            <option value="is_done">Статус</option>
            <option value="description">Описание</option>
        </select>
        <input type="submit" value="Отсортировать" name="sort">
    </form>
</div>
<table>
    <tr>
        <th>Описание задачи</th>
        <th>Дата добавления</th>
        <th>Статус</th>
        <th>Корректировка</th>
        <th>Ответственный</th>
        <th>Автор</th>
        <th>Закрепить задачу за пользователем</th>
    </tr>
    <?php
    $row1 = $db->prepare($select);
    $row1->execute();
    while ($row = $row1->fetch(PDO::FETCH_ASSOC)) :
            $id = $row['id'];
            $users = [];
            ?>
            <tr>
                <td><?= $row['description'] ?></td>
                <td><?= $row['date_added'] ?></td>
                <td><?php if ($row['is_done'] == false) {
                        echo '<span style="color: orange;">В процессе</span>';
                    } else {
                        echo '<span style="color: green;">Выполнено</span>';
                    } ?>
                </td>
                <td>
                    <a href="index.php?id=<?= $id ?>&action=edit">Изменить</a>
                    <a href="index.php?id=<?= $id ?>&action=delete">Удалить</a>
                    <?php if (($row['user_id'] === $row['assigned_user_id']) AND ($row['is_done'] == false)) { ?>
                        <a href="index.php?id=<?= $id ?>&action=done">Выполнить</a>
                    <?php } ?>
                </td>
                <td>
                    <?php if (($id_user === $row['user_id']) AND ($row['user_id'] === $row['assigned_user_id'])) {
                        echo 'Вы';
                    } else {  
                        $assign_user_name->execute([$row['assigned_user_id']]);
                        $assign_user_name_f = $assign_user_name->fetch(PDO::FETCH_ASSOC)['login'];
                        echo "$assign_user_name_f";
                    }
                    ?>
                </td>
                <td><?= $user_login ?></td>
                <td>
                    <form action="" method="post">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <select name="assign_to">
                            <?php
                            foreach ($db->query("SELECT login FROM user") as $user):
                                echo "<option>" . $user['login'] . "</option>";
                            endforeach; ?>
                        </select>
                        <input type="submit" value="Переложить ответственность" name="assign">
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
</table>
<p>Также посмотрите что от Вас требуют другие люди</p>
<table>
    <tr>
        <th>Описание задачи</th>
        <th>Дата добавления</th>
        <th>Статус</th>
        <th>Корректировка</th>
        <th>Ответсвенный</th>
        <th>Автор</th>
    </tr>
    <?php
    $select = "SELECT * FROM user JOIN task ON user.id=task.user_id WHERE assigned_user_id = $id_user ";
    $row2 = $db->prepare($select);
    $row2->execute();
    $fetch = $row2->fetchAll(PDO::FETCH_ASSOC);
    foreach ($fetch as $fetch_values) {
        if ($fetch_values['assigned_user_id'] !== $fetch_values['user_id']) {?>
            <tr>
                <td><?= $fetch_values['description'] ?></td>
                <td><?= $fetch_values['date_added'] ?></td>
                <td><?php if ($fetch_values['is_done'] == false) {
                        echo '<span style="color: orange;">В процессе</span>';
                    } else {
                        echo '<span style="color: green;">Выполнено</span>';
                    } ?>
                </td>
                <td>
                    <a href="index.php?id=<?= $fetch_values['id'] ?>&action=edit">Изменить</a>
                    <?php if ($fetch_values['is_done'] == false) : ?>
                        <a href="index.php?id=<?= $fetch_values['id'] ?>&action=done">Выполнить</a>
                    <?php endif; ?>

                </td>
                <td>
                    <?php
                    $assign_user_name->execute([$fetch_values['assigned_user_id']]);
                    $assign_user_name_f = $assign_user_name->fetch(PDO::FETCH_ASSOC)['login'];
                    echo "$assign_user_name_f";
                    ?>
                </td>
                <td>
                    <?php
                    $assign_user_name = $db->prepare("SELECT login FROM user WHERE id=?");
                    $assign_user_name->execute([$fetch_values['user_id']]);
                    echo "$assign_user_name_f";
                    ?>
                </td>
            </tr>
            <?php
        }
    }

    ?>
</table>
<br>
<a href="logout.php">Выйти</a>
<?php
}
else {
    echo '<a href="registration.php">Войдите или зарегистрируйтесь</a>';
} ?>
</body>
</html>
