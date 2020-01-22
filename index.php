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
        }
        if (isset($argv[3])){
            $parent_id = $argv[3];
        } else {
            $parent_id = 1;
        }



        break;
}