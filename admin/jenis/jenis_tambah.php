<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- DEBUGGING: Tampilkan isi sesi saat halaman dimuat (sementara) ---
// echo '<pre>';
// echo 'DEBUG: Isi $_SESSION saat awal load:';
// var_dump($_SESSION);
// echo '</pre>';
// --- AKHIR DEBUGGING ---

// PENTING: Pastikan ini adalah hal pertama untuk melindungi halaman.
// Asumsi file ini berada di admin/jenis/jenis_tambah.php
// Path ke login.php dari sini adalah ../../login.php

// Inisialisasi variabel pesan dan tipe pesan.
// Ini akan menampung pesan dari sesi jika ada.
$message_from_session = '';
$message_type_from_session = '';

// Cek apakah ada pesan yang disimpan di sesi
if (isset($_SESSION['message'])) {
    $message_from_session = $_SESSION['message'];
    $message_type_from_session = $_SESSION['message_type'];

    // Hapus pesan dari sesi agar tidak muncul lagi setelah refresh
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);

    // --- DEBUGGING: Tampilkan isi sesi setelah unset (sementara) ---
    // echo '<pre>';
    // echo 'DEBUG: Isi $_SESSION setelah unset:';
    // var_dump($_SESSION);
    // echo '</pre>';
    // --- AKHIR DEBUGGING ---
}

// Periksa status login dan peran pengguna
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php");
    exit();
}

// Path ke koneksi.php dari admin/jenis/
require_once '../../inc/koneksi.php';

// Periksa objek koneksi database ($conn)
if (!isset($conn) || $conn->connect_error) {
    die("Fatal Error: Objek koneksi database (\$conn) tidak tersedia atau koneksi gagal. Harap periksa ../../inc/koneksi.php.");
}

$admin_name = $_SESSION['nama']; // Dapatkan nama admin dari sesi

