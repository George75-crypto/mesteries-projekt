<?php

$server = 'localhost';
$user = 'root';
$pass = '';
$data = 'login_egyszeru';

$db = new mysqli($server, $user, $pass, $data);

if ($db->connect_error) {
    die('Nem sikerült csatlakozni az adatbázishoz: ' . $db->connect_error);
}

$db->set_charset('utf8mb4');
