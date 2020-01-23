<?php

$host = '127.0.0.1';
$db   = 'dbase';
$user = 'root';
$pass = '';
$charset = 'utf8';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, $user, $pass, $opt);

switch ($argv[1]) {
    case 'addNode':
        if (isset($argv[2])){
            $title = $argv[2];
        } else {
            echo 'Error: 2nd argument must be a title';
            break;
        }
        if (isset($argv[3])){
            $parent_id = $argv[3];
        } else {
            $stmt = $pdo->prepare('INSERT INTO category (title,lft,rgt,lvl) VALUES (:title, :lft, :rgt, :lvl)');
            $stmt->execute(['title' => $title, 'lft' => 1, 'rgt' => 2, 'lvl' => 1]);

            $stmt = $pdo->prepare('SELECT MAX(id) FROM category');
            $stmt->execute();
            $id = $stmt->fetchColumn();

            echo 'Node "'.$title.'" has been added with id #'.$id;
            break;
        }

        $stmt = $pdo->prepare('SELECT rgt,lvl FROM category WHERE id = :id');
        $stmt->execute(['id' => $parent_id]);
        $parent = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($parent) {
            $stmt = $pdo->prepare('UPDATE category SET rgt = rgt + 2 WHERE rgt >= :rgt');
            $stmt->execute(['rgt' => $parent['rgt']]);
            $stmt = $pdo->prepare('UPDATE category SET lft = lft + 2 WHERE lft > :rgt');
            $stmt->execute(['rgt' => $parent['rgt']]);

            $stmt = $pdo->prepare('INSERT INTO category (title,lft,rgt,lvl) VALUES (:title, :lft, :rgt, :lvl)');
            $stmt->execute(['title' => $title, 'lft' => $parent['rgt'], 'rgt' => $parent['rgt'] + 1, 'lvl' => $parent['lvl'] + 1]);

            $stmt = $pdo->prepare('SELECT MAX(id) FROM category');
            $stmt->execute();
            $id = $stmt->fetchColumn();

            echo 'Node "'.$title.'" has been added with id #'.$id;
        } else {
            echo 'Error, node with id #'.$argv[3].' is not found';
        }
        break;
    case 'deleteNode':
        $id = $argv[2];

        $stmt = $pdo->prepare('SELECT id FROM category where id = :id');
        $stmt->execute(['id' => $id]);
        $id = $stmt->fetchColumn();

        if ($id) {
            $stmt = $pdo->prepare('SELECT rgt, lft, lvl FROM category WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $pdo->prepare('DELETE FROM category WHERE id = :id');
            $stmt->execute(['id' => $id]);

            $stmt = $pdo->prepare('UPDATE category SET lft = lft - 1, rgt = rgt - 1, lvl = lvl - 1 WHERE lft >= :lft AND rgt <= :rgt');
            $stmt->execute(['lft' => $current['lft'],'rgt' => $current['rgt']]);
            $stmt = $pdo->prepare('UPDATE category SET lft = lft - 2 WHERE lft >= :rgt');
            $stmt->execute(['rgt' => $current['rgt']]);
            $stmt = $pdo->prepare('UPDATE category SET rgt = rgt - 2 WHERE rgt >= :rgt');
            $stmt->execute(['rgt' => $current['rgt']]);

            echo 'Node id #'.$id.' has been deleted';
        } else {
            echo 'Error, node with id #'.$argv[2].' is not found';
        }
        break;
    case 'renameNode':
        if (isset($argv[2]) && isset($argv[3])) {
            $id = $argv[2];
            $title = $argv[3];
        } else {
            echo 'Error, 2nd argument must be an id and 3rd argument must be a title';
            break;
        }

        $stmt = $pdo->prepare('SELECT id FROM category where id = :id');
        $stmt->execute(['id' => $id]);
        $id = $stmt->fetchColumn();

        if ($id) {
            $stmt = $pdo->prepare('UPDATE category SET title = :title WHERE id = :id');
            $stmt->execute(['title' => $title, 'id' => $id]);
            echo 'Node id #'.$id.' changed to "'.$title.'"';
        } else {
            echo 'Error, node with id #'.$argv[2].' is not found';
        }
        break;
}