// Tangani pengiriman formulir saat tombol 'tambah_jenis_submit' ditekan
if (isset($_POST['tambah_jenis_submit'])) {
    // Sanitasi dan dapatkan data formulir untuk tabel jenis_pengaduan
    $jenis_baru = $conn->real_escape_string($_POST['jenis']);

    // Validasi dasar
    if (empty($jenis_baru)) {
        $_SESSION['message'] = "Nama Jenis Pengaduan harus diisi.";
        $_SESSION['message_type'] = 'error';
        header("Location: jenis_tambah.php"); // Redirect kembali ke halaman ini untuk menampilkan error
        exit();
    } else {
        // Siapkan statement untuk memasukkan ke tabel jenis_pengaduan
        $stmt_jenis = $conn->prepare("INSERT INTO jenis_pengaduan (jenis) VALUES (?)");

        // Periksa jika prepare statement gagal
        if ($stmt_jenis === FALSE) {
            $_SESSION['message'] = "Prepare statement gagal: " . $conn->error;
            $_SESSION['message_type'] = 'error';
            header("Location: jenis_tambah.php");
            exit();
        }

        $stmt_jenis->bind_param("s", $jenis_baru); // 's' untuk tipe string
        
        if ($stmt_jenis->execute()) {
            $_SESSION['message'] = "Jenis pengaduan '" . htmlspecialchars($jenis_baru) . "' berhasil ditambahkan!";
            $_SESSION['message_type'] = 'success';
            // TIDAK ADA REDIRECT DI SINI! SweetAlert akan menangani pengalihan setelah dikonfirmasi.
            // Biarkan skrip PHP berlanjut untuk merender halaman dengan pesan sesi yang baru diatur.
        } else {
            $_SESSION['message'] = "Gagal menambahkan Jenis Pengaduan: " . $stmt_jenis->error;
            $_SESSION['message_type'] = 'error';
            header("Location: jenis_tambah.php"); // Redirect kembali ke halaman ini jika ada error
            exit();
        }
        $stmt_jenis->close();
    }
}
// Tutup koneksi di sini jika Anda hanya membutuhkannya untuk login,
// jika tidak, biarkan terbuka jika 'koneksi.php' mengelola koneksi persisten.
// Untuk skrip sederhana, menutup di sini tidak masalah.
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Jenis Aduan Baru - SiCepu</title>
    <link rel="stylesheet" href="../../assets/css/users.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* Custom styles for this form (kept mostly the same for consistency) */
        .form-section {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin: 20px;
            max-width: 600px; /* Adjust width as needed */
            margin-left: auto;
            margin-right: auto;
        }
        .form-section h3 {
            margin-bottom: 25px;
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"] { /* Kept for general styling, though only 'text' is used */
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1em;
            box-sizing: border-box; /* Include padding in width */
        }
        .form-group input[type="text"]:focus,
        .form-group input[type="email"]:focus,
        .form-group input[type="password"]:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }
        .btn-submit {
            background-color: #007bff;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background-color 0.2s ease;
            width: 100%;
        }
        .btn-submit:hover {
            background-color: #0056b3;
        }
        /* Hapus style .message karena akan diganti SweetAlert2 */
        /* .message { ... } */
        .form-divider { /* Not needed for single field, but can be kept if desired for visual separation */
            text-align: center;
            margin: 30px 0;
            font-size: 1.1em;
            color: #888;
            position: relative;
        }
        .form-divider::before,
        .form-divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 40%;
            height: 1px;
            background-color: #eee;
        }
        .form-divider::before {
            left: 0;
        }
        .form-divider::after {
            right: 0;
        }
        .form-divider span {
            background-color: #fff;
            padding: 0 10px;
            z-index: 1;
            position: relative;
        }
        /* Style for centering H2 title */
        .content-header {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }
        .content-header h2 {
            margin: 0;
            padding: 0;
        }

        /* Styles for top-info-bar (copied from dashboard_admin.php) */
        .navbar .top-info-bar {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-grow: 1;
            padding-left: 20px;
            color: #333;
        }
        .top-info-bar .status-info,
        .top-info-bar .time-location-info {
            display: flex;
            align-items: center;
            font-size: 0.95em;
            color: #333;
        }
        .top-info-bar .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .top-info-bar .status-connected {
            background-color: #28a745;
        }
        .top-info-bar .status-disconnected {
            background-color: #dc3545;
        }
        /* Make sure it's responsive */
        @media (max-width: 992px) {
            .navbar .top-info-bar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../../assets/img/logos.png" alt="SiCepu Logo" class="logo">
                <span class="logo-text">SiCepu</span>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="../../dashboard/dashboard_admin.php" class="nav-link"><i class="fas fa-th-large"></i> Dashboard</a></li>
                    <li><a href="../aduan/adu_tampil.php" class="nav-link"><i class="fas fa-boxes"></i> Aduan Fasilitas</a></li>
                    <li><a href="#" class="nav-link active"><i class="fas fa-file-alt"></i> Jenis Aduan </a></li>
                    <li><a href="../pengadu/pengadu_lihat.php" class="nav-link"><i class="fas fa-users"></i> Pengadu </a></li>
                </ul>
                <div class="nav-section-title">SETTINGS</div>
                <ul>
                    <li><a href="#" class="nav-link"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="#" class="nav-link"><i class="fas fa-question-circle"></i> Help</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="navbar">
                <div class="top-info-bar">
                    <div class="status-info">
                        <span class="status-dot <?php echo (isset($conn) && $conn ? 'status-connected' : 'status-disconnected'); ?>"></span>
                        <span>Database: <?php echo (isset($conn) && $conn ? 'Connected' : 'Disconnected'); ?></span>
                    </div>
                    <div class="time-location-info">
                        <span id="currentDateTime"></span> | <span>Bekasi Regency, West Java, Indonesia</span>
                    </div>
                </div>

                <div class="nav-icons">
                    <a href="#"><i class="fas fa-bell"></i></a>
                    <a href="#"><i class="fas fa-comment"></i></a>
                    <div class="user-profile">
                        <img src="../../assets/img/admin_pfp.jpg" alt="User Avatar" class="avatar">
                        <span><?php echo htmlspecialchars($admin_name); ?></span>
                        <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i></a>
                    </div>
                </div>
            </header>

            <section class="content-header">
                <h2>Tambah Jenis Pengaduan Baru</h2>
            </section>

            <section class="form-section">
                <form action="" method="POST">
                    <h3>Detail Jenis Pengaduan</h3>
                    <div class="form-group">
                        <label for="jenis">Nama Jenis Pengaduan:</label>
                        <input type="text" id="jenis" name="jenis" placeholder="Masukkan nama jenis pengaduan (e.g., Fasilitas Umum)" required>
                    </div>

                    <button type="submit" name="tambah_jenis_submit" class="btn-submit">Tambah Jenis Pengaduan</button>
                </form>
            </section>
        </main>
    </div>
    <script src="../../assets/js/jquery-1.10.2.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script>
        function updateDateTime() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            document.getElementById('currentDateTime').innerText = now.toLocaleDateString('id-ID', options);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Pembaruan awal
            updateDateTime();
            // Perbarui setiap detik
            setInterval(updateDateTime, 1000);

            // Tangani pesan dari sesi menggunakan SweetAlert2
            const message = "<?php echo $message_from_session; ?>";
            const messageType = "<?php echo $message_type_from_session; ?>";

            // --- DEBUGGING: Tampilkan nilai variabel JavaScript (sementara) ---
            // console.log("Pesan dari sesi (JS):", message);
            // console.log("Tipe pesan dari sesi (JS):", messageType);
            // --- AKHIR DEBUGGING ---

            if (message) {
                Swal.fire({
                    title: messageType === 'success' ? 'Berhasil!' : 'Gagal!',
                    text: message,
                    icon: messageType, // 'success' atau 'error'
                    confirmButtonText: 'Oke'
                }).then((result) => {
                    // Jika pesan sukses, redirect ke jenis_lihat.php setelah pop-up ditutup
                    // Hanya redirect jika itu pesan sukses DAN pengguna mengonfirmasi
                    if (messageType === 'success' && result.isConfirmed) {
                        window.location.href = 'jenis_lihat.php';
                    }
                });
            }
        });
    </script>
</body>
</html>
