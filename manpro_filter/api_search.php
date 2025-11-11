<?php


//file koneksi database
require 'db_connect.php';

//header response ke format JSON
header('Content-Type: application/json');

// Ambil parameter filter dari request GET, jika tidak ada, gunakan string kosong
$searchTerm = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';


$sql = "SELECT * FROM spesies WHERE 1=1"; // "1=1" trik agar mudah menambahkan klausa AND

// Array parameter (untuk prepared statements)
$params = [];
$types = ''; // String untuk tipe data parameter (s = string)

// Tambahkan filter pencarian jika ada
if (!empty($searchTerm)) {
    $sql .= " AND (nama_umum LIKE ? OR nama_ilmiah LIKE ?)";
    $likeTerm = "%" . $searchTerm . "%";
    array_push($params, $likeTerm, $likeTerm);
    $types .= 'ss';
}

// Tambahkan filter kategori jika ada
if (!empty($category)) {
    $sql .= " AND kategori = ?";
    array_push($params, $category);
    $types .= 's';
}

// Tambahkan filter status jika ada
if (!empty($status)) {
    $sql .= " AND status_konservasi = ?";
    array_push($params, $status);
    $types .= 's';
}

// Gunakan Prepared Statements untuk mencegah SQL Injection 
$stmt = $conn->prepare($sql);

if ($stmt) {
    // Bind parameter jika ada
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    // Eksekusi query
    $stmt->execute();
    $result = $stmt->get_result();

    // Ambil semua hasil ke dalam array
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    // Kembalikan hasil dalam format JSON
    echo json_encode($data);

    // Tutup statement
    $stmt->close();
} else {
    // Jika ada error pada persiapan query
    echo json_encode(['error' => 'Query Gagal: ' . $conn->error]);
}


$conn->close();
?>