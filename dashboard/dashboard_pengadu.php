<?php
// user_dashboard.php

// Mulai session (PENTING: Pastikan ini selalu di awal file yang memerlukan session)
session_start();

// Sertakan file koneksi database
require_once __DIR__ . '/../inc/koneksi.php'; // Sesuaikan path jika berbeda

// Inisialisasi variabel user dengan nilai default
$user_name = "Pengadu Default"; // Default jika data tidak ditemukan atau belum login
$user_email = "default@example.com"; // Default jika data tidak ditemukan atau belum login

// Asumsi: iduser yang sedang login diambil dari session
// Anda harus memastikan 'iduser' ini diset saat proses login berhasil di login.php
$logged_in_iduser = $_SESSION['iduser'] ?? null;

// Cek apakah user sudah login
if ($logged_in_iduser) {
    // Escape input untuk mencegah SQL injection
    $safe_iduser = $conn->real_escape_string($logged_in_iduser);

    // Menggunakan Prepared Statement untuk keamanan (DIREKOMENDASIKAN)
    $stmt = $conn->prepare("SELECT nama, email FROM pengguna WHERE iduser = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $logged_in_iduser); // "i" untuk integer
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_name = htmlspecialchars($user['nama']);
            $user_email = htmlspecialchars($user['email']);
        } else {
            // Pengguna tidak ditemukan di database meskipun iduser ada di session
            // Hancurkan session dan redirect ke halaman login
            session_unset();
            session_destroy();
            header('Location: login.php'); // Ganti dengan halaman login Anda
            exit();
        }
        $stmt->close(); // Tutup statement
    } else {
        // Handle error jika prepared statement gagal
        error_log("Failed to prepare statement: " . $conn->error);
        // Bisa tambahkan pesan error atau redirect ke halaman error
        session_unset();
        session_destroy();
        header('Location: login.php?error=db_error'); // Atau tampilkan pesan di halaman
        exit();
    }
} else {
    // Jika tidak ada iduser di session, redirect ke halaman login
    header('Location: login.php'); // Ganti dengan halaman login Anda
    exit();
}

// Tidak perlu menutup $conn di sini jika Anda akan menggunakan koneksi untuk query lain di bagian bawah halaman.
// Jika tidak ada query lain, bisa ditutup di akhir script.
// $conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiCepu - Dashboard Pengadu</title>
    <link rel="stylesheet" href="../assets/css/dash_pengadu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="https://via.placeholder.com/30x30/000000/FFFFFF?text=XT" alt="SiCepu Logo" class="logo">
                <span class="logo-text">SiCepu</span>
            </div>
            <div class="user-profile">
                <div class="user-avatar"><?php echo strtoupper(substr($user_name, 0, 2)); ?></div>
                <div class="user-info">
                    <span class="user-name"><?php echo $user_name; ?></span>
                    <span class="user-email"><?php echo $user_email; ?></span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="active"><a href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="#"><i class="fas fa-file-alt"></i> Pengaduan Saya</a></li>
                    <li><a href="#"><i class="fas fa-clipboard-list"></i> Riwayat Pengaduan</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> Pengaturan Akun</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <a href="#"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </aside>
        <main class="main-content">
            <header class="navbar">
                <div class="navbar-left">
                    <button class="nav-button"><i class="fas fa-chevron-left"></i></button>
                    <button class="nav-button"><i class="fas fa-chevron-right"></i></button>
                </div>
                <div class="navbar-center">
                    <div class="browser-bar">
                        <i class="fas fa-lock"></i>
                        <span>sicepu.co</span>
                        <i class="fas fa-sync-alt"></i>
                    </div>
                </div>
                <div class="navbar-right">
                    <button class="nav-button"><i class="fas fa-ellipsis-h"></i></button>
                    <button class="nav-button"><i class="fas fa-star"></i></button>
                    <button class="nav-button"><i class="fas fa-user-circle"></i></button>
                </div>
            </header>

            <div class="content-header">
                <h1>Selamat Datang, <?php echo $user_name; ?>!</h1>
                <p class="greeting-text">Ada keluhan apa hari ini?</p>
                <button class="btn btn-primary"><i class="fas fa-plus"></i> Buat Pengaduan Baru</button>
            </div>

            <div class="dashboard-content">
                <div class="summary-cards">
                    <div class="card">
                        <div class="card-icon" style="background-color: var(--status-pending-bg);"><i class="fas fa-hourglass-half" style="color: var(--status-pending-text);"></i></div>
                        <div class="card-info">
                            <span class="card-title">Pengaduan Pending</span>
                            <span class="card-value">3</span>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon" style="background-color: var(--status-active-bg);"><i class="fas fa-sync-alt" style="color: var(--status-active-text);"></i></div>
                        <div class="card-info">
                            <span class="card-title">Dalam Proses</span>
                            <span class="card-value">2</span>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon" style="background-color: var(--status-closed-bg);"><i class="fas fa-check-circle" style="color: var(--status-closed-text);"></i></div>
                        <div class="card-info">
                            <span class="card-title">Selesai Ditindaklanjuti</span>
                            <span class="card-value">15</span>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon" style="background-color: var(--status-declined-bg);"><i class="fas fa-times-circle" style="color: var(--status-declined-text);"></i></div>
                        <div class="card-info">
                            <span class="card-title">Ditolak</span>
                            <span class="card-value">1</span>
                        </div>
                    </div>
                </div>

                <div class="recent-activity">
                    <h2>Pengaduan Terbaru Anda</h2>
                    <table class="activity-table">
                        <thead>
                            <tr>
                                <th>ID Pengaduan</th>
                                <th>Topik</th>
                                <th>Tanggal Kirim</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#SC-00123</td>
                                <td>Jalan Rusak</td>
                                <td>29 Juni 2025</td>
                                <td><span class="status-badge pending">PENDING</span></td>
                                <td><a href="#" class="action-link">Lihat Detail</a></td>
                            </tr>
                            <tr>
                                <td>#SC-00122</td>
                                <td>Pelayanan Publik</td>
                                <td>28 Juni 2025</td>
                                <td><span class="status-badge active">DIPROSES</span></td>
                                <td><a href="#" class="action-link">Lihat Detail</a></td>
                            </tr>
                            <tr>
                                <td>#SC-00121</td>
                                <td>Sampah Menumpuk</td>
                                <td>25 Juni 2025</td>
                                <td><span class="status-badge closed">SELESAI</span></td>
                                <td><a href="#" class="action-link">Lihat Detail</a></td>
                            </tr>
                            <tr>
                                <td>#SC-00120</td>
                                <td>Pungli</td>
                                <td>20 Juni 2025</td>
                                <td><span class="status-badge declined">DITOLAK</span></td>
                                <td><a href="#" class="action-link">Lihat Detail</a></td>
                            </tr>
                            <tr>
                                <td>#SC-00119</td>
                                <td>Lampu Jalan Mati</td>
                                <td>18 Juni 2025</td>
                                <td><span class="status-badge active">DIPROSES</span></td>
                                <td><a href="#" class="action-link">Lihat Detail</a></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="view-all-complaints">
                        <a href="#">Lihat Semua Pengaduan Anda <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>

                <div class="quick-links-card">
                    <h2>Butuh Bantuan?</h2>
                    <ul>
                        <li><a href="#"><i class="fas fa-question-circle"></i> FAQ (Pertanyaan Sering Diajukan)</a></li>
                        <li><a href="#"><i class="fas fa-info-circle"></i> Panduan Penggunaan</a></li>
                        <li><a href="#"><i class="fas fa-envelope"></i> Hubungi Dukungan</a></li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
</body>
</html>