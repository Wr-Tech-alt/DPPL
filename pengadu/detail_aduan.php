<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Sertakan file koneksi database
require_once __DIR__ . '/../inc/koneksi.php';

// Cek apakah user sudah login dan memiliki peran 'Pengadu'
if (!isset($_SESSION['iduser']) || $_SESSION['role'] !== 'Pengadu') {
    header('Location: ../login.php');
    exit();
}

// Mengambil data pengguna dari session
$iduser = $_SESSION['iduser'];
$user_name = $_SESSION['nama'] ?? 'Pengadu';
$user_email = ''; // Bisa diambil jika diperlukan

// Validasi ID Aduan dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Error: ID Aduan tidak valid.");
}
$idpengaduan = $_GET['id'];

// Mengambil detail aduan spesifik dari database
$aduan = null;
$stmt = $conn->prepare(
    "SELECT a.*, j.jenis 
     FROM pengaduan a 
     JOIN jenis_pengaduan j ON a.idjenis = j.idjenis 
     WHERE a.idpengaduan = ? AND a.iduser = ?
     LIMIT 1"
);
$stmt->bind_param("ii", $idpengaduan, $iduser);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $aduan = $result->fetch_assoc();
}
$stmt->close();
$conn->close();

// Jika aduan tidak ditemukan (atau bukan milik user), tampilkan pesan error
if (!$aduan) {
    die("Aduan tidak ditemukan atau Anda tidak memiliki hak akses.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Aduan - SiCepu</title>
    <link rel="stylesheet" href="../assets/css/dash_pengadu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Menggunakan CSS dari dashboard_pengadu.php dan menambahkan style untuk detail */
        :root {
            --primary-color: #007bff; --primary-hover: #0056b3; --text-color: #333;
            --bg-color: #f4f7f6; --sidebar-bg: #ffffff; --card-bg: #ffffff;
            --border-color: #e0e0e0; --tanggapan-bg: #e9ecef;
        }
        body { font-family: 'Raleway', sans-serif; margin: 0; background-color: var(--bg-color); color: var(--text-color); }
        .app-container { display: flex; }
        .sidebar { width: 260px; background-color: var(--sidebar-bg); border-right: 1px solid var(--border-color); display: flex; flex-direction: column; height: 100vh; position: fixed; top: 0; left: 0; transition: transform 0.3s ease-in-out; z-index: 1000; }
        .sidebar-header, .user-profile, .sidebar-nav ul, .sidebar-footer { padding: 1.2rem 1.5rem; }
        .sidebar-header { display: flex; align-items: center; gap: 10px; font-size: 1.5em; font-weight: 700; }
        .user-profile { display: flex; align-items: center; gap: 15px; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); }
        .user-avatar { width: 45px; height: 45px; border-radius: 50%; background-color: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2em; }
        .user-name { font-weight: 600; }
        .user-email { font-size: 0.85em; color: #777; }
        .sidebar-nav { flex-grow: 1; }
        .sidebar-nav ul { list-style: none; margin: 0; padding: 0; }
        .sidebar-nav li a { display: flex; align-items: center; gap: 15px; padding: 0.9rem 1.5rem; text-decoration: none; color: #555; border-radius: 8px; margin-bottom: 5px; transition: background-color 0.2s, color 0.2s; }
        .sidebar-nav li a:hover, .sidebar-nav li.active a { background-color: var(--primary-color); color: white; }
        .sidebar-footer a { text-decoration: none; color: #555; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 1.5rem; transition: margin-left 0.3s ease-in-out; }
        .navbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        .hamburger-menu { display: none; background: none; border: none; font-size: 1.5em; cursor: pointer; color: var(--text-color); }

        /* Detail Aduan Styling */
        .detail-container {
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .detail-header {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        .detail-title {
            font-size: 1.8em;
            font-weight: 700;
            margin: 0;
            color: var(--text-color);
        }
        .detail-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            color: #777;
            font-size: 0.9em;
            margin-top: 0.5rem;
        }
        .detail-meta span { display: flex; align-items: center; gap: 5px; }
        .status-badge { padding: 4px 10px; border-radius: 15px; font-size: 0.8em; font-weight: 600; }
        .status-badge.pending, .status-badge.masuk { background-color: #fff0c2; color: #f39c12; }
        .status-badge.diproses { background-color: #cce5ff; color: #007bff; }
        .status-badge.selesai { background-color: #d4edda; color: #28a745; }
        .status-badge.ditolak { background-color: #f8d7da; color: #dc3545; }
        
        .detail-body {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 2rem;
        }
        .detail-image-container img {
            width: 100%;
            border-radius: 12px;
            height: auto;
            border: 1px solid var(--border-color);
        }
        .detail-info h4 {
            margin-top: 0;
            margin-bottom: 0.5rem;
            font-size: 1.1em;
            color: #555;
        }
        .detail-info p {
            margin: 0 0 1.5rem 0;
            line-height: 1.6;
        }
        .status-badge.large {
            font-size: 1.1em;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 700;
        }
        .tanggapan-section {
            margin-top: 2rem;
            padding: 1.5rem;
            background-color: var(--tanggapan-bg);
            border-radius: 12px;
            border: 1px solid #dee2e6;
        }
        .tanggapan-section h3 {
            margin-top: 0;
            font-weight: 600;
        }
        .tanggapan-section p {
            margin-bottom: 0;
            line-height: 1.7;
        }
        .tanggapan-section .no-tanggapan {
            color: #777;
            font-style: italic;
        }
        .btn-back {
            display: inline-block;
            margin-top: 1.5rem;
            text-decoration: none;
            color: var(--primary-color);
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; width: 100%; }
            .hamburger-menu { display: block; }
            .navbar { justify-content: flex-start; gap: 15px; }
            
            /* PERUBAHAN DI SINI: Untuk tablet dan mobile, layout menjadi 1 kolom */
            .detail-body {
                grid-template-columns: 1fr;
            }
        }
        /* Media query untuk 768px dihapus karena sudah dicakup oleh 992px */

    </style>
</head>
<body>
    <div class="app-container">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="../assets/img/logos.png" alt="SiCepu Logo" class="logo" style="width:30px; height:30px;">
                <span class="logo-text">SiCepu</span>
            </div>
            <div class="user-profile">
                <div class="user-avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                <div class="user-info">
                    <span class="user-name"><?php echo $user_name; ?></span>
                    <span class="user-email"><?php echo $user_email; ?></span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard_pengadu.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="form_tambahaduan.php"><i class="fas fa-plus-circle"></i> Buat Aduan Baru</a></li>
                    <li class="active"><a href="riwayat_aduan.php"><i class="fas fa-clipboard-list"></i> Riwayat Pengaduan</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> Pengaturan Akun</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </aside>

        <main class="main-content">
            <header class="navbar">
                <button class="hamburger-menu" id="hamburgerMenu"><i class="fas fa-bars"></i></button>
                <h1>Detail Aduan</h1>
            </header>

            <div class="detail-container">
                <div class="detail-header">
                    <h2 class="detail-title"><?php echo htmlspecialchars($aduan['judul']); ?></h2>
                    <div class="detail-meta">
                        <!-- Status dipindahkan dari sini -->
                        <span><i class="fas fa-tag"></i> Jenis: <?php echo htmlspecialchars($aduan['jenis']); ?></span>
                        <span><i class="fas fa-calendar-alt"></i> Dilaporkan pada: <?php echo date('d F Y, H:i', strtotime($aduan['waktu_aduan'])); ?></span>
                    </div>
                </div>

                <div class="detail-body">
                    <div class="detail-image-container">
                        <img src="../uploads/<?php echo htmlspecialchars($aduan['gambar']); ?>" alt="Bukti Aduan">
                    </div>
                    <div class="detail-info">
                        <h4>Lokasi Kejadian</h4>
                        <p><?php echo htmlspecialchars($aduan['lokasi']); ?></p>

                        <h4>Keterangan Aduan</h4>
                        <p><?php echo nl2br(htmlspecialchars($aduan['keterangan'])); ?></p>
                        
                        <!-- Status ditempatkan di sini dengan ukuran lebih besar -->
                        <h4>Status Aduan Saat Ini</h4>
                        <p><span class="status-badge large <?php echo strtolower(htmlspecialchars($aduan['status'])); ?>"><?php echo htmlspecialchars($aduan['status']); ?></span></p>
                    </div>
                </div>

                <div class="tanggapan-section">
                    <h3><i class="fas fa-reply"></i> Tanggapan dari Petugas</h3>
                    <?php if (!empty($aduan['tanggapan'])): ?>
                        <p><?php echo nl2br(htmlspecialchars($aduan['tanggapan'])); ?></p>
                    <?php else: ?>
                        <p class="no-tanggapan">Belum ada tanggapan untuk aduan ini.</p>
                    <?php endif; ?>
                </div>
                
                <a href="riwayat_aduan.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Riwayat</a>
            </div>
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hamburgerMenu = document.getElementById('hamburgerMenu');
            const sidebar = document.getElementById('sidebar');
            hamburgerMenu.addEventListener('click', () => sidebar.classList.toggle('show'));
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 992 && !sidebar.contains(e.target) && !hamburgerMenu.contains(e.target) && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>
