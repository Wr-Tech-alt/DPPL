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

// --- FETCH DATA FOR DASHBOARD CARDS AND NOTIFICATIONS ---
// Pastikan koneksi database berhasil sebelum melakukan query
// Variabel $koneksi akan ada jika koneksi di inc/koneksi.php berhasil
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

    // --- MODIFIKASI DISINI: Saring data untuk grafik ---
    $raw_chart_data = [
        ['label' => 'Aduan Masuk', 'value' => $total_masuk],
        ['label' => 'Aduan Diproses', 'value' => $total_diproses],
        ['label' => 'Aduan Selesai', 'value' => $total_selesai]
    ];

    $filtered_chart_data = [];
    foreach ($raw_chart_data as $data_point) {
        if ($data_point['value'] > 0) { // Hanya sertakan data jika nilainya lebih dari 0
            $filtered_chart_data[] = $data_point;
        }
    }
    $chart_data = json_encode($filtered_chart_data);
    // --- AKHIR MODIFIKASI ---

    // Jumlah aduan baru untuk notifikasi
    $new_complaints_count = $total_masuk;

} else {
    // Jika koneksi gagal, set semua total ke 0
    $total_masuk = 0;
    $total_diproses = 0;
    $total_selesai = 0;
    $total_aduan = 0;
    $chart_data = json_encode([]);
    $new_complaints_count = 0;
    // Pesan debug jika koneksi gagal sudah diinc/koneksi.php
    // echo "<div style='color: red; text-align: center; padding: 10px; background-color: #ffe0e0;'>DEBUG: Koneksi database GAGAL! Pastikan MySQL Running dan database 'sicepu' sudah ada.</div>";
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
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

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
        
        /* Notification badge styling */
        .nav-icons .notification-badge {
            position: absolute;
            top: -5px; /* Adjust as needed */
            right: -5px; /* Adjust as needed */
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7em;
            font-weight: bold;
            line-height: 1;
            min-width: 18px; /* Ensure it's a circle even with single digit */
            text-align: center;
        }
        .nav-icons .icon-wrapper {
            position: relative;
            display: inline-block; /* To contain the absolute positioned badge */
            margin-right: 15px; /* Space between icons */
        }

        /* New styles for top-info-bar */
        .navbar .top-info-bar {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-grow: 1; /* Allow it to take available space */
            padding-left: 20px; /* Adjust as needed */
            color: #333; /* Ubah ke warna gelap agar terbaca jelas */
        }
        .top-info-bar .status-info,
        .top-info-bar .time-location-info {
            display: flex;
            align-items: center;
            font-size: 0.95em;
            color: #333; /* Pastikan teks di dalamnya juga gelap */
        }
        .top-info-bar .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .top-info-bar .status-connected {
            background-color: #28a745; /* Green */
        }
        .top-info-bar .status-disconnected {
            background-color: #dc3545; /* Red */
        }
        /* Make sure it's responsive */
        @media (max-width: 992px) { /* Adjust breakpoint as needed */
            .navbar .top-info-bar {
                display: none; /* Hide on smaller screens to save space */
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
                <div class="top-info-bar">
                    <div class="status-info">
                        <span class="status-dot <?php echo (isset($koneksi) && $koneksi ? 'status-connected' : 'status-disconnected'); ?>"></span>
                        <span>Database: <?php echo (isset($koneksi) && $koneksi ? 'Connected' : 'Disconnected'); ?></span>
                    </div>
                    <div class="time-location-info">
                        <span id="currentDateTime"></span> | <span>Bekasi Regency, West Java, Indonesia</span>
                    </div>
                </div>

                <div class="nav-icons">
                    
                    <div class="icon-wrapper">
                        <a href="#" id="notificationBell">
                            <i class="fas fa-bell"></i>
                            <?php if ($new_complaints_count > 0): ?>
                                <span class="notification-badge"><?php echo $new_complaints_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
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

    <script src="../assets/js/jquery-1.10.2.js"></script>
    <script src="../assets/js/morris/raphael-2.1.0.min.js"></script>
    <script src="../assets/js/morris/morris.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

    <script>
        // Function to update current time and date
        function updateDateTime() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            document.getElementById('currentDateTime').innerText = now.toLocaleDateString('id-ID', options);
        }

        $(document).ready(function() {
            // Initial update
            updateDateTime();
            // Update every second
            setInterval(updateDateTime, 1000);

            // Inisialisasi Morris Donut Chart
            new Morris.Donut({
                element: 'morris-donut-chart',
                data: <?php echo $chart_data; ?>,
                colors: ['#f44336', '#ff9800', '#4CAF50'], // Warna sesuai status: Masuk (Merah), Diproses (Oranye), Selesai (Hijau)
                resize: true, // Mengaktifkan responsivitas
                formatter: function (y, data) { return y + " Aduan" } // Format tooltip
            });

            // SweetAlert2 for notification bell click
            $('#notificationBell').on('click', function(e) {
                e.preventDefault(); // Mencegah href="#" dari melompat ke atas halaman
                let newComplaints = <?php echo $new_complaints_count; ?>;
                let title = newComplaints > 0 ? 'Notifikasi Aduan Baru!' : 'Tidak Ada Aduan Baru';
                let text = newComplaints > 0 ? `Anda memiliki ${newComplaints} aduan baru yang masuk.` : 'Belum ada aduan baru yang perlu ditindaklanjuti.';
                let icon = newComplaints > 0 ? 'info' : 'success'; // 'info' atau 'warning' untuk ada, 'success' untuk tidak ada

                Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    confirmButtonText: 'Oke'
                });
            });

            // Update onclick for dashboard cards to use SweetAlert2 (optional, if you want consistent popups)
            $('.dashboard-card').off('click').on('click', function() {
                let cardLabel = $(this).find('.label').text();
                let cardValue = $(this).find('.value').text();
                Swal.fire({
                    title: cardLabel,
                    text: `Jumlah: ${cardValue}`,
                    icon: 'info',
                    confirmButtonText: 'Oke'
                });
            });
        });
    </script>
</body>
</html>