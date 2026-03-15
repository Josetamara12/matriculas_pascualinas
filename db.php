<?php
$conn = mysqli_connect("mariadb", "root", "", "pascual_bravo");

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>