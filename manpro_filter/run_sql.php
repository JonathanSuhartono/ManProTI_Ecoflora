<?php
require 'db_connect.php';

// Baca file SQL
$sql = file_get_contents('../../add_coordinates.sql');

// Split menjadi perintah SQL individual
$statements = array_filter(array_map('trim', explode(';', $sql)));

foreach ($statements as $statement) {
    if (!empty($statement)) {
        if ($conn->query($statement) === TRUE) {
            echo "Query berhasil: " . substr($statement, 0, 50) . "...\n";
        } else {
            echo "Error: " . $conn->error . "\n";
        }
    }
}

$conn->close();
echo "Selesai menjalankan SQL.\n";
?>