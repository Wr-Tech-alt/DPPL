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

// Mengambil semua data aduan milik pengguna yang login
$list_aduan = [];
$stmt = $conn->prepare(
    "SELECT a.idpengaduan, a.judul, a.waktu_aduan, a.status, a.gambar, j.jenis 
     FROM pengaduan a 
     JOIN jenis_pengaduan j ON a.idjenis = j.idjenis 
     WHERE a.iduser = ? 
     ORDER BY a.waktu_aduan DESC"
);
$stmt->bind_param("i", $iduser);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $list_aduan = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Aduan - SiCepu</title>
    <link rel="stylesheet" href="../assets/css/dash_pengadu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Menggunakan CSS dari dashboard_pengadu.php dan menambahkan style untuk list aduan */
        :root {
            --primary-color: #007bff; --primary-hover: #0056b3; --text-color: #333;
            --bg-color: #f4f7f6; --sidebar-bg: #ffffff; --card-bg: #ffffff;
            --border-color: #e0e0e0;
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
        
        /* Aduan List Styling */
        .aduan-list-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .aduan-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: flex;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .aduan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
        .aduan-card-img {
            width: 150px;
            flex-shrink: 0;
            background-size: cover;
            background-position: center;
        }
        .aduan-card-content {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .aduan-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
        }
        .aduan-card-title {
            font-size: 1.2em;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
        }
        .aduan-card-meta {
            display: flex;
            gap: 1.5rem;
            color: #777;
            font-size: 0.9em;
            margin-bottom: 1rem;
        }
        .aduan-card-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .aduan-card-footer {
            margin-top: auto;
            display: flex;
            justify-content: flex-end;
        }
        .btn-detail {
            background-color: var(--primary-color); color: white; padding: 8px 18px; border: none;
            border-radius: 8px; cursor: pointer; font-size: 0.9em; font-weight: 600;
            text-decoration: none;
        }
        .btn-detail:hover { background-color: var(--primary-hover); }
        .status-badge {
            padding: 4px 10px; border-radius: 15px; font-size: 0.8em;
            font-weight: 600;
        }
        .status-badge.pending, .status-badge.masuk { background-color: #fff0c2; color: #f39c12; }
        .status-badge.diproses { background-color: #cce5ff; color: #007bff; }
        .status-badge.selesai { background-color: #d4edda; color: #28a745; }
        .status-badge.ditolak { background-color: #f8d7da; color: #dc3545; }
        .no-data {
            text-align: center;
            padding: 3rem;
            background-color: var(--card-bg);
            border-radius: 12px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; width: 100%; }
            .hamburger-menu { display: block; }
            .navbar { justify-content: flex-start; gap: 15px; }
        }
        @media (max-width: 768px) {
            .aduan-card {
                flex-direction: column;
            }
            .aduan-card-img {
                width: 100%;
                height: 180px;
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
                    <li><a href="../dashboard/dashboard_pengadu.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="active"><a href="../pengadu/riwayat_aduan.php"><i class="fas fa-clipboard-list"></i> Riwayat Pengaduan</a></li>
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
                <h1>Riwayat Aduan Saya</h1>
            </header>

            <div class="aduan-list-container">
                <?php if (!empty($list_aduan)): ?>
                    <?php foreach ($list_aduan as $aduan): ?>
                        <div class="aduan-card">
                            <div class="aduan-card-img" style="background-image: url('../uploads/<?php echo htmlspecialchars($aduan['gambar']); ?>');"></div>
                            <div class="aduan-card-content">
                                <div class="aduan-card-header">
                                    <h3 class="aduan-card-title"><?php echo htmlspecialchars($aduan['judul']); ?></h3>
                                    <span class="status-badge <?php echo strtolower(htmlspecialchars($aduan['status'])); ?>"><?php echo htmlspecialchars($aduan['status']); ?></span>
                                </div>
                                <div class="aduan-card-meta">
                                    <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($aduan['jenis']); ?></span>
                                    <span><i class="fas fa-calendar-alt"></i> <?php echo date('d M Y, H:i', strtotime($aduan['waktu_aduan'])); ?></span>
                                </div>
                                <div class="aduan-card-footer">
                                    <a href="detail_aduan.php?id=<?php echo $aduan['idpengaduan']; ?>" class="btn-detail">Lihat Detail</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-folder-open fa-3x" style="color: #ccc; margin-bottom: 1rem;"></i>
                        <h3>Anda Belum Memiliki Riwayat Aduan</h3>
                        <p>Semua aduan yang Anda buat akan muncul di halaman ini.</p>
                    </div>
                <?php endif; ?>
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
