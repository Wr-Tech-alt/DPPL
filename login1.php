<?php
// Pastikan ini baris pertama. TIDAK ADA SPASI, NEWLINE, ATAU KARAKTER LAIN DI ATAS INI.
ob_start(); // Pastikan output buffering aktif di awal

include "inc/koneksi.php"; // Pastikan inc/koneksi.php tidak memiliki output apapun sebelum <?php

// Debugging: Catat waktu mulai proses
error_log("Login process started at: " . date("Y-m-d H:i:s"));

if (isset($_POST['btnLogin'])) {
    $username_input = $_POST['username'];
    $password_input = $_POST['password'];

    // Debugging: Log input
    error_log("Attempting login for username: " . $username_input);

    $sql_login = "SELECT id_pengguna, username, password, nama_pengguna, level FROM tb_pengguna WHERE username = ?";
    
    if ($koneksi === false) {
        error_log("Database connection failed for login.php");
        die("Koneksi database gagal.");
    }

    $stmt = mysqli_prepare($koneksi, $sql_login);
    
    if ($stmt === false) {
        error_log("Prepared statement failed: " . mysqli_error($koneksi));
        die("Prepare failed.");
    }

    mysqli_stmt_bind_param($stmt, "s", $username_input);
    mysqli_stmt_execute($stmt);
    $query_login = mysqli_stmt_get_result($stmt);
    $data_login = mysqli_fetch_array($query_login, MYSQLI_ASSOC);
    $jumlah_login = mysqli_num_rows($query_login);

    if ($jumlah_login == 1) {
        if (password_verify($password_input, $data_login['password'])) {
            // Login berhasil
            $_SESSION["ses_id"] = $data_login["id_pengguna"];
            $_SESSION["ses_username"] = $data_login["username"];
            $_SESSION["ses_nama"] = $data_login["nama_pengguna"]; 
            $_SESSION["ses_level"] = $data_login["level"];
            $_SESSION["loggedin"] = true; 

            $redirect_url = '';
            if ($_SESSION["ses_level"] == 'Administrator') {
                $redirect_url = 'default/admin.php';
            } elseif ($_SESSION["ses_level"] == 'Petugas') {
                $redirect_url = 'default/tugas.php';
            } elseif ($_SESSION["ses_level"] == 'Pengadu') {
                $redirect_url = 'default/pengadu.php';
            } else {
                session_unset();
                session_destroy();
                error_log("Login failed: Unknown level for user " . $username_input);
                header("Location: login1.php?error=level_tidak_dikenal");
                ob_end_clean(); // Hapus buffer dan jangan kirim apapun
                exit();
            }

            error_log("Login successful. Redirecting to: " . $redirect_url);

            // Cek apakah headers sudah terkirim (untuk debugging)
            if (headers_sent($file, $line)) {
                error_log("WARNING: Headers already sent in file: " . $file . " on line: " . $line);
                echo "Headers sudah terkirim! Silakan cek log error PHP Anda."; // Ini hanya akan terlihat jika headers_sent() true
                ob_end_clean();
                exit();
            }

            header("Location: " . $redirect_url);
            ob_end_flush(); // Pastikan semua buffer dikirim sebelum exit
            exit(); // SANGAT PENTING
        } else {
            error_log("Login failed: Password mismatch for user " . $username_input);
            header("Location: login1.php?error=password_salah");
            ob_end_clean();
            exit();
        }
    } else {
        error_log("Login failed: User not found: " . $username_input);
        header("Location: login1.php?error=user_tidak_ditemukan");
        ob_end_clean();
        exit();
    }
    mysqli_stmt_close($stmt);
} else {
    // Jika diakses langsung tanpa POST, arahkan ke halaman login yang sebenarnya
    header("Location: login1.php"); // Atau ke halaman login yang seharusnya
    ob_end_clean();
    exit();
}
?>