<?php

$host = "100.10.10.100:3456";
$user = "root";
$pass = "belajar";
$db = "mudatani99";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {

    die("Koneksi gagal : " . $conn->connect_error);

}

?>