<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Melindungi halaman
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['Admin', 'Petugas'])) {
    header("Location: ../../login.php"); // Path disesuaikan
    exit();
}

// Mengambil data sesi pengguna
$user_name = $_SESSION['nama'];
$user_role = $_SESSION['role'];

// Memanggil koneksi database
require '../../inc/koneksi.php';

// Mengambil data aduan
$query = "SELECT 
            a.idpengaduan, 
            a.judul, 
            a.status, 
            a.waktu_aduan, 
            p.nama AS nama_pengadu
          FROM pengaduan a
          JOIN pengguna p ON a.iduser = p.iduser
          ORDER BY a.waktu_aduan DESC";

$result = mysqli_query($conn, $query);

$pengaduan = [];
if ($result) {
    $pengaduan = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    // Menampilkan error jika query gagal
    die("Gagal mengambil data aduan: " . mysqli_error($conn)); 
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aduan Fasilitas - SiCepu</title>
    
    <!-- CSS dari Dashboard -->
    <link rel="stylesheet" href="../../assets/css/dash_admin.css">
    <!-- CSS khusus untuk tabel (jika ada styling unik) -->
    <link rel="stylesheet" href="../../assets/css/users.css"> 
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Tambahan style untuk memastikan konsistensi */
        .main-content {
            padding: 20px;
            background-color: #f0f2f5;
        }
        .content-container {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .table-header h3 {
            margin: 0;
            color: #333;
        }
        .filter-box {
            display: flex;
            gap: 10px;
        }
        .filter-input, .filter-select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar disalin dari dashboard_admin.php dengan path yang disesuaikan -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../../assets/img/logos.png" alt="SiCepu Logo" class="logo">
                <span class="logo-text">SiCepu</span>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="../../dashboard/dashboard_admin.php" class="nav-link"><i class="fas fa-th-large"></i> Dashboard</a></li>
                    <li><a href="#" class="nav-link active"><i class="fas fa-boxes"></i> Aduan Fasilitas</a></li>
                    <li><a href="../jenis/jenis_lihat.php" class="nav-link"><i class="fas fa-file-alt"></i> Jenis Aduan </a></li>
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
            <!-- Header disalin dari dashboard_admin.php -->
            <header class="navbar">
                <div class="top-info-bar">
                    <!-- Status koneksi bisa ditambahkan kembali jika perlu -->
                </div>

                <div class="nav-icons">
                    <a href="#"><i class="fas fa-bell"></i></a>
                    <a href="#"><i class="fas fa-comment"></i></a>
                    <div class="user-profile">
                        <img src="../../assets/img/admin_pfp.jpg" alt="User Avatar" class="avatar">
                        <span><?php echo htmlspecialchars($user_name); ?></span>
                        <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i></a>
                    </div>
                </div>
            </header>

            <!-- Konten Utama Halaman Aduan -->
            <section class="content-header">
                <h2>Manajemen Aduan Fasilitas</h2>
            </section>

            <div class="content-container">
                <div class="table-header">
                    <h3>Daftar Semua Aduan</h3>
                    <div class="header-actions">
                         <div class="filter-box">
                            <input type="text" placeholder="Cari aduan..." class="filter-input">
                            <select class="filter-select">
                              <option>Semua Status</option>
                              <option>Masuk</option>
                              <option>Diproses</option>
                              <option>Selesai</option>
                            </select>
                            <button class="btn-secondary"><i class="fas fa-download"></i> Ekspor</button>
                        </div>
                    </div>
                </div>

                <section class="customer-table-section">
                  <table>
                    <thead>
                      <tr>
                        <th>ID Aduan</th>
                        <th>Pengadu</th>
                        <th>Judul Aduan</th>
                        <th>Waktu Kirim</th>
                        <th>Status</th>
                        <th>Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($pengaduan)): ?>
                          <?php foreach ($pengaduan as $aduan): ?>
                          <tr>
                            <td><?php echo htmlspecialchars($aduan['idpengaduan']); ?></td>
                            <td><?php echo htmlspecialchars($aduan['nama_pengadu']); ?></td>
                            <td><?php echo htmlspecialchars($aduan['judul']); ?></td>
                            <td><?php echo date('d M Y, H:i', strtotime($aduan['waktu_aduan'])); ?></td>
                            <td>
                              <span class="status-badge <?php echo strtolower(htmlspecialchars($aduan['status'])); ?>">
                                <?php echo htmlspecialchars($aduan['status']); ?>
                              </span>
                            </td>
                            <td>
                                <a href="adu_detail.php?id=<?php echo $aduan['idpengaduan']; ?>" title="Lihat Detail"><i class="fas fa-eye action-icon"></i></a>
                                <a href="adu_detail.php?id=<?php echo $aduan['idpengaduan']; ?>" title="Edit"><i class="fas fa-edit action-icon"></i></a>
                                <a href="adu_hapus.php?id=<?php echo $aduan['idpengaduan']; ?>" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus aduan ini?');"><i class="fas fa-trash action-icon"></i></a>
                            </td>
                          </tr>
                          <?php endforeach; ?>
                      <?php else: ?>
                          <tr>
                            <td colspan="6" style="text-align: center; padding: 20px;">Belum ada data aduan.</td>
                          </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </section>

                <div class="pagination">
                  <a href="#" class="page-arrow"><i class="fas fa-chevron-left"></i></a>
                  <a href="#" class="page-number active">1</a>
                  <a href="#" class="page-arrow"><i class="fas fa-chevron-right"></i></a>
                </div>
            </div>

        </main>
    </div>

</body>
</html>
