<?php


$servername = "localhost";
$username = "root"; 
$password = "";     
$dbname = "flora_fauna_db"; 


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// Set charset untuk menghindari masalah karakter
$conn->set_charset("utf8mb4");
?>