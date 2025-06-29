<?php
session_start(); // Mulai sesi jika belum dimulai

// Hapus semua variabel sesi
$_SESSION = array();

// Jika ingin menghapus cookie sesi, hapus juga cookie-nya.
// Catatan: Ini akan menghancurkan sesi, bukan hanya data sesi!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Terakhir, hancurkan sesi
session_destroy();

// Arahkan pengguna kembali ke halaman login
// Pastikan login.php berada di direktori yang sama dengan logout.php
header("Location: login.php"); 
exit(); // Pastikan tidak ada kode lain setelah exit()
?>