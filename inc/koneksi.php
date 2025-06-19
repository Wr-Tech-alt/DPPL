<?php

// Pastikan untuk mengganti dengan kredensial database yang sesuai di lingkungan produksi.
// Sebaiknya, kredensial ini disimpan di luar root dokumen web atau di variabel lingkungan
// untuk keamanan yang lebih baik.
define('DB_HOST', 'localhost');
define('DB_USER', 'u951570841_hadi');
define('DB_PASS', 'Hadi333#');
define('DB_NAME', 'u951570841_hadi');

// Buat koneksi ke database menggunakan MySQLi Object-Oriented
$koneksi = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($koneksi->connect_error) {
    // Untuk pengembangan, tampilkan detail error.
    // Di lingkungan produksi, log error ke file dan tampilkan pesan generik.
    die("Koneksi gagal: " . $koneksi->connect_error);
    // Alternatif untuk produksi:
    // die("Terjadi masalah saat menghubungkan ke database. Silakan coba lagi nanti.");
}

// Kamu bisa menambahkan ini untuk melihat status koneksi (hanya untuk debugging)
// echo "Koneksi ke database berhasil!";

?>

<!-- end -->