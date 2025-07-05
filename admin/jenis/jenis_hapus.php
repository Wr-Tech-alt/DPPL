<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// IMPORTANT: Ensure this is the very first thing to protect the page.
// Assuming this file is located at admin/jenis/jenis_hapus.php
// Path to login.php from here is ../../login.php
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php");
    exit();
}

// Path to koneksi.php from admin/jenis/
require_once '../../inc/koneksi.php';

// Check database connection object ($conn)
if (!isset($conn) || $conn->connect_error) {
    die("Fatal Error: Database connection object (\$conn) is not available or connection failed. Please check ../../inc/koneksi.php. Error: " . $conn->connect_error);
}

// Initialize session messages for feedback to the user on the redirected page.
if (!isset($_SESSION['form_message'])) {
    $_SESSION['form_message'] = '';
}
if (!isset($_SESSION['form_message_type'])) {
    $_SESSION['form_message_type'] = '';
}

// Check if an 'id' parameter (which is idjenis) is provided in the URL (GET request).
if (isset($_GET['id'])) {
    // Sanitize and validate the input ID as an integer.
    $id_jenis_to_delete = intval($_GET['id']);

    // Prepare statement variable to ensure it is always closed.
    $stmt_delete_jenis = null;

    try {
        // Prepare the SQL DELETE statement for the 'jenis_pengaduan' table.
        $stmt_delete_jenis = $conn->prepare("DELETE FROM jenis_pengaduan WHERE idjenis = ?");

        if ($stmt_delete_jenis === FALSE) {
            throw new Exception("Error preparing statement to delete jenis pengaduan: " . $conn->error);
        }

        // Bind the integer parameter to the prepared statement.
        $stmt_delete_jenis->bind_param("i", $id_jenis_to_delete);

        // Execute the delete statement.
        if ($stmt_delete_jenis->execute()) {
            // Check if any rows were affected by the deletion.
            if ($stmt_delete_jenis->affected_rows > 0) {
                $_SESSION['form_message'] = "Jenis pengaduan dengan ID " . htmlspecialchars($id_jenis_to_delete) . " berhasil dihapus!";
                $_SESSION['form_message_type'] = 'success';
            } else {
                // If no rows were affected, it means the ID was not found.
                $_SESSION['form_message'] = "Jenis pengaduan dengan ID " . htmlspecialchars($id_jenis_to_delete) . " tidak ditemukan atau sudah dihapus.";
                $_SESSION['form_message_type'] = 'error';
            }
        } else {
            // Handle execution errors.
            throw new Exception("Gagal menghapus jenis pengaduan: " . $stmt_delete_jenis->error);
        }

    } catch (Exception $e) {
        // Catch any exceptions thrown during the process and set an error message.
        $_SESSION['form_message'] = "Terjadi kesalahan saat menghapus jenis pengaduan: " . $e->getMessage();
        $_SESSION['form_message_type'] = 'error';
    } finally {
        // Ensure the prepared statement is closed, regardless of success or failure.
        // Removed is_closed() check for broader PHP version compatibility.
        if ($stmt_delete_jenis) {
            $stmt_delete_jenis->close();
        }
    }

} else {
    // If no 'id' parameter is provided, set an error message.
    $_SESSION['form_message'] = "ID Jenis Pengaduan tidak diberikan untuk penghapusan.";
    $_SESSION['form_message_type'] = 'error';
}

// Redirect back to the jenis_lihat.php page after processing.
// The session messages will be displayed there using SweetAlert2.
header("Location: jenis_lihat.php");
exit();

// Close the main database connection.
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>