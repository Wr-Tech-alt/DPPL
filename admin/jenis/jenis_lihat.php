<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

$message_from_session = '';
$message_type_from_session = '';

if (isset($_SESSION['form_message'])) {
    $message_from_session = $_SESSION['form_message'];
    $message_type_from_session = $_SESSION['form_message_type'];
    // Hapus pesan dari sesi agar tidak muncul lagi setelah refresh
    unset($_SESSION['form_message']);
    unset($_SESSION['form_message_type']);
}

// IMPORTANT: Ensure this is the very first thing to protect the page.
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php");
    exit();
}

require_once '../../inc/koneksi.php'; // Correct path to koneksi.php from admin/pengadu/

// Add this check immediately after including koneksi.php
if (!isset($conn) || $conn->connect_error) {
    die("Fatal Error: Database connection object (\$conn) is not available or connection failed. Please check ../../inc/koneksi.php.");
}

$admin_name = $_SESSION['nama']; // Get admin's name from session

// --- Data untuk Notifikasi (Keep this as it's part of the dashboard features) ---
$query_masuk_aduan = mysqli_query($conn, "SELECT COUNT(*) AS total FROM pengaduan WHERE status = 'Masuk'");
$data_masuk_aduan = mysqli_fetch_assoc($query_masuk_aduan);
$new_complaints_count = $data_masuk_aduan['total'];


// --- PHP Logic Change Starts Here ---

// Fetch data from the 'jenis_pengaduan' table
$jenis_pengaduan_data = []; // Renamed variable to avoid conflict with 'users' in HTML template's loop
$sql_jenis_pengaduan = "SELECT idjenis, jenis FROM jenis_pengaduan ORDER BY idjenis ASC";
$result_jenis_pengaduan = $conn->query($sql_jenis_pengaduan);

if ($result_jenis_pengaduan) {
    if ($result_jenis_pengaduan->num_rows > 0) {
        while ($row_jenis = $result_jenis_pengaduan->fetch_assoc()) {
            $jenis_pengaduan_data[] = $row_jenis;
        }
    }
} else {
    echo "Error fetching jenis pengaduan: " . $conn->error;
}

// --- PHP Logic Change Ends Here ---


// Close the connection after fetching data
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Jenis Pengaduan - SiCepu</title> <link rel="stylesheet" href="../../assets/css/users.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../../assets/js/dataTables/dataTables.bootstrap.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>    
        .content-header {
            display: flex; /* Menggunakan flexbox untuk penataan */
            flex-direction: column; /* Mengatur item dalam kolom */
            align-items: center; /* Memusatkan item secara horizontal */
            gap: 15px; /* Memberi sedikit jarak antar elemen */
        }

        .header-title h2 {
            text-align: center; /* Pastikan judul tetap di tengah */
            margin: 0; /* Hapus margin default jika ada */
        }

        .header-actions {
            /* Tidak perlu properti text-align di sini karena align-items di parent sudah mengatur */
            /* Jika ingin mengatur posisi tombol secara spesifik, bisa diatur di sini */
            text-align: right; /* Pastikan judul tetap di tengah */
            margin: 0; /* Hapus margin default jika ada */
        }

        /* Styles for action buttons */
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

        /* Notification badge styling (from dashboard_admin.php) */
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

        /* --- Custom DataTables Pagination Styles --- */
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
                    <li><a href="../aduan/adu_tampil.php" class="nav-link"><i class="fas fa-boxes"></i> Aduan Fasilitas</a></li>
                    <li><a href="#" class="nav-link active"><i class="fas fa-file-alt"></i> Jenis Aduan </a></li>
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
                            <?php if ($new_complaints_count > 0): ?>
                                <span class="notification-badge"><?php echo $new_complaints_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="user-profile">
                        <img src="../../assets/img/admin_pfp.jpg" alt="User Avatar" class="avatar"> 
                        <span><?php echo htmlspecialchars($admin_name); ?></span>
                        <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i></a> 
                    </div>
                </div>
            </header>

            <section class="content-header">
                <div class="header-title">
                    <h2>Manajemen Jenis Pengaduan</h2>
                </div>
            </section>
            <div class="header-actions">
                <a href="jenis_tambah.php" class="btn-primary"><i class="fas fa-plus"></i> Tambah Jenis</a>
            </div>

            <section class="customer-table-section">
                <div class="filter-bar">
                    <div class="search-customer">
                        <i class="fas fa-search"></i>
                        <input type="text" id="customSearchInput" placeholder="Cari jenis pengaduan..."> </div>
                </div>

                <div class="table-container">
                    <table id="jenisPengaduanTable"> 
                        <thead>
                            <tr>
                                <th>ID Jenis</th>
                                <th>Jenis Pengaduan</th>
                                <th>Action</th> 
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($jenis_pengaduan_data)): ?>
                                <?php foreach ($jenis_pengaduan_data as $jenis_p): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($jenis_p['idjenis']); ?></td>
                                        <td><?php echo htmlspecialchars($jenis_p['jenis']); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="jenis_ubah.php?id=<?php echo htmlspecialchars($jenis_p['idjenis']); ?>" class="action-button edit" title="Ubah Jenis Pengaduan">
                                                    <i class="fas fa-edit"></i> Ubah
                                                </a>
                                                <a href="jenis_hapus.php?id=<?php echo htmlspecialchars($jenis_p['idjenis']); ?>" class="action-button delete" title="Hapus Jenis Pengaduan">
                                                    <i class="fas fa-trash-alt"></i> Hapus
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align: center;">Tidak ada data jenis pengaduan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </section>
        </main>
    </div>
    <script src="../../assets/js/jquery-1.10.2.js"></script>
    <script src="../../assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="../../assets/js/dataTables/dataTables.bootstrap.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

    <script>
        // MODIFIKASI: Fungsi updateDateTime dipindahkan ke sini
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

            const message_from_session = <?php echo json_encode($message_from_session); ?>;
            const message_type_from_session = <?php echo json_encode($message_type_from_session); ?>;


            // Inisialisasi DataTables
            // Changed table ID to match the HTML
            const table = $('#jenisPengaduanTable').DataTable({
                "paging": true,      
                "ordering": true,    
                "info": true,        
                "searching": true,   
                "lengthChange": true,
                "dom": 'rtip' 
            });

            const message = message_from_session;
            const messageType = message_type_from_session;

            if (message) {
                Swal.fire({
                    title: messageType === 'success' ? 'Berhasil!' : 'Gagal!',
                    text: message,
                    icon: messageType, // 'success' atau 'error'
                    confirmButtonText: 'Oke'
                });
            }

            // Hubungkan input search kustom dengan DataTables
            $('#customSearchInput').on('keyup change', function() {
                // MODIFIKASI: Mencari berdasarkan kolom "Jenis Pengaduan" (kolom indeks 1)
                table.column(1).search(this.value).draw(); // Assuming 'jenis' is at index 1
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
            // Pastikan tidak ada onclick="return confirm(...)" di HTML untuk tombol hapus
            // Target the delete buttons within the new table
            $('#jenisPengaduanTable').on('click', '.action-button.delete', function(e) {
                e.preventDefault(); // Mencegah langsung hapus
                const deleteUrl = $(this).attr('href'); // Dapatkan URL hapus dari href

                Swal.fire({
                    title: 'Apakah Anda Yakin?',
                    text: "Data jenis pengaduan ini akan dihapus permanen!",
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