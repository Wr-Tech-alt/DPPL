<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// IMPORTANT: Ensure this is the very first thing to protect the page.
// Current file is admin/pengguna/pengguna_hapus.php
// Path to login.php from here is ../../login.php
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php"); 
    exit();
}

// Path to koneksi.php from admin/pengguna/
require_once '../../inc/koneksi.php'; 

// Check database connection object ($conn)
if (!isset($conn) || $conn->connect_error) {
    die("Fatal Error: Database connection object (\$conn) is not available or connection failed. Please check ../../inc/koneksi.php.");
}

$message = '';
$message_type = '';

if (isset($_GET['id'])) {
    $iduser_to_delete = intval($_GET['id']);

    // Start transaction for atomicity
    $conn->begin_transaction();

    try {
        // 1. Get the 'nama' and 'Role' from the pengguna table
        $stmt_get_user_info = $conn->prepare("SELECT nama, Role FROM pengguna WHERE iduser = ?");
        if ($stmt_get_user_info === FALSE) {
            throw new Exception("Prepare statement for getting user info failed: " . $conn->error);
        }
        $stmt_get_user_info->bind_param("i", $iduser_to_delete);
        $stmt_get_user_info->execute();
        $result_get_user_info = $stmt_get_user_info->get_result();
        
        if ($result_get_user_info->num_rows === 0) {
            throw new Exception("Pengguna tidak ditemukan.");
        }
        $user_info = $result_get_user_info->fetch_assoc();
        $nama_pengguna_to_delete = $user_info['nama'];
        $role_pengguna_to_delete = $user_info['Role'];
        $stmt_get_user_info->close();

        // 2. Delete from tb_telegram table ONLY if the role is 'Pengadu'
        if ($role_pengguna_to_delete === 'Pengadu') {
            $stmt_delete_telegram = $conn->prepare("DELETE FROM tb_telegram WHERE user = ?");
            if ($stmt_delete_telegram === FALSE) {
                throw new Exception("Prepare statement for deleting telegram failed: " . $conn->error);
            }
            $stmt_delete_telegram->bind_param("s", $nama_pengguna_to_delete);
            $stmt_delete_telegram->execute();
            $stmt_delete_telegram->close();
        }

        // 3. Delete from pengaduan table (due to ON DELETE NO ACTION constraint)
        $stmt_delete_pengaduan = $conn->prepare("DELETE FROM pengaduan WHERE iduser = ?");
        if ($stmt_delete_pengaduan === FALSE) {
            throw new Exception("Prepare statement for deleting pengaduan failed: " . $conn->error);
        }
        $stmt_delete_pengaduan->bind_param("i", $iduser_to_delete);
        $stmt_delete_pengaduan->execute();
        $stmt_delete_pengaduan->close();

        // 4. Delete from pengguna table
        $stmt_delete_pengguna = $conn->prepare("DELETE FROM pengguna WHERE iduser = ?");
        if ($stmt_delete_pengguna === FALSE) {
            throw new Exception("Prepare statement for deleting pengguna failed: " . $conn->error);
        }
        $stmt_delete_pengguna->bind_param("i", $iduser_to_delete);

        if (!$stmt_delete_pengguna->execute()) {
            throw new Exception("Gagal menghapus Pengguna: " . $stmt_delete_pengguna->error);
        }
        $stmt_delete_pengguna->close();

        // If all deletions are successful
        $conn->commit();
        $_SESSION['form_message'] = "Pengguna '" . htmlspecialchars($nama_pengguna_to_delete) . "' berhasil dihapus!";
        $_SESSION['form_message_type'] = 'success';

    } catch (Exception $e) {
        $conn->rollback(); // Rollback transaction on error
        $_SESSION['form_message'] = "Gagal menghapus pengguna: " . $e->getMessage();
        $_SESSION['form_message_type'] = 'error';
    }

} else {
    $_SESSION['form_message'] = "ID Pengguna tidak diberikan untuk penghapusan.";
    $_SESSION['form_message_type'] = 'error';
}

// Redirect back to pengguna_lihat.php
header("Location: pengguna_lihat.php");
exit();

// Close connection at the end of script
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close(); 
}
?>