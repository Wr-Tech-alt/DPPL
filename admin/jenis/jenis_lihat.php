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

// Mengambil data jenis fasilitas
// Assuming the table for facility types is named 'jenis_fasilitas'
$query = "SELECT idjenis, jenis FROM jenis_fasilitas ORDER BY idjenis ASC";

$result = mysqli_query($conn, $query);

$jenis_fasilitas = []; // Change variable name to reflect the data being fetched
if ($result) {
    $jenis_fasilitas = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    // Menampilkan error jika query gagal
    die("Gagal mengambil data jenis fasilitas: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jenis Fasilitas - SiCepu</title>
    
    <link rel="stylesheet" href="../../assets/css/dash_admin.css">
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

        /* Styles for action icons (assuming they are small and inline) */
        .action-icon {
            margin-right: 5px; /* Adjust as needed for spacing between icons */
            color: #007bff; /* Example color */
        }
        .action-icon.fa-edit {
            color: #ffc107; /* Example color for edit */
        }
        .action-icon.fa-trash {
            color: #dc3545; /* Example color for delete */
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
                    <li><a href="#" class="nav-link active"><i class="fas fa-boxes"></i> Jenis Fasilitas</a></li>
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

            <section class="content-header">
                <h2>Manajemen Jenis Fasilitas</h2>
            </section>

            <div class="content-container">
                <div class="table-header">
                    <h3>Daftar Jenis Fasilitas</h3>
                    <div class="header-actions">
                        <div class="filter-box">
                            <input type="text" placeholder="Cari jenis fasilitas..." class="filter-input">
                            <button class="btn-secondary"><i class="fas fa-plus"></i> Tambah Baru</button> </div>
                    </div>
                </div>

                <section class="customer-table-section">
                    <table>
                        <thead>
                            <tr>
                                <th>ID Jenis</th>
                                <th>Jenis Fasilitas</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($jenis_fasilitas)): ?>
                                <?php foreach ($jenis_fasilitas as $jenis_fa): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($jenis_fa['idjenis']); ?></td>
                                        <td><?php echo htmlspecialchars($jenis_fa['jenis']); ?></td>
                                        <td>
                                            <a href="jenis_fasilitas_edit.php?id=<?php echo $jenis_fa['idjenis']; ?>" title="Edit"><i class="fas fa-edit action-icon"></i></a>
                                            <a href="jenis_fasilitas_delete.php?id=<?php echo $jenis_fa['idjenis']; ?>" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus jenis fasilitas ini?');"><i class="fas fa-trash action-icon"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 20px;">Belum ada data jenis fasilitas.</td>
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