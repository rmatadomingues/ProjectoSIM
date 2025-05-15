<?php

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'ai_review';

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Erro na ligação à base de dados: " . mysqli_connect_error());
}
?>