<?php
// aduan_tolak.php

// Pastikan skrip ini hanya dapat diakses melalui CLI atau cron job,
// bukan langsung dari web.
if (php_sapi_name() !== 'cli') {
    die("Akses tidak diizinkan.");
}

// Sertakan file koneksi database Anda
// Sesuaikan path agar sesuai dengan lokasi file ini di server Anda.
// Contoh: jika file ini berada di luar root web, dan 'inc' berada di dalam root web,
// maka Anda mungkin perlu path yang lebih spesifik atau menyesuaikan struktur folder.
require_once __DIR__ . '/inc/koneksi.php'; // Sesuaikan path ini jika Anda menempatkan 'aduan_tolak.php' di folder lain.

// Periksa koneksi database
if ($conn->connect_error) {
    error_log("Koneksi database gagal: " . $conn->connect_error);
    exit();
}

// Definisikan batas waktu penolakan otomatis (dalam detik)
// 1 minggu = 7 hari * 24 jam * 60 menit * 60 detik = 604800 detik
$waktu_batas_detik = 604800; 

// Hitung waktu batas (sekarang dikurangi waktu batas)
$waktu_sekarang = time();
$waktu_batas_timestamp = $waktu_sekarang - $waktu_batas_detik;
$waktu_batas_mysql = date('Y-m-d H:i:s', $waktu_batas_timestamp);

// Query untuk mencari aduan yang berstatus 'Pending'
// dan sudah lebih dari satu minggu sejak 'waktu_aduan'
$sql = "UPDATE pengaduan 
        SET status = 'Ditolak' 
        WHERE status = 'Pending' 
        AND waktu_aduan <= ?";

$stmt = $conn->prepare($sql);

if ($stmt === FALSE) {
    error_log("Gagal menyiapkan statement: " . $conn->error);
    $conn->close();
    exit();
}

$stmt->bind_param("s", $waktu_batas_mysql);
$stmt->execute();

$jumlah_aduan_ditolak = $stmt->affected_rows;

// Catat hasil ke log (opsional, untuk debugging)
error_log("Skrip aduan_tolak.php: " . $jumlah_aduan_ditolak . " aduan diubah menjadi 'Ditolak' pada " . date('Y-m-d H:i:s'));

$stmt->close();
$conn->close();

echo "Pembaruan selesai. " . $jumlah_aduan_ditolak . " aduan diubah menjadi 'Ditolak'.\n";

?>