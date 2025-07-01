<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// IMPORTANT: Ensure this is the very first thing to protect the page.
// Current file is admin/pengadu/pengadu_hapus.php
// Path to login.php from here is ../../login.php
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php"); 
    exit();
}

// Path to koneksi.php from admin/pengadu/
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
        // 1. Get the 'nama' (username) from the pengguna table for deletion in tb_telegram
        $stmt_get_nama = $conn->prepare("SELECT nama FROM pengguna WHERE iduser = ?");
        if ($stmt_get_nama === FALSE) {
            throw new Exception("Prepare statement for getting nama failed: " . $conn->error);
        }
        $stmt_get_nama->bind_param("i", $iduser_to_delete);
        $stmt_get_nama->execute();
        $result_get_nama = $stmt_get_nama->get_result();
        
        if ($result_get_nama->num_rows === 0) {
            throw new Exception("Pengguna tidak ditemukan.");
        }
        $user_data = $result_get_nama->fetch_assoc();
        $nama_pengguna_to_delete = $user_data['nama'];
        $stmt_get_nama->close();

        // 2. Delete from tb_telegram table (linked by 'user' column which is 'nama' from pengguna)
        $stmt_delete_telegram = $conn->prepare("DELETE FROM tb_telegram WHERE user = ?");
        if ($stmt_delete_telegram === FALSE) {
            throw new Exception("Prepare statement for deleting telegram failed: " . $conn->error);
        }
        $stmt_delete_telegram->bind_param("s", $nama_pengguna_to_delete);
        $stmt_delete_telegram->execute();
        $stmt_delete_telegram->close();

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
        $_SESSION['form_message'] = "Pengadu dan data terkait berhasil dihapus!";
        $_SESSION['form_message_type'] = 'success';

    } catch (Exception $e) {
        $conn->rollback(); // Rollback transaction on error
        $_SESSION['form_message'] = "Gagal menghapus pengadu: " . $e->getMessage();
        $_SESSION['form_message_type'] = 'error';
    }

} else {
    $_SESSION['form_message'] = "ID Pengadu tidak diberikan untuk penghapusan.";
    $_SESSION['form_message_type'] = 'error';
}

// Redirect back to pengadu_lihat.php
header("Location: pengadu_lihat.php");
exit();

// Close connection at the end of script
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close(); 
}
?>