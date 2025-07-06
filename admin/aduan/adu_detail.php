<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['Admin', 'Petugas', 'Pengadu'])) {
    header("Location: ../login.php");
    exit();
}

include "../../inc/koneksi.php";
include "../../inc/kirim_email.php"; // Tambahan: untuk kirim email

$user_name = $_SESSION['nama'];
$user_role = $_SESSION['role'];

$complaint_detail = null;
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if ($user_role === 'Admin' && isset($_POST['idpengaduan'])) {
        $id_pengaduan_to_update = mysqli_real_escape_string($conn, $_POST['idpengaduan']);

        $current_status_query = mysqli_query($conn, "SELECT status FROM pengaduan WHERE idpengaduan = '$id_pengaduan_to_update'");
        $current_status_row = mysqli_fetch_assoc($current_status_query);
        $current_status = $current_status_row['status'];

        if ($current_status === 'Pending') {
            $update_query = mysqli_query($conn, "UPDATE pengaduan SET status = 'Diproses' WHERE idpengaduan = '$id_pengaduan_to_update'");
            $_SESSION['update_message'] = ['type' => 'success', 'text' => 'Status aduan berhasil diperbarui menjadi "Diproses".'];

        } elseif ($current_status === 'Diproses') {
            $update_query = mysqli_query($conn, "UPDATE pengaduan SET status = 'Selesai' WHERE idpengaduan = '$id_pengaduan_to_update'");
            if ($update_query) {
                // Kirim email notifikasi ke pengguna
                $query_email = mysqli_query($conn, "SELECT p.judul, u.email, u.nama FROM pengaduan p JOIN pengguna u ON p.iduser = u.iduser WHERE p.idpengaduan = '$id_pengaduan_to_update'");
                if ($row = mysqli_fetch_assoc($query_email)) {
                    kirimEmailPengaduan($row['email'], $row['nama'], $row['judul'], 'Selesai');
                }

                $_SESSION['update_message'] = ['type' => 'success', 'text' => 'Status aduan berhasil diperbarui menjadi "Selesai". Email notifikasi telah dikirim.'];
            } else {
                $_SESSION['update_message'] = ['type' => 'error', 'text' => 'Gagal memperbarui status aduan: ' . mysqli_error($conn)];
            }
        } else {
            $_SESSION['update_message'] = ['type' => 'error', 'text' => 'Status aduan tidak dapat diperbarui karena bukan "Pending" atau "Diproses".'];
        }
    } else {
        $_SESSION['update_message'] = ['type' => 'error', 'text' => 'Anda tidak memiliki izin untuk melakukan tindakan ini atau ID aduan tidak valid.'];
    }
    header("Location: adu_detail.php?id=" . $id_pengaduan_to_update);
    exit();
}


