<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// IMPORTANT: Pastikan ini adalah hal pertama untuk melindungi halaman.
// Asumsi file ini berlokasi di admin/pengaduan/pengaduan_hapus.php
// Path ke login.php dari sini adalah ../../login.php
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php");
    exit();
}

// Path ke koneksi.php dari admin/pengaduan/
require_once '../../inc/koneksi.php';

// Periksa objek koneksi database ($conn)
if (!isset($conn) || $conn->connect_error) {
    die("Fatal Error: Objek koneksi database (\$conn) tidak tersedia atau koneksi gagal. Harap periksa ../../inc/koneksi.php. Error: " . $conn->connect_error);
}

// Inisialisasi pesan sesi untuk umpan balik kepada pengguna di halaman yang dialihkan.
if (!isset($_SESSION['form_message'])) {
    $_SESSION['form_message'] = '';
}
if (!isset($_SESSION['form_message_type'])) {
    $_SESSION['form_message_type'] = '';
}

// Periksa apakah parameter 'id' (yang merupakan idpengaduan) disediakan di URL (permintaan GET).
if (isset($_GET['id'])) {
    // Sanitasi dan validasi ID input sebagai integer.
    $id_pengaduan_to_delete = intval($_GET['id']);

    // Siapkan variabel statement untuk memastikan selalu ditutup.
    $stmt_delete_pengaduan = null;

    try {
        // Siapkan pernyataan SQL DELETE untuk tabel 'pengaduan'.
        $stmt_delete_pengaduan = $conn->prepare("DELETE FROM pengaduan WHERE idpengaduan = ?");

        if ($stmt_delete_pengaduan === FALSE) {
            throw new Exception("Error preparing statement to delete pengaduan: " . $conn->error);
        }

        // Ikat parameter integer ke pernyataan yang disiapkan.
        $stmt_delete_pengaduan->bind_param("i", $id_pengaduan_to_delete);

        // Jalankan pernyataan penghapusan.
        if ($stmt_delete_pengaduan->execute()) {
            // Periksa apakah ada baris yang terpengaruh oleh penghapusan.
            if ($stmt_delete_pengaduan->affected_rows > 0) {
                $_SESSION['form_message'] = "Pengaduan dengan ID " . htmlspecialchars($id_pengaduan_to_delete) . " berhasil dihapus!";
                $_SESSION['form_message_type'] = 'success';
            } else {
                // Jika tidak ada baris yang terpengaruh, berarti ID tidak ditemukan.
                $_SESSION['form_message'] = "Pengaduan dengan ID " . htmlspecialchars($id_pengaduan_to_delete) . " tidak ditemukan atau sudah dihapus.";
                $_SESSION['form_message_type'] = 'error';
            }
        } else {
            // Tangani kesalahan eksekusi.
            throw new Exception("Gagal menghapus pengaduan: " . $stmt_delete_pengaduan->error);
        }

    } catch (Exception $e) {
        // Tangkap pengecualian apa pun yang terjadi selama proses dan atur pesan kesalahan.
        $_SESSION['form_message'] = "Terjadi kesalahan saat menghapus pengaduan: " . $e->getMessage();
        $_SESSION['form_message_type'] = 'error';
    } finally {
        // Pastikan pernyataan yang disiapkan ditutup, terlepas dari keberhasilan atau kegagalan.
        if ($stmt_delete_pengaduan) {
            $stmt_delete_pengaduan->close();
        }
    }

} else {
    // Jika tidak ada parameter 'id' yang diberikan, atur pesan kesalahan.
    $_SESSION['form_message'] = "ID Pengaduan tidak diberikan untuk penghapusan.";
    $_SESSION['form_message_type'] = 'error';
}

// Redirect kembali ke halaman pengaduan_lihat.php setelah pemrosesan.
// Pesan sesi akan ditampilkan di sana menggunakan SweetAlert2.
header("Location: adu_tampil.php");
exit();

// Tutup koneksi database utama.
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>
