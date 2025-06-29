<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// IMPORTANT: Ensure this is the very first thing to protect the page.
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['Admin', 'Petugas', 'Pengadu'])) {
    // If not logged in or role not recognized, redirect to login page.
    header("Location: ../login.php");
    exit();
}

// Include your database connection file
include "../inc/koneksi.php"; // Pastikan path ini benar

// Fetch user-specific data from session
$user_name = $_SESSION['nama'];
$user_role = $_SESSION['role'];

// --- FETCH DATA FOR DASHBOARD CARDS ---
// Pastikan koneksi database berhasil sebelum melakukan query
if ($koneksi) {
    // Query untuk mendapatkan jumlah aduan masuk
    $query_masuk = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pengaduan WHERE status = 'Masuk'");
    $data_masuk = mysqli_fetch_assoc($query_masuk);
    $total_masuk = $data_masuk['total'];

    // Query untuk mendapatkan jumlah aduan diproses
    $query_diproses = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pengaduan WHERE status = 'Diproses'");
    $data_diproses = mysqli_fetch_assoc($query_diproses);
    $total_diproses = $data_diproses['total'];

    // Query untuk mendapatkan jumlah aduan selesai
    $query_selesai = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pengaduan WHERE status = 'Selesai'");
    $data_selesai = mysqli_fetch_assoc($query_selesai);
    $total_selesai = $data_selesai['total'];

    // Query untuk mendapatkan total semua aduan
    $query_total_aduan = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pengaduan");
    $data_total_aduan = mysqli_fetch_assoc($query_total_aduan);
    $total_aduan = $data_total_aduan['total'];

    // Data untuk grafik (donut chart)
    $chart_data = json_encode([
        ['label' => 'Aduan Masuk', 'value' => $total_masuk],
        ['label' => 'Aduan Diproses', 'value' => $total_diproses],
        ['label' => 'Aduan Selesai', 'value' => $total_selesai]
    ]);

} else {
    // Jika koneksi gagal, set semua total ke 0
    $total_masuk = 0;
    $total_diproses = 0;
    $total_selesai = 0;
    $total_aduan = 0;
    $chart_data = json_encode([]); // Data kosong untuk grafik
    echo "<div style='color: red; text-align: center; padding: 10px; background-color: #ffe0e0;'>DEBUG: Koneksi database GAGAL! Pastikan MySQL Running dan database 'sicepu' sudah ada.</div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SiCepu</title>
    <link rel="stylesheet" href="../assets/css/dashadmin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/js/morris/morris-0.4.3.min.css">
    <style>
        /* Custom styles for dashboard cards */
        .dashboard-card-row {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        .dashboard-card {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            text-align: center;
            flex: 1 1 200px; /* Adjusts size based on available space */
            min-width: 200px;
            max-width: 28%; /* Roughly 3 cards per row */
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer; /* Indicates clickability */
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .dashboard-card .icon {
            font-size: 3em;
            margin-bottom: 10px;
            color: #4CAF50; /* Green default */
        }
        .dashboard-card .value {
            font-size: 2.2em;
            font-weight: 700;
            color: #333;
        }
        .dashboard-card .label {
            font-size: 1em;
            color: #666;
            margin-top: 5px;
        }

        /* Specific colors for cards */
        .dashboard-card.card-masuk .icon { color: #f44336; } /* Red */
        .dashboard-card.card-diproses .icon { color: #ff9800; } /* Orange */
        .dashboard-card.card-selesai .icon { color: #4CAF50; } /* Green */
        .dashboard-card.card-total .icon { color: #2196F3; } /* Blue */

        /* Chart container styling */
        .chart-container {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            min-height: 350px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        #morris-donut-chart {
            width: 100%;
            height: 300px;
            /* Flex properties for centering within chart-container */
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .dashboard-card {
                flex: 1 1 45%; /* Two cards per row on smaller screens */
                max-width: 48%;
            }
            #morris-donut-chart {
                height: 250px; /* Adjust height for smaller screens */
            }
        }
        @media (max-width: 480px) {
            .dashboard-card {
                flex: 1 1 90%; /* One card per row on very small screens */
                max-width: 95%;
            }
        }

    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/img/shodai_logo.png" alt="SiCepu Logo" class="logo">
                <span class="logo-text">SiCepu</span>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="#" class="nav-link active"><i class="fas fa-th-large"></i> Dashboard</a></li>
                    <li><a href="#" class="nav-link"><i class="fas fa-boxes"></i> Aduan Fasilitas</a></li>
                    <li><a href="../admin/pengguna/pengguna_lihat.php" class="nav-link"><i class="fas fa-users"></i> Pengadu </a></li>
                    <li><a href="#" class="nav-link"><i class="fas fa-envelope"></i> Pengguna </a></li>
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
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search...">
                </div>
                <div class="nav-icons">
                    <a href="#"><i class="fas fa-bell"></i></a>
                    <a href="#"><i class="fas fa-comment"></i></a>
                    <div class="user-profile">
                        <img src="../assets/img/user_avatar.jpg" alt="User Avatar" class="avatar">
                        <span><?php echo htmlspecialchars($user_name); ?></span>
                        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i></a>
                    </div>
                </div>
            </header>

            <section class="content-header">
                <h2>Selamat Datang, <?php echo htmlspecialchars($user_name); ?>!</h2>
            </section>

            <section style="padding: 20px; background-color: #f0f2f5;">
                <div class="dashboard-card-row">
                    <div class="dashboard-card card-masuk" onclick="alert('Aduan Masuk: <?php echo $total_masuk; ?>')">
                        <div class="icon"><i class="fas fa-inbox"></i></div>
                        <div class="value"><?php echo $total_masuk; ?></div>
                        <div class="label">Aduan Masuk</div>
                    </div>
                    <div class="dashboard-card card-diproses" onclick="alert('Aduan Diproses: <?php echo $total_diproses; ?>')">
                        <div class="icon"><i class="fas fa-sync-alt"></i></div>
                        <div class="value"><?php echo $total_diproses; ?></div>
                        <div class="label">Aduan Diproses</div>
                    </div>
                    <div class="dashboard-card card-selesai" onclick="alert('Aduan Selesai: <?php echo $total_selesai; ?>')">
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                        <div class="value"><?php echo $total_selesai; ?></div>
                        <div class="label">Aduan Selesai</div>
                    </div>
                    <div class="dashboard-card card-total" onclick="alert('Total Aduan: <?php echo $total_aduan; ?>')">
                        <div class="icon"><i class="fas fa-clipboard-list"></i></div>
                        <div class="value"><?php echo $total_aduan; ?></div>
                        <div class="label">Total Aduan</div>
                    </div>
                </div>

                <div class="chart-container">
                    <h3 style="margin-bottom: 20px; color: #333;">Distribusi Status Aduan</h3>
                    <div id="morris-donut-chart"></div>
                </div>
            </section>

        </main>
    </div>

    <script src="../assets/js/morris/raphael-2.1.0.min.js"></script>
    <script src="../assets/js/morris/morris.js"></script>
    <script>
        $(document).ready(function() {
            // Inisialisasi Morris Donut Chart
            new Morris.Donut({
                element: 'morris-donut-chart',
                data: <?php echo $chart_data; ?>,
                colors: ['#f44336', '#ff9800', '#4CAF50'], // Warna sesuai status: Masuk (Merah), Diproses (Oranye), Selesai (Hijau)
                resize: true, // Mengaktifkan responsivitas
                formatter: function (y, data) { return y + " Aduan" } // Format tooltip
            });
        });
    </script>
</body>
</html>