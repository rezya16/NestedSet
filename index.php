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
            return 'Error: 2nd argument must be a string';
        }
        if (isset($argv[3])){
            $parent_id = $argv[3];
        } else {
            $parent_id = 1;
        }


        $stmt = $pdo->prepare('SELECT rgt,lvl FROM category WHERE id = :id');
        $stmt->execute(['id' => $parent_id]);
        $parent = $stmt->fetch(PDO::FETCH_ASSOC);


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
        break;
    case 'deleteNode':
        $id = $argv[2];

        $stmt = $pdo->prepare('SELECT id FROM category where id = :id');
        $stmt->execute(['id' => $id]);
        $id = $stmt->fetchColumn();

        if ($id) {
            $stmt = $pdo->prepare('DELETE FROM category WHERE id = :id');
            $stmt->execute(['id' => $id]);

            echo 'Node id #'.$id.' has been deleted';
        } else {
            echo 'Error, node with id #'.$argv[2].' is not found';
        }
        break;
}