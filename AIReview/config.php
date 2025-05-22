<?php
$host = 'localhost';
$user = 'root';
$password = 'root';
$database = 'ai_review';

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Erro na conexão: " . mysqli_connect_error());
}
?>