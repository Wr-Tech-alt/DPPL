<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Protect page access
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['Admin', 'Petugas'])) {
    header("Location: ../login.php");
    exit();
}

require '../../inc/koneksi.php';

// Fetch aduan with jenis "Fasilitas"
$query = "SELECT 
            a.idpengaduan, 
            a.judul, 
            a.status, 
            a.waktu_aduan, 
            p.nama AS nama_pengadu
          FROM pengaduan a
          JOIN pengguna p ON a.iduser = p.iduser
          JOIN jenis_pengaduan j ON a.idjenis = j.idjenis
          ORDER BY a.waktu_aduan DESC";

$result = mysqli_query($conn, $query); // This is the only query execution needed

$pengaduan = [];
if ($result) {
    $pengaduan = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    // It's good to die here if the query failed, as the rest of the page depends on this data
    die("Gagal ambil data: " . mysqli_error($conn)); 
}
?>

<!DOCTYPE html>
<head>
  <meta charset="UTF-8">
  <title>Admin - Aduan Fasilitas</title>
  
  <link rel="stylesheet" href="../../assets/css/dash_admin.css"> <link rel="stylesheet" href="../../assets/css/users.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="dashboard-wrapper">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <h3>SiCepu Admin</h3>
    </div>
    <ul class="sidebar-menu">
      <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
      <li><a href="pengadu_lihat.php"><i class="fas fa-user"></i> Data Pengadu</a></li>
      <li class="active"><a href="aduan_fasilitas.php"><i class="fas fa-tools"></i> Aduan Fasilitas</a></li>
    </ul>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
    <header class="navbar">
      <div class="navbar-left">
        <h2>Aduan Fasilitas</h2>
      </div>
      <div class="navbar-right">
        <span class="user-role"><?php echo $_SESSION['role']; ?></span>
      </div>
    </header>

    <section class="content-header">
      <div class="header-actions">
        <button class="btn-secondary"><i class="fas fa-download"></i> Ekspor</button>
      </div>
      <div class="filter-box">
        <input type="text" placeholder="Cari aduan..." class="filter-input">
        <select class="filter-select">
          <option>Status</option>
          <option>Menunggu</option>
          <option>Diproses</option>
          <option>Selesai</option>
        </select>
      </div>
    </section>

    <section class="customer-table-section">
      <table>
        <thead>
          <tr>
            <th>ID Aduan</th>
            <th>Pengadu</th>
            <th>Topik</th>
            <th>Jenis</th>
            <th>Waktu Kirim</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pengaduan as $aduan): ?>
          <tr>
            <td><?php echo htmlspecialchars($aduan['idpengaduan']); ?></td>
            <td><?php echo htmlspecialchars($aduan['nama_pengadu']); ?></td>
            <td><?php echo htmlspecialchars($aduan['judul']); ?></td>
            <td>Fasilitas</td>
            <td><?php echo date('d M Y H:i', strtotime($aduan['waktu_aduan'])); ?></td>
            <td>
              <span class="status-badge <?php echo strtolower($aduan['status']); ?>">
                <?php echo htmlspecialchars($aduan['status']); ?>
              </span>
            </td>
            <td><i class="fas fa-ellipsis-h action-icon"></i></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>

    <div class="pagination">
      <a href="#" class="page-arrow"><i class="fas fa-chevron-left"></i></a>
      <a href="#" class="page-number active">1</a>
      <a href="#" class="page-arrow"><i class="fas fa-chevron-right"></i></a>
    </div>
  </main>
</div>

</body>
</html>
