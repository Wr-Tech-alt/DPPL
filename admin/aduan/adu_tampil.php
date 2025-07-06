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

// Add this check immediately after including koneksi.php
if (!isset($conn) || $conn->connect_error) {
    die("Fatal Error: Database connection object (\$conn) is not available or connection failed. Please check ../../inc/koneksi.php.");
}

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

// Data for Notification Badge (from jenis_lihat.php logic)
$query_masuk_aduan = mysqli_query($conn, "SELECT COUNT(*) AS total FROM pengaduan WHERE status = 'Masuk'");
$data_masuk_aduan = mysqli_fetch_assoc($query_masuk_aduan);
$new_complaints_count = $data_masuk_aduan['total'];

// Close the connection after fetching data
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
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
    
    <link rel="stylesheet" href="../../assets/js/dataTables/dataTables.bootstrap.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
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

        /* Styles for action buttons - copied from jenis_lihat.php */
        .action-buttons {
            display: flex;
            gap: 5px; /* Space between buttons */
            justify-content: center; /* Center buttons within their cell */
        }
        .action-button {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            color: white;
            font-size: 0.9em;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }
        .action-button i {
            margin-right: 5px;
        }
        .action-button:hover {
            transform: translateY(-1px);
        }

        .action-button.edit {
            background-color: #007bff; /* Blue */
        }
        .action-button.edit:hover {
            background-color: #0056b3;
        }

        .action-button.detail {
            background-color: #17a2b8; /* Info Blue */
        }
        .action-button.detail:hover {
            background-color: #138496;
        }

        .action-button.delete {
            background-color: #dc3545; /* Red */
        }
        .action-button.delete:hover {
            background-color: #c82333;
        }

        /* Adjust table header for Action column */
        table thead th:last-child {
            text-align: center; /* Center "Action" header text */
        }
        table tbody td:last-child {
            text-align: center; /* Center buttons within cells */
        }

        /* Notification badge styling (from jenis_lihat.php) */
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
        /* Styles for top-info-bar */
        .navbar .top-info-bar { 
            display: flex;
            align-items: center;
            gap: 20px;
            flex-grow: 1; 
            padding-left: 20px; 
            color: #000; /* IMPORTANT MODIFICATION: Text color changed to black */
        }
        .top-info-bar .status-info,
        .top-info-bar .time-location-info {
            display: flex;
            align-items: center;
            font-size: 0.95em;
            color: #000; /* IMPORTANT MODIFICATION: Ensure inner text is also black */
        }
        .top-info-bar .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .top-info-bar .status-connected {
            background-color: #28a745; 
        }
        .top-info-bar .status-disconnected {
            background-color: #dc3545; 
        }
        /* Make sure it's responsive */
        @media (max-width: 992px) { 
            .navbar .top-info-bar {
                display: none; 
            }
        }

        /* --- Custom DataTables Pagination Styles --- (Copied from jenis_lihat.php) */
        div.dataTables_paginate {
            display: flex;
            justify-content: flex-end; /* Move pagination to the right */
            margin-top: 20px;
            margin-bottom: 20px;
            width: 100%;
        }
        div.dataTables_paginate ul.pagination {
            display: inline-flex; /* For horizontal arrangement of buttons */
            padding-left: 0;
            margin: 0; /* Remove default Bootstrap margin */
            border-radius: .25rem;
            overflow: hidden; /* Ensure rounded corners */
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); /* Soft shadow */
            list-style: none; /* Remove bullet list */
        }

        div.dataTables_paginate .paginate_button {
            padding: 8px 15px;
            border: 1px solid #dee2e6; /* Soft border */
            background-color: #fff;
            color: #495057; /* Dark gray text */
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
            min-width: 40px;
            text-align: center;
            line-height: 1.42857143;
            /* Ensure no double left border for subsequent buttons */
            border-left: none; 
        }
        /* Left border for the first button */
        div.dataTables_paginate .paginate_button:first-child {
            border-left: 1px solid #dee2e6; 
        }


        div.dataTables_paginate .paginate_button:hover:not(.current):not(.disabled) {
            background-color: #e9ecef; /* Light gray on hover */
            color: #343a40; /* Darker text on hover */
            border-color: #c0c0c0;
        }

        div.dataTables_paginate .paginate_button.current {
            background-color: #007bff; /* Primary blue for active */
            color: white;
            border-color: #007bff;
            cursor: default;
        }

        div.dataTables_paginate .paginate_button.current:hover {
            background-color: #0056b3; /* Darker blue on hover for active button */
            border-color: #0056b3;
        }

        div.dataTables_paginate .paginate_button.disabled {
            background-color: #f8f9fa; /* Very light gray for disabled */
            color: #adb5bd; /* Faded gray text */
            border-color: #dee2e2; 
            cursor: not-allowed;
            opacity: 0.8;
        }
        div.dataTables_paginate .paginate_button.disabled:hover {
            background-color: #f8f9fa; /* No hover change for disabled */
            color: #adb5bd;
            border-color: #dee2e2; 
        }

        /* Arrow icons for Previous/Next */
        div.dataTables_paginate .paginate_button.previous,
        div.dataTables_paginate .paginate_button.next {
            text-indent: -9999px; /* Hide text */
            position: relative;
            overflow: hidden;
            min-width: 38px; /* Adjust minimum width */
        }
        div.dataTables_paginate .paginate_button.previous::before {
            content: "\f053"; /* fa-chevron-circle-left */
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-indent: 0;
            color: #495057; /* Icon color */
        }
        div.dataTables_paginate .paginate_button.next::before {
            content: "\f054"; /* fa-chevron-circle-right */
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-indent: 0;
            color: #495057; /* Icon color */
        }
        div.dataTables_paginate .paginate_button.current.previous::before,
        div.dataTables_paginate .paginate_button.current.next::before {
            color: white; /* White icon for active button */
        }
        div.dataTables_paginate .paginate_button.disabled.previous::before,
        div.dataTables_paginate .paginate_button.disabled.next::before {
            color: #adb5bd; /* Disabled icon color */
        }
        
        /* Border radius for pagination ends */
        div.dataTables_paginate .paginate_button:first-child {
            border-top-left-radius: .25rem;
            border-bottom-left-radius: .25rem;
        }
        div.dataTables_paginate .paginate_button:last-child {
            border-top-right-radius: .25rem;
            border-bottom-right-radius: .25rem;
        }
        /* MODIFICATION: Ensure borders between buttons are clearly defined */
        div.dataTables_paginate .paginate_button:not(:first-child) {
            border-left: 1px solid #dee2e6; /* Add left border for subsequent buttons */
        }

        /* Styles for btn-primary (Add New Button) from jenis_lihat.php */
        .btn-primary {
            background-color: #007bff;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none; /* For anchor tags styled as buttons */
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.2s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }

        /* Filter bar styling from jenis_lihat.php */
        .filter-bar {
            display: flex;
            /* Removed justify-content: space-between; to allow search input to expand */
            align-items: center;
            margin-bottom: 20px;
            gap: 10px;
        }
        .btn-filter {
            background-color: #f8f9fa; /* Light background */
            color: #333;
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
        .btn-filter:hover {
            background-color: #e2e6ea;
            border-color: #c6c6c6;
        }
       .search-customer {
            position: relative;
            flex-grow: 1;
            margin-right: auto;
        }
        .search-customer input {
            width: 100%;
            padding: 8px 12px 8px 35px;
            border-radius: 6px;
            box-sizing: border-box;
        }
        .search-customer input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }
        .search-customer i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            pointer-events: none;
        }

        /* Table container for DataTables responsiveness */
        .table-container {
            overflow-x: auto; /* Enable horizontal scrolling on small screens */
            width: 100%;
        }

        /* Status badge styling */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: 600;
            color: white;
            text-align: center;
        }

        .status-badge.masuk {
            background-color: #007bff; /* Blue */
        }
        .status-badge.diproses {
            background-color: #ffc107; /* Yellow */
            color: #333; /* Dark text for yellow background */
        }
        .status-badge.selesai {
            background-color: #28a745; /* Green */
        }
        .status-badge.pending { /* Assuming 'Pending' might be a status */
            background-color: #6c757d; /* Gray */
        }
        /* Pastikan section.content-header memusatkan kontennya */

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
    <li><a href="../../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Sign Out</a></li> </ul>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <!-- Header disalin dari dashboard_admin.php -->
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
                            <?php if ($new_complaints_count > 0): ?>
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

            <!-- Konten Utama Halaman Aduan -->
            <section class="content-header">
                <h2 style="text-align: center;">Manajemen Aduan Fasilitas</h2>
                <div class="header-actions">
                    <!-- No 'Tambah Aduan' button here, as it's for Jenis Aduan. If you need one, add it. -->
                </div>
            </section>

            <div class="content-container">
                <!-- Filter Bar - Replicating jenis_lihat.php style -->
                <div class="filter-bar">
                    <div class="search-customer">
                        <i class="fas fa-search"></i>
                        <input type="text" id="customSearchInput" placeholder="Cari aduan...">
                    </div>
                    <!-- Moved the status filter here to be part of the filter-bar if desired, or keep it separate -->
                    <select class="filter-select" id="statusFilter">
                        <option value="">Semua Status</option>
                        <option value="Masuk">Masuk</option>
                        <option value="Diproses">Diproses</option>
                        <option value="Selesai">Selesai</option>
                        <option value="Pending">Pending</option> <!-- Added Pending as seen in screenshot -->
                    </select>
                </div>

                <section class="customer-table-section">
                    <div class="table-container">
                        <table id="aduanTable" class="display"> <!-- Changed ID to aduanTable -->
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
                                        <td><?php echo htmlspecialchars($aduan['waktu_aduan']); ?></td> <!-- Changed to raw value for DataTables sorting -->
                                        <td>
                                            <span class="status-badge <?php echo strtolower(htmlspecialchars($aduan['status'])); ?>">
                                                <?php echo htmlspecialchars($aduan['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="adu_detail.php?id=<?php echo $aduan['idpengaduan']; ?>" class="action-button detail" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                                <a href="adu_hapus.php?id=<?php echo $aduan['idpengaduan']; ?>" class="action-button delete" title="Hapus Aduan">
                                                    <i class="fas fa-trash-alt"></i> Hapus
                                                </a>
                                            </div>
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
                    </div>
                </section>
                <!-- Pagination will be handled by DataTables automatically -->
            </div>

        </main>
    </div>
    <script src="../../assets/js/jquery-1.10.2.js"></script>
    <script src="../../assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="../../assets/js/dataTables/dataTables.bootstrap.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

    <script>
        // Fungsi untuk mengupdate waktu dan tanggal saat ini
        function updateDateTime() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            document.getElementById('currentDateTime').innerText = now.toLocaleDateString('id-ID', options);
        }

        $(document).ready(function() {
            // Initial update for date and time
            updateDateTime();
            // Update every second
            setInterval(updateDateTime, 1000);

            // Inisialisasi DataTables
            const table = $('#aduanTable').DataTable({
                "paging": true,      
                "ordering": true,    
                "info": true,        
                "searching": true,   
                "lengthChange": true,
                "dom": 'rtip', // This hides the default search and lengthChange, but keeps table, info, paging
                "order": [[ 3, "desc" ]], // Order by 'Waktu Kirim' (index 3) in descending order
                "columnDefs": [
                    { "type": "date", "targets": 3 } // Specify column 3 (Waktu Kirim) as date type for proper sorting
                ]
            });

            // Hubungkan input search kustom dengan DataTables
            $('#customSearchInput').on('keyup change', function() {
                table.search(this.value).draw(); // Search across all columns
            });

            // Hubungkan filter status dengan DataTables
            $('#statusFilter').on('change', function() {
                const status = $(this).val();
                if (status) {
                    table.column(4).search(status).draw(); // Search 'Status' column (index 4)
                } else {
                    table.column(4).search('').draw(); // Clear search if 'Semua Status' is selected
                }
            });

            // Sembunyikan search filter bawaan DataTables (karena kita pakai yang kustom)
            $('.dataTables_filter').hide();
            // Sembunyikan length change bawaan DataTables (jika tidak ingin pakai)
            $('.dataTables_length').hide();

            // Notifikasi Aduan Masuk (SweetAlert2)
            $('#notificationBell').on('click', function(e) {
                e.preventDefault();
                let newComplaints = <?php echo $new_complaints_count; ?>;
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

            // Konfirmasi hapus dengan SweetAlert2
            $('#aduanTable').on('click', '.action-button.delete', function(e) {
                e.preventDefault(); // Mencegah langsung hapus
                const deleteUrl = $(this).attr('href'); // Dapatkan URL hapus dari href

                Swal.fire({
                    title: 'Apakah Anda Yakin?',
                    text: "Data aduan ini akan dihapus permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = deleteUrl; 
                    }
                });
            });
        });
    </script>
</body>
</html>
