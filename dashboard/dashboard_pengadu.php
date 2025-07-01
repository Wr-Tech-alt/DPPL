<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Sertakan file koneksi database
require_once __DIR__ . '/../inc/koneksi.php';

// Cek apakah user sudah login dan memiliki peran 'Pengadu'
if (!isset($_SESSION['iduser']) || $_SESSION['role'] !== 'Pengadu') {
    // Jika tidak, redirect ke halaman login
    header('Location: ../login.php');
    exit();
}

// Mengambil data pengguna dari session dan database
$iduser = $_SESSION['iduser'];
$user_name = "Pengadu"; // Default name
$user_email = "pengadu@example.com"; // Default email

$stmt = $conn->prepare("SELECT nama, email FROM pengguna WHERE iduser = ?");
$stmt->bind_param("i", $iduser);
$stmt->execute();
$result = $stmt->get_result();
if ($user_data = $result->fetch_assoc()) {
    $user_name = htmlspecialchars($user_data['nama']);
    $user_email = htmlspecialchars($user_data['email']);
}
$stmt->close();

// --- MENGAMBIL DATA UNTUK KARTU STATISTIK ---
$statuses = ['Pending', 'Diproses', 'Selesai', 'Ditolak'];
$stats = [];
foreach ($statuses as $status) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM pengaduan WHERE iduser = ? AND status = ?");
    $stmt->bind_param("is", $iduser, $status);
    $stmt->execute();
    $stats[$status] = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
}

