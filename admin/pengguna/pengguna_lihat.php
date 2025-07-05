<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// IMPORTANT: Ensure this is the very first thing to protect the page.
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php"); 
    exit();
}

require_once '../../inc/koneksi.php'; 

// Add this check immediately after including koneksi.php
if (!isset($conn) || $conn->connect_error) {
    die("Fatal Error: Database connection object (\$conn) is not available or connection failed. Please check ../../inc/koneksi.php.");
}

$admin_name = $_SESSION['nama']; // Get admin's name from session

// --- Data untuk Notifikasi ---
$query_masuk_aduan = mysqli_query($conn, "SELECT COUNT(*) AS total FROM pengaduan WHERE status = 'Masuk'");
$data_masuk_aduan = mysqli_fetch_assoc($query_masuk_aduan);
$new_complaints_count = $data_masuk_aduan['total'];


// Fetch users from the 'pengguna' table (ALL roles)
$users = [];
$sql = "SELECT iduser, nama, email, Role FROM pengguna ORDER BY nama ASC"; 
$result = $conn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
} else {
    echo "Error fetching users: " . $conn->error;
}

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
    <title>Manajemen Pengguna - SiCepu</title>
    <link rel="stylesheet" href="../../assets/css/users.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../../assets/js/dataTables/dataTables.bootstrap.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
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
            color: #000; /* Warna gelap agar terbaca jelas */
        }
        .top-info-bar .status-info,
        .top-info-bar .time-location-info {
            display: flex;
            align-items: center;
            font-size: 0.95em;
            color: #000; /* Pastikan teks di dalamnya juga gelap */
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
        div.dataTables_paginate ul.pagination {
            margin: 20px 0; 
            display: flex; 
            padding-left: 0;
            list-style: none;
            border-radius: .25rem; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
            overflow: hidden; 
            width: fit-content; 
            float: right; 
        }

        div.dataTables_paginate .paginate_button {
            padding: 8px 15px;
            border: 1px solid #dee2e6; 
            background-color: #fff;
            color: #495057; 
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
            min-width: 40px;
            text-align: center;
            line-height: 1.42857143;
            border-left: none; 
        }
        div.dataTables_paginate .paginate_button:first-child {
            border-left: 1px solid #dee2e6; 
        }


        div.dataTables_paginate .paginate_button:hover:not(.current):not(.disabled) {
            background-color: #e9ecef; 
            color: #343a40; 
            border-color: #c0c0c0;
        }

        div.dataTables_paginate .paginate_button.current {
            background-color: #007bff; 
            color: white;
            border-color: #007bff;
            cursor: default;
        }

        div.dataTables_paginate .paginate_button.current:hover {
            background-color: #0056b3; 
            border-color: #0056b3;
        }

        div.dataTables_paginate .paginate_button.disabled {
            background-color: #f8f9fa; 
            color: #adb5bd; 
            border-color: #dee2e2; 
            cursor: not-allowed;
            opacity: 0.8;
        }
        div.dataTables_paginate .paginate_button.disabled:hover {
            background-color: #f8f9fa; 
            color: #adb5bd;
            border-color: #dee2e2; 
        }

        /* Ikon panah untuk Previous/Next */
        div.dataTables_paginate .paginate_button.previous,
        div.dataTables_paginate .paginate_button.next {
            text-indent: -9999px; 
            position: relative;
            overflow: hidden;
            min-width: 38px; 
        }
        div.dataTables_paginate .paginate_button.previous::before {
            content: "\f053"; 
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-indent: 0;
            color: #495057; 
        }
        div.dataTables_paginate .paginate_button.next::before {
            content: "\f054"; 
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-indent: 0;
            color: #495057; 
        }
        div.dataTables_paginate .paginate_button.current.previous::before,
        div.dataTables_paginate .paginate_button.current.next::before {
            color: white; 
        }
        div.dataTables_paginate .paginate_button.disabled.previous::before,
        div.dataTables_paginate .paginate_button.disabled.next::before {
            color: #adb5bd; 
        }
        
        /* Border radius untuk ujung paginasi */
        div.dataTables_paginate .paginate_button:first-child {
            border-top-left-radius: .25rem;
            border-bottom-left-radius: .25rem;
        }
        div.dataTables_paginate .paginate_button:last-child {
            border-top-right-radius: .25rem;
            border-bottom-right-radius: .25rem;
        }
        div.dataTables_paginate .paginate_button:not(:first-child) {
            border-left: 1px solid #dee2e6; 
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
                    <li><a href="#" class="nav-link"><i class="fas fa-boxes"></i> Aduan Fasilitas</a></li>
                    <li><a href="../pengadu/pengadu_lihat.php" class="nav-link"><i class="fas fa-users"></i> Pengadu</a></li>
                    <li><a href="pengguna_lihat.php" class="nav-link active"><i class="fas fa-users"></i> Pengguna</a></li>
                </ul>
                <div class="nav-section-title">SETTINGS</div>
                <ul>
                    <li><a href="#" class="nav-link"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="#" class="nav-link"><i class="fas fa-question-circle"></i> Help</a></li>
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
                    <div class="user-profile">
                        <img src="../../assets/img/admin_pfp.jpg" alt="User Avatar" class="avatar"> 
                        <span><?php echo htmlspecialchars($admin_name); ?></span>
                        <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i></a> 
                    </div>
                </div>
            </header>

            <section class="content-header">
                <div class="customer-tabs">
                    <button class="tab-button active">Semua Pengguna</button> </div>
                <div class="header-actions">
                    <button class="btn-secondary"><i class="fas fa-download"></i>Ekspor</button>
                    <a href="pengguna_tambah.php" class="btn-primary"><i class="fas fa-plus"></i>Tambah Pengguna</a> 
                </div>
            </section>

            <section class="customer-table-section">
                <div class="filter-bar">
                    <button class="btn-filter"><i class="fas fa-filter"></i> Filter</button>
                    <div class="search-customer">
                        <i class="fas fa-search"></i>
                        <input type="text" id="customSearchInput" placeholder="Cari nama pengguna..."> 
                    </div>
                </div>

                <div class="table-container">
                    <table id="penggunaTable"> 
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Action</th> 
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <img src="../../assets/img/userav.jpg" alt="Avatar" class="table-avatar"> 
                                            <?php echo htmlspecialchars($user['nama']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['Role']); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="pengguna_ubah.php?id=<?php echo htmlspecialchars($user['iduser']); ?>" class="action-button edit" title="Ubah Pengguna">
                                                    <i class="fas fa-edit"></i> Ubah
                                                </a>
                                                <a href="pengguna_detail.php?id=<?php echo htmlspecialchars($user['iduser']); ?>" class="action-button detail" title="Detail Pengguna">
                                                    <i class="fas fa-info-circle"></i> Detail
                                                </a>
                                                <a href="pengguna_hapus.php?id=<?php echo htmlspecialchars($user['iduser']); ?>" class="action-button delete" title="Hapus Pengguna">
                                                    <i class="fas fa-trash-alt"></i> Hapus
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center;">No users found in the database.</td>
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
            const table = $('#penggunaTable').DataTable({
                "paging": true,      
                "ordering": true,    
                "info": true,        
                "searching": true,   
                "lengthChange": true,
                "dom": 'rtip' 
            });

            // Hubungkan input search kustom dengan DataTables
            $('#customSearchInput').on('keyup change', function() {
                table.search(this.value).draw(); 
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
            $('.action-button.delete').on('click', function(e) {
                e.preventDefault(); 
                const deleteUrl = $(this).attr('href'); 

                Swal.fire({
                    title: 'Apakah Anda Yakin?',
                    text: "Data pengguna ini akan dihapus permanen!",
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