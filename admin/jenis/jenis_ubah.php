<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// IMPORTANT: Ensure this is the very first thing to protect the page.
// Assuming this file is located at admin/jenis/jenis_ubah.php
// Path to login.php from here is ../../login.php
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php");
    exit();
}

// Inisialisasi pesan dan tipe pesan dari sesi (untuk pop-up setelah redirect)
$message_from_session = '';
$message_type_from_session = '';

// Path to koneksi.php from admin/jenis/
require_once '../../inc/koneksi.php';

// Check database connection object ($conn)
if (!isset($conn) || $conn->connect_error) {
    die("Fatal Error: Database connection object (\$conn) is not available or connection failed. Please check ../../inc/koneksi.php.");
}

$admin_name = $_SESSION['nama']; // Get admin's name from session

// Inisialisasi variabel untuk menampung data jenis pengaduan yang akan diubah
$id_jenis_to_edit = null;
$jenis_data = []; // Renamed from $pengadu_data to $jenis_data

// Inisialisasi pesan dan tipe pesan dari sesi (untuk pop-up setelah redirect)
$message_from_session = '';
$message_type_from_session = '';

if (isset($_SESSION['form_message'])) {
    $message_from_session = $_SESSION['form_message'];
    $message_type_from_session = $_SESSION['form_message_type'];
    // Hapus pesan dari sesi agar tidak muncul lagi setelah refresh
    unset($_SESSION['form_message']);
    unset($_SESSION['form_message_type']);
}


// Handle GET request to load existing data for editing
if (isset($_GET['id'])) {
    $id_jenis_to_edit = intval($_GET['id']); // Ensure it's an integer

    // Fetch jenis_pengaduan data
    $stmt_jenis = $conn->prepare("SELECT idjenis, jenis FROM jenis_pengaduan WHERE idjenis = ?");
    if ($stmt_jenis === FALSE) {
        $_SESSION['form_message'] = "Prepare statement failed: " . $conn->error;
        $_SESSION['form_message_type'] = 'error';
        header("Location: jenis_lihat.php"); // Redirect back to list
        exit();
    }
    $stmt_jenis->bind_param("i", $id_jenis_to_edit); // 'i' for integer
    $stmt_jenis->execute();
    $result_jenis = $stmt_jenis->get_result();

    if ($result_jenis->num_rows === 1) {
        $jenis_data = $result_jenis->fetch_assoc();
    } else {
        $_SESSION['form_message'] = "Jenis pengaduan tidak ditemukan.";
        $_SESSION['form_message_type'] = 'error';
        header("Location: jenis_lihat.php"); // Redirect back to list
        exit();
    }
    $stmt_jenis->close();
}

// Handle POST request to update data
if (isset($_POST['ubah_jenis_submit'])) {
    $id_jenis_to_edit = intval($_POST['id_jenis']); // Get hidden ID
    $jenis_baru = $conn->real_escape_string($_POST['jenis']);

    // Basic validation
    if (empty($jenis_baru)) {
        $_SESSION['form_message'] = "Nama Jenis Pengaduan tidak boleh kosong.";
        $_SESSION['form_message_type'] = 'error';
        // Redirect back to this page with the ID to retain context
        header("Location: jenis_ubah.php?id=" . $id_jenis_to_edit);
        exit();
    }

    // Update jenis_pengaduan table
    $stmt_update = $conn->prepare("UPDATE jenis_pengaduan SET jenis = ? WHERE idjenis = ?");
    if ($stmt_update === FALSE) {
        $_SESSION['form_message'] = "Prepare statement failed: " . $conn->error;
        $_SESSION['form_message_type'] = 'error';
        header("Location: jenis_ubah.php?id=" . $id_jenis_to_edit);
        exit();
    }
    $stmt_update->bind_param("si", $jenis_baru, $id_jenis_to_edit); // 's' for string, 'i' for integer

    if ($stmt_update->execute()) {
        $_SESSION['form_message'] = "Jenis pengaduan berhasil diubah menjadi '" . htmlspecialchars($jenis_baru) . "'.";
        $_SESSION['form_message_type'] = 'success';
        header("Location: jenis_lihat.php"); // Redirect to list page after successful update
        exit();
    } else {
        $_SESSION['form_message'] = "Gagal mengubah jenis pengaduan: " . $stmt_update->error;
        $_SESSION['form_message_type'] = 'error';
        header("Location: jenis_ubah.php?id=" . $id_jenis_to_edit); // Stay on edit page with error
        exit();
    }
    $stmt_update->close();

}

