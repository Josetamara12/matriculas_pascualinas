<?php

$host = "mariadb";
$user = "root";
$password = "root";
$db = "pascual_bravo";

$conn = mysqli_connect($host, $user, $password, $db);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

?>