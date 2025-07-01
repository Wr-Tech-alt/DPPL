<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// IMPORTANT: Ensure this is the very first thing to protect the page.
// Current file is admin/pengguna/pengguna_tambah.php
// Path to login.php from here is ../../login.php
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php"); 
    exit();
}

// Path to koneksi.php from admin/pengguna/
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

if (isset($_POST['tambah_pengguna_submit'])) { // Ubah nama submit button
    // Sanitize and get form data for pengguna table
    $nama = $conn->real_escape_string($_POST['nama']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);
    $role = $conn->real_escape_string($_POST['role']); // Ambil role dari input form

    // Sanitize and get form data for tb_telegram table
    $id_telegram = $conn->real_escape_string($_POST['id_telegram']);
    $id_chat = $conn->real_escape_string($_POST['id_chat']);
    // 'user' di tb_telegram akan disamakan dengan 'nama' pengguna

    // Basic validation
    if (empty($nama) || empty($password) || empty($id_telegram) || empty($id_chat) || empty($role)) {
        $_SESSION['form_message'] = "Semua field wajib (Nama, Password, Role, ID Telegram, ID Chat) harus diisi.";
        $_SESSION['form_message_type'] = 'error';
        header("Location: pengguna_tambah.php"); // Redirect kembali ke halaman ini untuk menampilkan error
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
                throw new Exception("Gagal menambahkan Pengguna: " . $stmt_pengguna->error);
            }
            $new_user_id = $stmt_pengguna->insert_id; // Get the ID of the newly inserted user
            $stmt_pengguna->close();

            // 2. Insert into tb_telegram table
            // Ini akan dieksekusi hanya jika role yang ditambahkan adalah "Pengadu"
            // Karena tb_telegram terhubung ke role pengadu
            if ($role === 'Pengadu') { 
                $stmt_telegram = $conn->prepare("INSERT INTO tb_telegram (id_telegram, id_chat, user) VALUES (?, ?, ?)");
                if ($stmt_telegram === FALSE) {
                    throw new Exception("Prepare statement for tb_telegram failed: " . $conn->error);
                }
                $stmt_telegram->bind_param("sss", $id_telegram, $id_chat, $nama); // 'user' di tb_telegram adalah 'nama' pengguna

                if (!$stmt_telegram->execute()) {
                    throw new Exception("Gagal menambahkan data Telegram: " . $stmt_telegram->error);
                }
                $stmt_telegram->close();
            }

            // If both inserts are successful
            $conn->commit();
            $_SESSION['form_message'] = "Pengguna baru dengan nama '" . htmlspecialchars($nama) . "' dan role '" . htmlspecialchars($role) . "' berhasil ditambahkan!";
            $_SESSION['form_message_type'] = 'success';
            // Redirect ke halaman lihat setelah sukses
            header("Location: pengguna_lihat.php"); 
            exit();

        } catch (Exception $e) {
            $conn->rollback(); // Rollback transaction on error
            $_SESSION['form_message'] = "Terjadi kesalahan: " . $e->getMessage();
            $_SESSION['form_message_type'] = 'error';
            header("Location: pengguna_tambah.php"); // Redirect kembali ke halaman ini jika ada error
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
    <title>Tambah Pengguna Baru - SiCepu</title>
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
        .btn-submit {
            background-color: #007bff;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background-color 0.2s ease;
            width: 100%;
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

        /* Styles for Radio Buttons */
        .form-group.radio-group label {
            display: inline-block; /* For the main label */
            margin-right: 15px;
            font-weight: normal; /* Override bold from .form-group label */
        }
        .form-group.radio-group input[type="radio"] {
            margin-right: 5px;
            vertical-align: middle;
        }
        .form-group.radio-group .radio-option {
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: all 0.2s ease;
            margin-right: 10px;
            background-color: #f8f8f8;
        }
        .form-group.radio-group .radio-option:hover {
            background-color: #e9ecef;
            border-color: #c0c0c0;
        }
        .form-group.radio-group input[type="radio"]:checked + .radio-option {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }
        .form-group.radio-group input[type="radio"]:checked + .radio-option label {
            color: white; /* Ensure label text is white when checked */
        }
        .form-group.radio-group input[type="radio"] {
            /* Hide the default radio button */
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        /* Style the visible part of the radio button */
        .form-group.radio-group .radio-option label {
            cursor: pointer;
            position: relative;
            padding-left: 0; /* Remove default padding for custom look */
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
                <h2>Tambah Pengguna Baru</h2>
            </section>

            <section class="form-section">
                <?php if ($message_from_session): ?>
                    <div class="message <?php echo $message_type_from_session; ?>">
                        <?php echo htmlspecialchars($message_from_session); ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <h3>Data Pengguna</h3>
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

                    <div class="form-group radio-group">
                        <label>Role:</label><br>
                        <?php 
                        $roles = ['Admin', 'Petugas', 'Pengadu'];
                        $default_role = 'Pengadu'; // Set default role
                        foreach ($roles as $role_option): 
                        ?>
                            <input type="radio" id="role_<?php echo strtolower($role_option); ?>" name="role" value="<?php echo $role_option; ?>" <?php echo ($role_option === $default_role) ? 'checked' : ''; ?> required>
                            <label for="role_<?php echo strtolower($role_option); ?>" class="radio-option">
                                <?php echo $role_option; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-divider"><span>Data Telegram</span></div>

                    <div class="form-group">
                        <label for="id_telegram">ID Telegram (Username Telegram):</label>
                        <input type="text" id="id_telegram" name="id_telegram" placeholder="Masukkan ID Telegram (misal: @username_tele)">
                    </div>
                    <div class="form-group">
                        <label for="id_chat">ID Chat Telegram:</label>
                        <input type="text" id="id_chat" name="id_chat" placeholder="Masukkan ID Chat Telegram">
                    </div>
                    
                    <button type="submit" name="tambah_pengguna_submit" class="btn-submit">Tambah Pengguna</button>
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
                });
            }
            
            // Logika untuk menampilkan/menyembunyikan bagian Telegram berdasarkan pilihan role
            function toggleTelegramFields() {
                const selectedRole = $('input[name="role"]:checked').val();
                const telegramSection = $('.form-divider:contains("Data Telegram"), .form-group label[for="id_telegram"]').parent('div.form-group').add($('.form-group label[for="id_chat"]').parent('div.form-group'));
                const telegramDivider = $('.form-divider:contains("Data Telegram")');

                if (selectedRole === 'Pengadu') {
                    telegramSection.show();
                    telegramDivider.show();
                    $('#id_telegram').prop('required', true);
                    $('#id_chat').prop('required', true);
                } else {
                    telegramSection.hide();
                    telegramDivider.hide();
                    $('#id_telegram').prop('required', false).val(''); // Clear and remove required
                    $('#id_chat').prop('required', false).val(''); // Clear and remove required
                }
            }

            // Panggil saat halaman dimuat
            toggleTelegramFields();

            // Panggil saat radio button role berubah
            $('input[name="role"]').on('change', toggleTelegramFields);
        });
    </script>
</body>
</html>