// Close connection at the end of script
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Jenis Pengaduan - SiCepu</title>
    <link rel="stylesheet" href="../../assets/css/users.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        /* Custom styles for this form */
        .form-section {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin: 20px;
            max-width: 600px; /* Adjust width as needed */
            margin-left: auto;
            margin-right: auto;
        }
        .form-section h3 {
            margin-bottom: 25px;
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"] { /* Kept for general styling, though only 'text' is used */
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1em;
            box-sizing: border-box; /* Include padding in width */
        }
        .form-group input[type="text"]:focus,
        .form-group input[type="email"]:focus,
        .form-group input[type="password"]:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }

        /* New container for buttons */
        .form-actions {
            display: flex; /* Enable flexbox */
            gap: 10px; /* Space between buttons */
            justify-content: space-between; /* Distribute space evenly */
            margin-top: 20px; /* Add some space above the buttons */
        }

        /* Styles for both submit and back buttons within form-actions */
        .btn-submit,
        .btn-back {
            width: 50%; /* Each button takes half the width of the container, adjusting for gap */
            box-sizing: border-box; /* Include padding/border in width calculation */
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background-color 0.2s ease;
            text-align: center; /* Ensure text is centered for the anchor tag as well */
            text-decoration: none; /* Remove underline for anchor tag */
        }

        .btn-submit {
            background-color: #007bff;
            color: white;
        }
        .btn-submit:hover {
            background-color: #0056b3;
        }

        .btn-back {
            background-color: #6c757d; /* A neutral gray */
            color: white;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
        
        /* .message (SweetAlert2 will handle this) */
        .form-divider { /* Not strictly needed for a single field, but can be kept for consistency */
            text-align: center;
            margin: 30px 0;
            font-size: 1.1em;
            color: #888;
            position: relative;
        }
        .form-divider::before,
        .form-divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 40%;
            height: 1px;
            background-color: #eee;
        }
        .form-divider::before {
            left: 0;
        }
        .form-divider::after {
            right: 0;
        }
        .form-divider span {
            background-color: #fff;
            padding: 0 10px;
            z-index: 1;
            position: relative;
        }
        /* Style for centering H2 title */
        .content-header {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }
        .content-header h2 {
            margin: 0;
            padding: 0;
        }

        /* Styles for top-info-bar */
        .navbar .top-info-bar {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-grow: 1;
            padding-left: 20px;
            color: #333;
        }
        .top-info-bar .status-info,
        .top-info-bar .time-location-info {
            display: flex;
            align-items: center;
            font-size: 0.95em;
            color: #333;
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
        @media (max-width: 992px) {
            .navbar .top-info-bar {
                display: none;
            }
        }

        /* Media query for responsiveness: Stack buttons on small screens */
        @media (max-width: 480px) {
            .form-actions {
                flex-direction: column; /* Stack buttons vertically on small screens */
            }
            .btn-submit,
            .btn-back {
                width: 100%; /* Make them full width when stacked */
                margin-bottom: 10px; /* Add some space between stacked buttons */
            }
            .btn-back {
                margin-bottom: 0; /* No margin below the last button when stacked */
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
                    <li><a href="../aduan/adu_tampil.php" class="nav-link"><i class="fas fa-boxes"></i> Aduan Fasilitas</a></li>
                    <li><a href="#" class="nav-link active"><i class="fas fa-file-alt"></i> Jenis Aduan </a></li>
                    <li><a href="../pengadu/pengadu_lihat.php" class="nav-link"><i class="fas fa-users"></i> Pengadu </a></li>
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
                        <span id="currentDateTime"></span> 
                    </div>
                </div>

                <div class="nav-icons">
                    <a href="#"><i class="fas fa-bell"></i></a>
                    <a href="#"><i class="fas fa-comment"></i></a>
                    <div class="user-profile">
                        <img src="../../assets/img/admin_pfp.jpg" alt="User Avatar" class="avatar">
                        <span><?php echo htmlspecialchars($admin_name); ?></span>
                        <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i></a>
                    </div>
                </div>
            </header>

            <section class="content-header">
                <h2>Ubah Jenis Pengaduan</h2>
            </section>

            <section class="form-section">
                <form action="" method="POST">
                    <!-- Hidden input to pass idjenis for update -->
                    <input type="hidden" name="id_jenis" value="<?php echo htmlspecialchars($id_jenis_to_edit ?? ''); ?>">
                    
                    <h3>Detail Jenis Pengaduan</h3>
                    <div class="form-group">
                        <label for="jenis">Nama Jenis Pengaduan:</label>
                        <!-- Pre-fill input with fetched data -->
                        <input type="text" id="jenis" name="jenis" placeholder="Masukkan nama jenis pengaduan" value="<?php echo htmlspecialchars($jenis_data['jenis'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="ubah_jenis_submit" class="btn-submit">Ubah Jenis Pengaduan</button>
                        <a href="jenis_lihat.php" class="btn-back">Kembali</a>
                    </div>
                </form>
            </section>
        </main>
    </div>
    <script src="../../assets/js/jquery-1.10.2.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

    <script>
        // Fungsi untuk mengupdate waktu dan tanggal saat ini
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

            // Tangani pesan dari sesi menggunakan SweetAlert2
            const message = "<?php echo $message_from_session; ?>";
            const messageType = "<?php echo $message_type_from_session; ?>";

            if (message) {
                Swal.fire({
                    title: messageType === 'success' ? 'Berhasil!' : 'Gagal!',
                    text: message,
                    icon: messageType, // 'success' atau 'error'
                    confirmButtonText: 'Oke'
                }).then((result) => {
                    // If it's an error, the page will remain with the form and error message.
                    // If the message is not an error and the user confirms, we might still redirect if the PHP logic for success dictates.
                    // However, for success messages, the PHP already handles the redirect.
                    // This 'then' block for success messages might cause a double redirect if not careful,
                    // but since the PHP header("Location: jenis_lihat.php") is already there, it should be fine.
                    // For error messages, keeping the user on the page is good.
                    // So, no explicit redirect here for success, as PHP already does it.
                });
            }
        });
    </script>
</body>
</html>