// --- MENGAMBIL DATA UNTUK TABEL RIWAYAT ---
$stmt = $conn->prepare("SELECT idpengaduan, judul, waktu_aduan, status FROM pengaduan WHERE iduser = ? ORDER BY waktu_aduan DESC LIMIT 5");
$stmt->bind_param("i", $iduser);
$stmt->execute();
$result_aduan = $stmt->get_result();
$pengaduan_terbaru = $result_aduan->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiCepu - Dashboard Pengadu</title>
    <link rel="stylesheet" href="../assets/css/dash_pengadu.css"> <!-- Pastikan file CSS ini ada dan sesuai -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS Reset & Basic Styling */
        :root {
            --primary-color: #007bff;
            --primary-hover: #0056b3;
            --text-color: #333;
            --bg-color: #f4f7f6;
            --sidebar-bg: #ffffff;
            --card-bg: #ffffff;
            --border-color: #e0e0e0;
        }
        body {
            font-family: 'Raleway', sans-serif;
            margin: 0;
            background-color: var(--bg-color);
            color: var(--text-color);
        }
        .app-container {
            display: flex;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            transition: transform 0.3s ease-in-out;
            z-index: 1000;
        }
        .sidebar-header, .user-profile, .sidebar-nav ul, .sidebar-footer {
            padding: 1.2rem 1.5rem;
        }
        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5em;
            font-weight: 700;
        }
        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
        }
        .user-avatar {
            width: 45px; height: 45px; border-radius: 50%;
            background-color: var(--primary-color); color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 1.2em;
        }
        .user-name { font-weight: 600; }
        .user-email { font-size: 0.85em; color: #777; }
        .sidebar-nav { flex-grow: 1; }
        .sidebar-nav ul { list-style: none; margin: 0; padding: 0; }
        .sidebar-nav li a {
            display: flex; align-items: center; gap: 15px;
            padding: 0.9rem 1.5rem; text-decoration: none;
            color: #555; border-radius: 8px; margin-bottom: 5px;
            transition: background-color 0.2s, color 0.2s;
        }
        .sidebar-nav li a:hover, .sidebar-nav li.active a {
            background-color: var(--primary-color);
            color: white;
        }
        .sidebar-footer a { text-decoration: none; color: #555; }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            padding: 1.5rem;
            transition: margin-left 0.3s ease-in-out;
        }
        .navbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        .hamburger-menu {
            display: none; background: none; border: none;
            font-size: 1.5em; cursor: pointer; color: var(--text-color);
        }
        .content-header { margin-bottom: 1.5rem; }
        .btn-primary {
            background-color: var(--primary-color); color: white;
            padding: 12px 22px; border: none; border-radius: 8px;
            cursor: pointer; font-size: 1em; font-weight: 600;
            text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-primary:hover { background-color: var(--primary-hover); }

        /* Summary Cards */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .card {
            background-color: var(--card-bg);
            padding: 1.5rem; border-radius: 12px;
            display: flex; align-items: center; gap: 1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .card-icon {
            width: 50px; height: 50px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5em;
        }
        .card-title { font-size: 0.9em; color: #666; }
        .card-value { font-size: 1.8em; font-weight: 700; }

        /* Recent Activity Table */
        .recent-activity {
            background-color: var(--card-bg);
            padding: 1.5rem; border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .activity-table { width: 100%; border-collapse: collapse; }
        .activity-table th, .activity-table td {
            padding: 1rem; text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        .activity-table th { font-size: 0.9em; color: #777; text-transform: uppercase; }
        .status-badge {
            padding: 4px 10px; border-radius: 15px; font-size: 0.8em;
            font-weight: 600;
        }
        .status-badge.pending, .status-badge.masuk { background-color: #fff0c2; color: #f39c12; }
        .status-badge.diproses { background-color: #cce5ff; color: #007bff; }
        .status-badge.selesai { background-color: #d4edda; color: #28a745; }
        .status-badge.ditolak { background-color: #f8d7da; color: #dc3545; }
        .action-link { color: var(--primary-color); text-decoration: none; font-weight: 600; }

        /* Responsive Design */
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; width: 100%; }
            .hamburger-menu { display: block; }
            .navbar { justify-content: flex-start; gap: 15px; }
        }
        @media (max-width: 768px) {
            .activity-table thead { display: none; }
            .activity-table, .activity-table tbody, .activity-table tr, .activity-table td {
                display: block; width: 100%;
            }
            .activity-table tr {
                margin-bottom: 1rem; border: 1px solid var(--border-color);
                border-radius: 8px; overflow: hidden;
            }
            .activity-table td {
                text-align: right; padding-left: 50%;
                position: relative; border-bottom: 1px solid #eee;
            }
            .activity-table td:last-child { border-bottom: none; }
            .activity-table td::before {
                content: attr(data-label);
                position: absolute; left: 1rem;
                width: calc(50% - 2rem);
                font-weight: bold; text-align: left;
            }
        }
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
                    <li class="active"><a href="#../dashboard/dashboard_pengadu.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="../pengadu/riwayat_aduan.php"><i class="fas fa-clipboard-list"></i> Riwayat Pengaduan</a></li>
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
                <h1>Dashboard</h1>
            </header>

            <div class="content-header">
                <h2>Selamat Datang Kembali, <?php echo $user_name; ?>!</h2>
                <p class="greeting-text">Ada keluhan yang ingin Anda sampaikan hari ini?</p>
                <a href="../pengadu/adu_form_tambah.php" class="btn btn-primary"><i class="fas fa-plus"></i> Buat Pengaduan Baru</a>
            </div>

            <div class="dashboard-content">
                <div class="summary-cards">
                    <div class="card">
                        <div class="card-icon" style="background-color: #fff0c2;"><i class="fas fa-hourglass-half" style="color: #f39c12;"></i></div>
                        <div class="card-info">
                            <span class="card-title">Pending</span>
                            <span class="card-value"><?php echo $stats['Pending']; ?></span>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon" style="background-color: #cce5ff;"><i class="fas fa-sync-alt" style="color: #007bff;"></i></div>
                        <div class="card-info">
                            <span class="card-title">Diproses</span>
                            <span class="card-value"><?php echo $stats['Diproses']; ?></span>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon" style="background-color: #d4edda;"><i class="fas fa-check-circle" style="color: #28a745;"></i></div>
                        <div class="card-info">
                            <span class="card-title">Selesai</span>
                            <span class="card-value"><?php echo $stats['Selesai']; ?></span>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon" style="background-color: #f8d7da;"><i class="fas fa-times-circle" style="color: #dc3545;"></i></div>
                        <div class="card-info">
                            <span class="card-title">Ditolak</span>
                            <span class="card-value"><?php echo $stats['Ditolak']; ?></span>
                        </div>
                    </div>
                </div>

                <div class="recent-activity">
                    <h2>Aktivitas Terbaru</h2>
                    <table class="activity-table">
                        <thead>
                            <tr>
                                <th>ID Aduan</th>
                                <th>Judul</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pengaduan_terbaru)): ?>
                                <?php foreach ($pengaduan_terbaru as $aduan): ?>
                                <tr>
                                    <td data-label="ID Aduan">#<?php echo htmlspecialchars($aduan['idpengaduan']); ?></td>
                                    <td data-label="Judul"><?php echo htmlspecialchars($aduan['judul']); ?></td>
                                    <td data-label="Tanggal"><?php echo date('d M Y', strtotime($aduan['waktu_aduan'])); ?></td>
                                    <td data-label="Status"><span class="status-badge <?php echo strtolower(htmlspecialchars($aduan['status'])); ?>"><?php echo htmlspecialchars($aduan['status']); ?></span></td>
                                    <td data-label="Aksi"><a href="#" class="action-link">Lihat Detail</a></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 20px;">Belum ada aktivitas pengaduan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hamburgerMenu = document.getElementById('hamburgerMenu');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.main-content');

            hamburgerMenu.addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });
            
            // Optional: Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 992) {
                    const isClickInsideSidebar = sidebar.contains(event.target);
                    const isClickOnHamburger = hamburgerMenu.contains(event.target);
                    if (!isClickInsideSidebar && !isClickOnHamburger && sidebar.classList.contains('show')) {
                        sidebar.classList.remove('show');
                    }
                }
            });
        });
    </script>
</body>
</html>
