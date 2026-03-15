<?php
$conn = mysqli_connect("mysql", "root", "", "pascual_bravo");

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>