<?php
// Aktifkan error reporting untuk melihat pesan kesalahan
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = ""; // Kosongkan jika tidak ada password di MySQL Anda
$database = "sicepu"; // Pastikan nama ini sama persis dengan nama database di phpMyAdmin

// Membuat koneksi
$koneksi = mysqli_connect($servername, $username, $password, $database);

// Mengecek koneksi
if (!$koneksi) {
    // Jika koneksi gagal, tampilkan pesan error yang spesifik dari MySQL
    die("Koneksi Database GAGAL! Pesan Error: " . mysqli_connect_error() . " (Nomor Error: " . mysqli_connect_errno() . ")");
}

// Jika koneksi berhasil, Anda bisa menambahkan pesan debug ini (opsional)
// echo "DEBUG: Koneksi database berhasil!<br>";

?>