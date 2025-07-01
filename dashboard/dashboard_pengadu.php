<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Memanggil koneksi database
require_once __DIR__ . '/../inc/koneksi.php';

// Cek apakah pengguna sudah login dan memiliki peran 'Pengadu'
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Pengadu') {
    header("Location: ../login.php");
    exit();
}

// Mengambil data sesi pengguna
$iduser = $_SESSION['iduser'];
$user_name = $_SESSION['nama'];

// --- MENGAMBIL DATA UNTUK KARTU STATISTIK ---
$query_masuk = $conn->prepare("SELECT COUNT(*) AS total FROM pengaduan WHERE iduser = ? AND status = 'Masuk'");
$query_masuk->bind_param("i", $iduser);
$query_masuk->execute();
$total_masuk = $query_masuk->get_result()->fetch_assoc()['total'];

$query_diproses = $conn->prepare("SELECT COUNT(*) AS total FROM pengaduan WHERE iduser = ? AND status = 'Diproses'");
$query_diproses->bind_param("i", $iduser);
$query_diproses->execute();
$total_diproses = $query_diproses->get_result()->fetch_assoc()['total'];

$query_selesai = $conn->prepare("SELECT COUNT(*) AS total FROM pengaduan WHERE iduser = ? AND status = 'Selesai'");
$query_selesai->bind_param("i", $iduser);
$query_selesai->execute();
$total_selesai = $query_selesai->get_result()->fetch_assoc()['total'];

// --- MENGAMBIL DATA UNTUK TABEL RIWAYAT ---
$query_aduan = $conn->prepare("SELECT idpengaduan, judul, waktu_aduan, status FROM pengaduan WHERE iduser = ? ORDER BY waktu_aduan DESC LIMIT 5");
$query_aduan->bind_param("i", $iduser);
$query_aduan->execute();
$result_aduan = $query_aduan->get_result();
$pengaduan_terbaru = $result_aduan->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengadu - SiCepu</title>
    
    <!-- Menggunakan CSS yang sama dengan Dashboard Admin -->
    <link rel="stylesheet" href="../assets/css/dash_admin.css">
    <link rel="stylesheet" href="../assets/css/users.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Style tambahan untuk kartu dan tombol khusus pengadu */
        .dashboard-card .icon { font-size: 2.5em; }
        .dashboard-card .value { font-size: 2em; }
        .btn-primary {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .content-container {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar untuk Pengadu -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/img/logos.png" alt="SiCepu Logo" class="logo">
                <span class="logo-text">SiCepu</span>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="#" class="nav-link active"><i class="fas fa-th-large"></i> Dashboard</a></li>
                    <li><a href="aduan/adu_buat.php" class="nav-link"><i class="fas fa-plus-circle"></i> Buat Aduan</a></li>
                    <li><a href="aduan/adu_riwayat.php" class="nav-link"><i class="fas fa-history"></i> Riwayat Aduan</a></li>
                </ul>
                <div class="nav-section-title">AKUN</div>
                <ul>
                    <li><a href="#" class="nav-link"><i class="fas fa-cog"></i> Pengaturan</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <!-- Header disamakan dengan Admin -->
            <header class="navbar">
                <div class="top-info-bar">
                    <!-- Kosongkan atau bisa diisi info lain -->
                </div>
                <div class="nav-icons">
                    <div class="user-profile">
                        <img src="../assets/img/admin_pfp.jpg" alt="User Avatar" class="avatar">
                        <span><?php echo htmlspecialchars($user_name); ?></span>
                        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i></a>
                    </div>
                </div>
            </header>

            <!-- Konten Utama Dashboard Pengadu -->
            <section class="content-header">
                <h2>Selamat Datang, <?php echo htmlspecialchars($user_name); ?>!</h2>
                <p>Siap melaporkan keluhan Anda? Klik tombol di bawah ini.</p>
                <a href="aduan/adu_buat.php" class="btn btn-primary"><i class="fas fa-plus"></i> Buat Pengaduan Baru</a>
            </section>

            <section style="padding: 20px; background-color: #f0f2f5;">
                <!-- Kartu Statistik Aduan Pengguna -->
                <div class="dashboard-card-row">
                    <div class="dashboard-card card-masuk">
                        <div class="icon"><i class="fas fa-inbox"></i></div>
                        <div class="value"><?php echo $total_masuk; ?></div>
                        <div class="label">Aduan Masuk</div>
                    </div>
                    <div class="dashboard-card card-diproses">
                        <div class="icon"><i class="fas fa-sync-alt"></i></div>
                        <div class="value"><?php echo $total_diproses; ?></div>
                        <div class="label">Aduan Diproses</div>
                    </div>
                    <div class="dashboard-card card-selesai">
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                        <div class="value"><?php echo $total_selesai; ?></div>
                        <div class="label">Aduan Selesai</div>
                    </div>
                </div>

                <!-- Tabel Riwayat Aduan Terbaru -->
                <div class="content-container">
                    <h3>Riwayat Aduan Terbaru</h3>
                    <section class="customer-table-section">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID Aduan</th>
                                    <th>Judul Aduan</th>
                                    <th>Tanggal Kirim</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($pengaduan_terbaru)): ?>
                                    <?php foreach ($pengaduan_terbaru as $aduan): ?>
                                    <tr>
                                        <td>#<?php echo htmlspecialchars($aduan['idpengaduan']); ?></td>
                                        <td><?php echo htmlspecialchars($aduan['judul']); ?></td>
                                        <td><?php echo date('d M Y, H:i', strtotime($aduan['waktu_aduan'])); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo strtolower(htmlspecialchars($aduan['status'])); ?>">
                                                <?php echo htmlspecialchars($aduan['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="aduan/adu_detail.php?id=<?php echo $aduan['idpengaduan']; ?>" title="Lihat Detail"><i class="fas fa-eye action-icon"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 20px;">Anda belum pernah membuat aduan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </section>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