// Periksa apakah idpengaduan disediakan di URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_pengaduan = mysqli_real_escape_string($conn, $_GET['id']);

    if ($conn) {
        // Kueri untuk mengambil semua detail untuk ID aduan spesifik
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
                                                u.nama AS nama_pengadu,
                                                j.jenis AS jenis_aduan
                                            FROM 
                                                pengaduan p
                                            LEFT JOIN 
                                                pengguna u ON p.iduser = u.iduser
                                            LEFT JOIN 
                                                jenis_pengaduan j ON p.idjenis = j.idjenis
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
            flex-direction: column; /* Default to column, will change for larger screens */
            gap: 20px;
        }
        .detail-header {
            text-align: center;
            margin-bottom: 20px;
            position: relative; /* Untuk memposisikan tombol update */
        }
        .detail-header h2 {
            color: #333;
            font-size: 2em;
            margin-bottom: 10px;
        }
        /* New styles for image at the top */
        .detail-image-top {
            text-align: center;
            margin-bottom: 20px; /* Spasi di bawah gambar */
        }
        .detail-image-top label {
            font-weight: 600;
            color: #555;
            margin-bottom: 10px;
            font-size: 1em;
            display: block; /* Membuat label mengambil lebar penuh */
        }
        .detail-image-top img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        /* New styles for two-column layout */
        .detail-content-columns {
            display: flex;
            flex-wrap: wrap; /* Memungkinkan kolom untuk membungkus pada layar yang lebih kecil */
            gap: 30px; /* Spasi antar kolom */
        }

        .detail-left-column,
        .detail-right-column {
            flex: 1; /* Setiap kolom mengambil ruang yang sama */
            min-width: 300px; /* Lebar minimum sebelum membungkus */
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
            word-wrap: break-word; /* Memastikan teks panjang membungkus */
        }
        .detail-item.status-info p {
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending { background-color: #ffe0e0; color: #d32f2f; border-color: #d32f2f; } /* Merah muda */
        .status-diproses { background-color: #fff3e0; color: #f57c00; border-color: #f57c00; } /* Oranye muda */
        .status-selesai { background-color: #e8f5e9; color: #388e3c; border-color: #388e3c; } /* Hijau muda */

        /* Update Status Button */
        .update-status-button {
            background-color: #007bff; /* Biru */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            transition: background-color 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px; /* Spasi di bawah header */
            float: right; /* Posisikan ke kanan */
        }
        .update-status-button:hover {
            background-color: #0056b3;
        }
        .update-status-button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .back-button-container {
            text-align: center;
            margin-top: 30px;
        }
        .back-button {
            display: inline-block;
            background-color: #6c757d; /* Abu-abu */
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
            .detail-content-columns {
                flex-direction: column; /* Tumpuk kolom pada layar kecil */
                gap: 0; /* Hapus spasi saat ditumpuk */
            }
            .update-status-button {
                float: none; /* Hapus float pada layar kecil */
                width: 100%; /* Tombol lebar penuh */
                justify-content: center; /* Pusatkan konten */
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
                    <li><a href="../../dashboard/dashboard_admin.php" class="nav-link"><i class="fas fa-th-large"></i> Dashboard</a></li>
                    <li><a href="#" class="nav-link active"><i class="fas fa-boxes"></i> Aduan Fasilitas</a></li>
                    <li><a href="../jenis/jenis_lihat.php" class="nav-link"><i class="fas fa-file-alt"></i> Jenis Aduan </a></li>
                    <li><a href="../pengadu/pengadu_lihat.php" class="nav-link"><i class="fas fa-users"></i> Pengadu </a></li>
                </ul>
                <div class="nav-section-title">SETTINGS</div>
                <ul>
                    <li><a href="#" class="nav-link"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="../../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Sign Out</a></li> </ul>
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
                        <span id="currentDateTime"></span> 
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
                <div class="" style="align-items: center; justify-content: center;">
                    <h2 style="text-align: center;">Detail Aduan</h2>
                </div>
                <?php 
                // Tampilkan tombol update hanya jika peran adalah Admin dan status adalah Pending atau Diproses
                if ($user_role === 'Admin' && $complaint_detail && 
                    ($complaint_detail['status'] === 'Pending' || $complaint_detail['status'] === 'Diproses')): 
                    
                    $button_text = '';
                    $confirm_text = '';
                    if ($complaint_detail['status'] === 'Pending') {
                        $button_text = 'Update ke "Diproses"';
                        $confirm_text = "Apakah Anda yakin ingin mengubah status aduan ini menjadi 'Diproses'?";
                    } elseif ($complaint_detail['status'] === 'Diproses') {
                        $button_text = 'Update ke "Selesai"';
                        $confirm_text = "Apakah Anda yakin ingin mengubah status aduan ini menjadi 'Selesai'?";
                    }
                ?>
                    <form id="updateStatusForm" method="POST" action="">
                        <input type="hidden" name="idpengaduan" value="<?php echo htmlspecialchars($complaint_detail['idpengaduan']); ?>">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="current_status" value="<?php echo htmlspecialchars($complaint_detail['status']); ?>">
                        <button type="submit" class="update-status-button" data-confirm-text="<?php echo htmlspecialchars($confirm_text); ?>">
                            <i class="fas fa-sync-alt"></i> <?php echo $button_text; ?>
                        </button>
                    </form>
                <?php endif; ?>
            </section>

            <section style="padding: 20px; background-color: #f0f2f5;">
                <div class="detail-container">
                    <?php if ($complaint_detail): ?>
                        <div class="detail-header">
                            <h2><?php echo htmlspecialchars($complaint_detail['judul']); ?></h2>
                        </div>

                        <?php if (!empty($complaint_detail['gambar'])): ?>
                            <div class="detail-image-top">
                                <label>Gambar Aduan:</label>
                                <img src="../../uploads/<?php echo htmlspecialchars($complaint_detail['gambar']); ?>" alt="Gambar Aduan" onerror="this.onerror=null;this.src='https://placehold.co/600x400/e0e0e0/555555?text=Gambar+Tidak+Tersedia';">
                            </div>
                        <?php else: ?>
                            <div class="detail-item detail-image-top">
                                <label>Gambar Aduan:</label>
                                <p>Tidak ada gambar terlampir.</p>
                            </div>
                        <?php endif; ?>

                        <div class="detail-content-columns">
                            <div class="detail-left-column">
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
                            </div>
                            <div class="detail-right-column">
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
                            </div>
                        </div>

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
                // Anda mungkin ingin mengambil new_complaints_count secara dinamis atau meneruskannya dari PHP
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

            // SweetAlert2 for status update confirmation
            $('#updateStatusForm').on('submit', function(e) {
                e.preventDefault(); // Mencegah pengiriman formulir default

                const form = this;
                const confirmText = $(this).find('.update-status-button').data('confirm-text');

                Swal.fire({
                    title: 'Konfirmasi Perubahan Status',
                    text: confirmText,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Lanjutkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit(); // Kirim formulir jika dikonfirmasi
                    }
                });
            });

            // Tampilkan pesan update status jika ada (dari PHP session)
            if (typeof Swal !== 'undefined' && <?php echo isset($_SESSION['update_message']) ? 'true' : 'false'; ?>) {
                const message = <?php echo json_encode($_SESSION['update_message'] ?? null); ?>;
                if (message) {
                    Swal.fire({
                        title: message.type === 'success' ? 'Berhasil!' : 'Gagal!',
                        text: message.text,
                        icon: message.type,
                        confirmButtonText: 'Oke'
                    });
                    <?php unset($_SESSION['update_message']); // Hapus pesan setelah ditampilkan ?>
                }
            }
        });
    </script>
</body>
</html>
