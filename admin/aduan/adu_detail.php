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
include "../../inc/koneksi.php"; // Sesuaikan path jika diperlukan

// Fetch user-specific data from session
$user_name = $_SESSION['nama'];
$user_role = $_SESSION['role'];

$complaint_detail = null;
$error_message = "";

// Check if idpengaduan is provided in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_pengaduan = mysqli_real_escape_string($conn, $_GET['id']);

    if ($conn) {
        // Query to fetch all details for the specific complaint ID
        $query_detail = mysqli_query($conn, "SELECT 
                                                p.idpengaduan,
                                                p.waktu_aduan,
                                                p.judul,
                                                p.notelp,
                                                p.keterangan,
                                                p.lokasi,
                                                p.tanggapan,
                                                p.status,
                                                p.gambar,
                                                p.author,
                                                u.nama_user AS nama_pengadu,
                                                j.nama_jenis AS jenis_aduan
                                            FROM 
                                                pengaduan p
                                            LEFT JOIN 
                                                pengguna u ON p.iduser = u.iduser
                                            LEFT JOIN 
                                                tb_jenis_aduan j ON p.idjenis = j.id_jenis
                                            WHERE 
                                                p.idpengaduan = '$id_pengaduan'");
        
        if ($query_detail && mysqli_num_rows($query_detail) > 0) {
            $complaint_detail = mysqli_fetch_assoc($query_detail);
        } else {
            $error_message = "Aduan tidak ditemukan atau ID tidak valid.";
        }
    } else {
        $error_message = "Koneksi database gagal. Silakan coba lagi nanti.";
    }
} else {
    $error_message = "ID aduan tidak disediakan.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Aduan - SiCepu</title>
    <link rel="stylesheet" href="../../assets/css/dash_admin.css"> <!-- Sesuaikan path CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* General styling for the detail page */
        .detail-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            max-width: 900px;
            margin: 30px auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .detail-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .detail-header h2 {
            color: #333;
            font-size: 2em;
            margin-bottom: 10px;
        }
        .detail-item {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
        }
        .detail-item label {
            font-weight: 600;
            color: #555;
            margin-bottom: 5px;
            font-size: 0.95em;
        }
        .detail-item p {
            background-color: #f9f9f9;
            border: 1px solid #eee;
            padding: 10px 15px;
            border-radius: 5px;
            color: #333;
            font-size: 1em;
            word-wrap: break-word; /* Ensure long text wraps */
        }
        .detail-item.status-info p {
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending { background-color: #ffe0e0; color: #d32f2f; border-color: #d32f2f; } /* Light red */
        .status-diproses { background-color: #fff3e0; color: #f57c00; border-color: #f57c00; } /* Light orange */
        .status-selesai { background-color: #e8f5e9; color: #388e3c; border-color: #388e3c; } /* Light green */

        .detail-image {
            text-align: center;
            margin-top: 20px;
        }
        .detail-image img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .back-button-container {
            text-align: center;
            margin-top: 30px;
        }
        .back-button {
            display: inline-block;
            background-color: #6c757d; /* Grey */
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1em;
            transition: background-color 0.2s ease;
        }
        .back-button:hover {
            background-color: #5a6268;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .detail-container {
                margin: 20px 15px;
                padding: 20px;
            }
            .detail-header h2 {
                font-size: 1.8em;
            }
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
                    <li><a href="../dashboard.php" class="nav-link"><i class="fas fa-th-large"></i> Dashboard</a></li>
                    <li><a href="adu_tampil.php" class="nav-link active"><i class="fas fa-boxes"></i> Aduan Fasilitas</a></li>
                    <li><a href="../pengadu/pengadu_lihat.php" class="nav-link"><i class="fas fa-users"></i> Pengadu </a></li>
                    <li><a href="../pengguna/pengguna_lihat.php" class="nav-link"><i class="fas fa-users"></i> Pengguna </a></li>
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
                        <span class="status-dot <?php echo (isset($conn) && $conn ? 'status-connected' : 'status-disconnected'); ?>"></span>
                        <span>Database: <?php echo (isset($conn) && $conn ? 'Connected' : 'Disconnected'); ?></span>
                    </div>
                    <div class="time-location-info">
                        <span id="currentDateTime"></span> | <span>Bekasi Regency, West Java, Indonesia</span>
                    </div>
                </div>

                <div class="nav-icons">
                    <div class="icon-wrapper">
                        <a href="#" id="notificationBell">
                            <i class="fas fa-bell"></i>
                            <?php if (isset($new_complaints_count) && $new_complaints_count > 0): ?>
                                <span class="notification-badge"><?php echo $new_complaints_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="user-profile">
                        <img src="../../assets/img/admin_pfp.jpg" alt="User Avatar" class="avatar">
                        <span><?php echo htmlspecialchars($user_name); ?></span>
                        <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i></a>
                    </div>
                </div>
            </header>

            <section class="content-header">
                <h2>Detail Aduan</h2>
            </section>

            <section style="padding: 20px; background-color: #f0f2f5;">
                <div class="detail-container">
                    <?php if ($complaint_detail): ?>
                        <div class="detail-header">
                            <h2><?php echo htmlspecialchars($complaint_detail['judul']); ?></h2>
                        </div>
                        <div class="detail-item">
                            <label>ID Aduan:</label>
                            <p><?php echo htmlspecialchars($complaint_detail['idpengaduan']); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Pengadu:</label>
                            <p><?php echo htmlspecialchars($complaint_detail['nama_pengadu'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Jenis Aduan:</label>
                            <p><?php echo htmlspecialchars($complaint_detail['jenis_aduan'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Waktu Aduan:</label>
                            <p><?php echo htmlspecialchars($complaint_detail['waktu_aduan']); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Nomor Telepon:</label>
                            <p><?php echo htmlspecialchars($complaint_detail['notelp']); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Keterangan:</label>
                            <p><?php echo nl2br(htmlspecialchars($complaint_detail['keterangan'])); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Lokasi:</label>
                            <p><?php echo htmlspecialchars($complaint_detail['lokasi']); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Tanggapan:</label>
                            <p><?php echo nl2br(htmlspecialchars($complaint_detail['tanggapan'] ?? 'Belum ada tanggapan.')); ?></p>
                        </div>
                        <div class="detail-item status-info">
                            <label>Status:</label>
                            <p class="status-<?php echo strtolower($complaint_detail['status']); ?>"><?php echo htmlspecialchars($complaint_detail['status']); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Author:</label>
                            <p><?php echo htmlspecialchars($complaint_detail['author']); ?></p>
                        </div>
                        <?php if (!empty($complaint_detail['gambar'])): ?>
                            <div class="detail-image">
                                <label>Gambar:</label>
                                <img src="../../uploads/<?php echo htmlspecialchars($complaint_detail['gambar']); ?>" alt="Gambar Aduan" onerror="this.onerror=null;this.src='https://placehold.co/400x300/e0e0e0/555555?text=Gambar+Tidak+Tersedia';">
                            </div>
                        <?php else: ?>
                            <div class="detail-item">
                                <label>Gambar:</label>
                                <p>Tidak ada gambar terlampir.</p>
                            </div>
                        <?php endif; ?>

                        <div class="back-button-container">
                            <a href="javascript:history.back()" class="back-button">Kembali</a>
                        </div>
                    <?php else: ?>
                        <div class="detail-header">
                            <h2>Terjadi Kesalahan</h2>
                        </div>
                        <p style="text-align: center; color: red;"><?php echo htmlspecialchars($error_message); ?></p>
                        <div class="back-button-container">
                            <a href="javascript:history.back()" class="back-button">Kembali</a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <!-- Updated script sources to use CDNs for reliability -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
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

            // SweetAlert2 for notification bell click (if needed on this page)
            $('#notificationBell').on('click', function(e) {
                e.preventDefault();
                // You might want to fetch new_complaints_count dynamically or pass it from PHP
                let newComplaints = 0; // Placeholder
                let title = newComplaints > 0 ? 'Notifikasi Aduan Baru!' : 'Tidak Ada Aduan Baru';
                let text = newComplaints > 0 ? `Anda memiliki ${newComplaints} aduan baru yang masuk.` : 'Belum ada aduan baru yang perlu ditindaklanjuti.';
                let icon = newComplaints > 0 ? 'info' : 'success';

                Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    confirmButtonText: 'Oke'
                });
            });
        });
    </script>
</body>
</html>
