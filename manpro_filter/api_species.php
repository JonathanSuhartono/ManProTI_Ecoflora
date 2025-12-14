<?php
// File koneksi database
require 'db_connect.php';

// Header response ke format JSON
header('Content-Type: application/json');

// Fungsi untuk menormalkan status konservasi
function normalizeStatus($status) {
    // Handle null/empty
    if ($status === null) return 'unknown';

    $orig = trim($status);
    $low = strtolower($orig);

    // First check explicit IUCN codes (word boundaries) to avoid substring collisions
    if (preg_match('/\bcr\b/i', $low) || strpos($low, 'kritis') !== false) {
        return 'critically-endangered';
    }
    if (preg_match('/\ben\b/i', $low) || strpos($low, 'terancam punah') !== false || strpos($low, 'terancam (en)') !== false) {
        return 'endangered';
    }
    if (preg_match('/\bvu\b/i', $low) || strpos($low, 'rentan') !== false) {
        return 'vulnerable';
    }
    if (preg_match('/\bnt\b/i', $low) || strpos($low, 'hampir terancam') !== false) {
        return 'near-threatened';
    }
    if (preg_match('/\blc\b/i', $low) || strpos($low, 'risiko rendah') !== false) {
        return 'least-concern';
    }
    if (preg_match('/\bew\b/i', $low) || stripos($low, 'punah') !== false && stripos($low, 'alam') !== false) {
        return 'extinct-wild';
    }
    if (preg_match('/\bdd\b/i', $low) || strpos($low, 'informasi kurang') !== false) {
        return 'data-deficient';
    }
    if (preg_match('/\bne\b/i', $low) || strpos($low, 'belum dievaluasi') !== false || stripos($low, 'domestik') !== false || stripos($low, 'hibrida') !== false) {
        return 'not-evaluated';
    }

    // Fallback: normalize known English words
    if (strpos($low, 'endangered') !== false) return 'endangered';
    if (strpos($low, 'vulnerable') !== false) return 'vulnerable';
    if (strpos($low, 'near-threatened') !== false || strpos($low, 'near threatened') !== false) return 'near-threatened';
    if (strpos($low, 'least-concern') !== false || strpos($low, 'least concern') !== false) return 'least-concern';
    if (strpos($low, 'extinct') !== false) return 'extinct-wild';

    // If still unknown, attempt to create a safe key from the value
    $normalized = preg_replace('/[^a-z0-9]+/i', '-', $low);
    $normalized = trim($normalized, '-');
    if (!empty($normalized)) return $normalized;

    return 'unknown';
}

// Fungsi untuk generate gambar placeholder dengan nama species
function generatePlaceholderImage($name, $category) {
    $bgColor = ($category === 'fauna') ? '4CAF50' : '2196F3';
    $initials = substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 2);
    $encoded = urlencode(substr($name, 0, 20));
    return "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='150'%3E%3Crect fill='%23" . $bgColor . "' width='300' height='150'/%3E%3Ctext x='50%25' y='40%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='32' font-weight='bold' fill='white'%3E" . strtoupper($initials) . "%3C/text%3E%3Ctext x='50%25' y='75%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='11' fill='white' opacity='0.8'%3E" . $encoded . "%3C/text%3E%3C/svg%3E";
}

// Query untuk mengambil data flora
$sql_flora = "SELECT
    f.id_flora as id,
    f.nama as name,
    f.nama_scientific as scientificName,
    'flora' as category,
    f.deskripsi as description,
    f.status_konservasi as conservationStatus,
    f.habitat,
    d.nama_daerah as province,
    f.gambar as wikiLink,
    f.latitude,
    f.longitude
FROM flora f
JOIN daerah d ON f.id_daerah = d.id_daerah
WHERE f.latitude IS NOT NULL AND f.longitude IS NOT NULL";

// Query untuk mengambil data fauna
$sql_fauna = "SELECT
    f.id_fauna as id,
    f.nama as name,
    f.nama_scientific as scientificName,
    'fauna' as category,
    f.deskripsi as description,
    f.status_konservasi as conservationStatus,
    f.habitat,
    d.nama_daerah as province,
    f.gambar as wikiLink,
    f.latitude,
    f.longitude
FROM fauna f
JOIN daerah d ON f.id_daerah = d.id_daerah
WHERE f.latitude IS NOT NULL AND f.longitude IS NOT NULL";

// Eksekusi query flora
$result_flora = $conn->query($sql_flora);
$data = [];

if ($result_flora) {
    while ($row = $result_flora->fetch_assoc()) {
        // expose original DB value for debugging and accurate mapping
        $row['originalStatus'] = $row['conservationStatus'];
        $row['conservationStatus'] = normalizeStatus($row['conservationStatus']);
        // Generate placeholder image
        $row['image'] = generatePlaceholderImage($row['name'], 'flora');
        $data[] = $row;
    }
}

// Eksekusi query fauna
$result_fauna = $conn->query($sql_fauna);

if ($result_fauna) {
    while ($row = $result_fauna->fetch_assoc()) {
        // expose original DB value for debugging and accurate mapping
        $row['originalStatus'] = $row['conservationStatus'];
        $row['conservationStatus'] = normalizeStatus($row['conservationStatus']);
        // Generate placeholder image
        $row['image'] = generatePlaceholderImage($row['name'], 'fauna');
        $data[] = $row;
    }
}

// Kembalikan hasil dalam format JSON
echo json_encode($data);

$conn->close();
?>