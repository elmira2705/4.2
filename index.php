<?php
    define('DB_DRIVER', "mysql");
    define('DB_HOST', "localhost");
    define('DB_NAME', "test");
    define('DB_USER', "root");
    define('DB_PASS', "");

    $db_connect = (DB_DRIVER . ':host=' . DB_HOST . ';dbname=' . DB_NAME . '; charset=utf8');
    $db = new PDO($db_connect, DB_USER, DB_PASS);
    $rows=$db->exec("CREATE TABLE `tasks`(
`id` int(11) NOT NULL AUTO_INCREMENT,
`description` text NOT NULL,
`is_done` tinyint(4) NOT NULL DEFAULT '0',
`date_added` datetime NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$select = "SELECT * FROM tasks";
$submit = "Добавить";
    if(isset($_GET['action'])) {
    $id = $_GET['id'];
    if($_GET['action'] === (string)'done'){
        $edit_task = $db->prepare('UPDATE tasks SET is_done = TRUE WHERE id = ? LIMIT 1');
        $edit_task->execute([$id]);
    }
    if($_GET['action'] === (string)'delete'){
        $del_task = $db->prepare('DELETE FROM tasks WHERE id = ? LIMIT 1');
        $del_task->execute([$id]);
    }
    if($_GET['action'] === (string)'edit'){
        $edit_task = $db->prepare('SELECT * FROM tasks WHERE id = ?');
        $edit_task->execute([$id]);
        $task_description = $edit_task->fetch(PDO::FETCH_ASSOC)['description'];
        $submit = 'Сохранить';}
    }
    if(isset($_POST['add']) AND (!empty($_POST['add']))) {
        $desc = $_POST['add'];
        $id = $_POST['id'];
        if(isset($_GET['action']) AND $_GET['action'] === (string)'edit') {
            $id_get = $_GET['id'];
            $rows = $db->prepare('UPDATE tasks SET description = ? WHERE id = ? LIMIT 1');
            $rows->execute([$desc, $id]);
            header('Refresh: 0; index.php');
        } else {
        $rows = $db->prepare('INSERT INTO tasks (description, is_done, date_added) VALUES (?, ?, CURRENT_TIMESTAMP)');
        $exec_rows = $rows->execute([$_POST['add'], false]);
        }
    }
    if (isset($_POST['sort'])){
        $sortBy = addslashes($_POST['sortBy']);
        $select = "SELECT * FROM tasks ORDER BY $sortBy";
    }
    echo '</pre/>';

?>

<!doctype html>
<html lang="ru">
<head>
    <title>Домашнее задание к лекции 4.2 «Запросы SELECT, INSERT, UPDATE и DELETE»</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>Список дел на сегодня</h1>
    <div style="display: inline-block;">
        <form method="post">
            <input type="hidden" name="id" value="<?= isset($_GET['id']) ? $_GET['id'] : "" ?>">
            <input type="text" name="add" value="<?= isset($task_description) ? $task_description : '';?>" placeholder="Введите задание">
            <input type="submit" value="<?= $submit?>">
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
        <th>Статус</th>
        <th>Дата добавления</th>
        <th></th>
    </tr>
<?php
$row1 = $db->prepare($select);
$row1->execute();

while($row = $row1->fetch(PDO::FETCH_ASSOC)) :
    $id = $row['id'];
?>
    <tr>
        <td><?=$row['description']?></td>
        <td><?=$row['is_done']==false ? 'В процессе' : 'Выполнено'; ?></td>
        <td><?=$row['date_added']?></td>
        <td>
            <a href="index.php?id=<?=$id?>&action=edit">Изменить</a>
            <a href="index.php?id=<?=$id?>&action=delete">Удалить</a>
            <a href="index.php?id=<?=$id?>&action=done">Выполнить</a>
        </td>
    </tr>
<?php endwhile; ?>
</table>
</body>
</html>
