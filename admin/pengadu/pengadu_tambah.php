<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// IMPORTANT: Ensure this is the very first thing to protect the page.
// Current file is admin/pengadu/pengadu_tambah.php
// Path to login.php from here is ../../login.php
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php"); 
    exit();
}

// Path to koneksi.php from admin/pengadu/
require_once '../../inc/koneksi.php'; 

// Check database connection object ($conn)
if (!isset($conn) || $conn->connect_error) {
    die("Fatal Error: Database connection object (\$conn) is not available or connection failed. Please check ../../inc/koneksi.php.");
}

$admin_name = $_SESSION['nama']; // Get admin's name from session

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


if (isset($_POST['tambah_pengadu_submit'])) {
    // Sanitize and get form data for pengguna table
    $nama = $conn->real_escape_string($_POST['nama']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);
    $role = 'Pengadu'; // Role otomatis diset sebagai 'Pengadu'

    // Sanitize and get form data for tb_telegram table
    $id_telegram = $conn->real_escape_string($_POST['id_telegram']);
    $id_chat = $conn->real_escape_string($_POST['id_chat']);
    // 'user' di tb_telegram akan disamakan dengan 'nama' pengguna

    // Basic validation
    if (empty($nama) || empty($password) || empty($id_telegram) || empty($id_chat)) {
        $_SESSION['form_message'] = "Nama, Password, ID Telegram, dan ID Chat harus diisi.";
        $_SESSION['form_message_type'] = 'error';
        header("Location: pengadu_tambah.php"); // Redirect kembali ke halaman ini untuk menampilkan error
        exit();
    } else {
        // --- SECURITY WARNING ---
        // Anda saat ini menyimpan password dalam plaintext. Ini SANGAT TIDAK AMAN.
        // Untuk produksi, ubah ini: $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        // Dan simpan $hashed_password ke DB. Kemudian gunakan password_verify() saat login.
        // ------------------------

        // Start transaction for atomicity (either both succeed or both fail)
        $conn->begin_transaction();

        try {
            // 1. Insert into pengguna table
            $stmt_pengguna = $conn->prepare("INSERT INTO pengguna (nama, email, password, Role) VALUES (?, ?, ?, ?)");
            if ($stmt_pengguna === FALSE) {
                throw new Exception("Prepare statement for pengguna failed: " . $conn->error);
            }
            $stmt_pengguna->bind_param("ssss", $nama, $email, $password, $role); // Simpan password plaintext untuk saat ini

            if (!$stmt_pengguna->execute()) {
                throw new Exception("Gagal menambahkan Pengadu: " . $stmt_pengguna->error);
            }
            $new_user_id = $stmt_pengguna->insert_id; // Get the ID of the newly inserted user
            $stmt_pengguna->close();

            // 2. Insert into tb_telegram table
            $stmt_telegram = $conn->prepare("INSERT INTO tb_telegram (id_telegram, id_chat, user) VALUES (?, ?, ?)");
            if ($stmt_telegram === FALSE) {
                throw new Exception("Prepare statement for tb_telegram failed: " . $conn->error);
            }
            $stmt_telegram->bind_param("sss", $id_telegram, $id_chat, $nama); // 'user' di tb_telegram adalah 'nama' pengguna

            if (!$stmt_telegram->execute()) {
                throw new Exception("Gagal menambahkan data Telegram: " . $stmt_telegram->error);
            }
            $stmt_telegram->close();

            // If both inserts are successful
            $conn->commit();
            $_SESSION['form_message'] = "Pengadu baru dengan nama '" . htmlspecialchars($nama) . "' dan data Telegram berhasil ditambahkan!";
            $_SESSION['form_message_type'] = 'success';
            // Redirect ke halaman lihat setelah sukses
            header("Location: pengadu_lihat.php"); 
            exit();

        } catch (Exception $e) {
            $conn->rollback(); // Rollback transaction on error
            $_SESSION['form_message'] = "Terjadi kesalahan: " . $e->getMessage();
            $_SESSION['form_message_type'] = 'error';
            header("Location: pengadu_tambah.php"); // Redirect kembali ke halaman ini jika ada error
            exit();
        }
    }
}
// Close connection here if you only need it for login,
// otherwise, leave it open if 'koneksi.php' manages a persistent connection.
// For simple scripts, closing here is fine.
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close(); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pengadu Baru - SiCepu</title>
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
        .form-group input[type="password"] {
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
        /* MODIFIKASI: Hapus btn-submit width: 100%; dan margin-top */
        .btn-submit {
            background-color: #007bff;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background-color 0.2s ease;
            /* width: 100%; */ /* Dihapus atau di override oleh form-actions */
            /* margin-top: 20px; */ /* Dihapus atau di override oleh form-actions */
        }
        .btn-submit:hover {
            background-color: #0056b3;
        }
        .form-divider {
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

        /* Styles for top-info-bar (copied from dashboard_admin.php) */
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

        /* Styles for Action Buttons Group (for back button) */
       /* Styles for Action Buttons Group (for back button) */
.form-actions {
    display: flex;
    justify-content: center; /* MODIFIKASI: Ubah dari flex-end menjadi center */
    gap: 10px; /* Space between buttons */
    margin-top: 20px;
}
/* Bagian CSS untuk .btn-back dan .btn-submit tetap sama */
.form-actions .btn-back,
.form-actions .btn-submit { 
    padding: 10px 20px;
    border-radius: 5px;
    text-decoration: none;
    cursor: pointer;
    font-size: 1em;
    transition: all 0.2s ease;
    width: auto; 
    display: inline-flex; 
    align-items: center;
    justify-content: center;
}
.form-actions .btn-back {
    background-color: #6c757d; 
    color: white;
    border: 1px solid #6c757d;
}
.form-actions .btn-back:hover {
    background-color: #5a6268;
    border-color: #545b62;
}
.form-actions .btn-submit {
    background-color: #007bff;
    color: white;
    border: 1px solid #007bff;
}
.form-actions .btn-submit:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}
.form-actions .btn-submit i, .form-actions .btn-back i {
    margin-right: 5px;
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
                    <li><a href="../jenis/jenis_lihat.php" class="nav-link"><i class="fas fa-file-alt"></i> Jenis Aduan </a></li>
                    <li><a href="pengadu_lihat.php" class="nav-link active"><i class="fas fa-users"></i> Pengadu</a></li>
                </ul>
                <div class="nav-section-title">SETTINGS</div>
                <ul>
                    <li><a href="../../logout.php" class="nav-link" id="logoutSidebar"><i class="fas fa-sign-out-alt"></i> Sign Out</a></li> </ul>
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
                    <div class="user-profile">
                        <img src="../../assets/img/admin_pfp.jpg" alt="User Avatar" class="avatar"> 
                        <span><?php echo htmlspecialchars($admin_name); ?></span>
                        <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i></a> 
                    </div>
                </div>
            </header>

            <section class="content-header">
                <h2>Tambah Pengadu Baru</h2>
            </section>

            <section class="form-section">
                <?php if ($message_from_session): ?>
                    <div class="message <?php echo $message_type_from_session; ?>">
                        <?php echo htmlspecialchars($message_from_session); ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <h3>Data Pengadu</h3>
                    <div class="form-group">
                        <label for="nama">Nama Pengguna (Username):</label>
                        <input type="text" id="nama" name="nama" placeholder="Masukkan nama pengguna" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" placeholder="Masukkan email (opsional)">
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                    </div>

                    <div class="form-divider"><span>Data Telegram</span></div>

                    <div class="form-group">
                        <label for="id_telegram">ID Telegram (Username Telegram):</label>
                        <input type="text" id="id_telegram" name="id_telegram" placeholder="Masukkan ID Telegram (misal: @username_tele)" required>
                    </div>
                    <div class="form-group">
                        <label for="id_chat">ID Chat Telegram:</label>
                        <input type="text" id="id_chat" name="id_chat" placeholder="Masukkan ID Chat Telegram" required>
                    </div>
                    
                    <div class="form-actions">
                        <a href="pengadu_lihat.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
                        <button type="submit" name="tambah_pengadu_submit" class="btn-submit"><i class="fas fa-plus"></i> Tambah Pengadu</button>
                    </div>
                </form>
            </section>
        </main>
    </div>
    <script src="../../assets/js/jquery-1.10.2.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script><script src="../../assets/js/sweetlogout.js"></script> 

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
                });
            }
            
            // Logika untuk menampilkan/menyembunyikan bagian Telegram berdasarkan pilihan role
            function toggleTelegramFields() {
                const selectedRole = $('input[name="role"]:checked').val();
                const telegramSection = $('.form-divider:contains("Data Telegram")').add($('label[for="id_telegram"]').parent('.form-group')).add($('label[for="id_chat"]').parent('.form-group'));

                if (selectedRole === 'Pengadu') {
                    telegramSection.show();
                    $('#id_telegram').prop('required', true);
                    $('#id_chat').prop('required', true);
                } else {
                    telegramSection.hide();
                    $('#id_telegram').prop('required', false).val(''); // Clear and remove required
                    $('#id_chat').prop('required', false).val(''); // Clear and remove required
                }
            }

            // Panggil saat halaman dimuat
            // Perhatikan: Karena ini pengadu_tambah.php, radio button role tidak ada secara langsung.
            // Fitur ini lebih relevan di pengguna_tambah.php atau pengguna_ubah.php
            // Anda bisa menghapus fungsi toggleTelegramFields dan panggilannya di sini
            // jika role pengadu_tambah memang selalu fix 'Pengadu'
            // toggleTelegramFields(); 
            // $('input[name="role"]').on('change', toggleTelegramFields); 
        });
    </script>
</body>
</html>