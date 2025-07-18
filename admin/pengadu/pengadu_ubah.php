<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// IMPORTANT: Ensure this is the very first thing to protect the page.
// Current file is admin/pengadu/pengadu_ubah.php
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

$iduser_to_edit = null;
$pengadu_data = []; // Data from pengguna table
$telegram_data = []; // Data from tb_telegram table (if Pengadu)

// --- Handle GET request to load existing data ---
if (isset($_GET['id'])) {
    $iduser_to_edit = intval($_GET['id']);

    // Fetch user data from 'pengguna' table
    $stmt_pengguna = $conn->prepare("SELECT iduser, nama, email, password, Role FROM pengguna WHERE iduser = ?");
    if ($stmt_pengguna === FALSE) {
        $_SESSION['form_message'] = "Terjadi kesalahan saat menyiapkan query pengguna: " . $conn->error;
        $_SESSION['form_message_type'] = 'error';
        header("Location: pengadu_lihat.php");
        exit();
    }
    $stmt_pengguna->bind_param("i", $iduser_to_edit);
    $stmt_pengguna->execute();
    $result_pengguna = $stmt_pengguna->get_result();

    if ($result_pengguna->num_rows === 1) {
        $pengadu_data = $result_pengguna->fetch_assoc();

        // Fetch Telegram data ONLY if the role is 'Pengadu'
        if ($pengadu_data['Role'] === 'Pengadu') {
            $stmt_telegram = $conn->prepare("SELECT id_telegram, id_chat FROM tb_telegram WHERE user = ?");
            if ($stmt_telegram === FALSE) {
                error_log("Prepare statement for getting telegram data failed: " . $conn->error);
            } else {
                $stmt_telegram->bind_param("s", $pengadu_data['nama']);
                $stmt_telegram->execute();
                $result_telegram = $stmt_telegram->get_result();
                
                if ($result_telegram->num_rows === 1) {
                    $telegram_data = $result_telegram->fetch_assoc();
                }
                $stmt_telegram->close();
            }
        }

    } else {
        $_SESSION['form_message'] = "Pengadu tidak ditemukan.";
        $_SESSION['form_message_type'] = 'error';
        header("Location: pengadu_lihat.php");
        exit();
    }
    $stmt_pengguna->close();

} 
// --- Handle POST request to update data ---
else if (isset($_POST['ubah_pengadu_submit'])) {
    $iduser_to_edit = intval($_POST['iduser']);
    $nama = $conn->real_escape_string($_POST['nama']);
    $email = $conn->real_escape_string($_POST['email']);
    $new_password = $_POST['password']; // Password input, might be empty if not changed

    // Dapatkan data telegram (jika ada, dari input)
    $id_telegram = $conn->real_escape_string($_POST['id_telegram'] ?? ''); 
    $id_chat = $conn->real_escape_string($_POST['id_chat'] ?? '');
    
    $old_nama = $conn->real_escape_string($_POST['old_nama']); // Original name to update telegram table
    $old_role_from_post = $conn->real_escape_string($_POST['old_role']); // Ambil old_role dari hidden input

    if (empty($nama)) { // Hapus validasi id_telegram dan id_chat di sini
        $_SESSION['form_message'] = "Nama tidak boleh kosong.";
        $_SESSION['form_message_type'] = 'error';
        header("Location: pengadu_ubah.php?id=" . $iduser_to_edit);
        exit();
    }
    
    // Validate Telegram fields if the role is 'Pengadu' and they are empty
    if ($old_role_from_post === 'Pengadu' && (empty($id_telegram) || empty($id_chat))) {
        $_SESSION['form_message'] = "Untuk role Pengadu, ID Telegram dan ID Chat harus diisi.";
        $_SESSION['form_message_type'] = 'error';
        header("Location: pengadu_ubah.php?id=" . $iduser_to_edit);
        exit();
    }

    $conn->begin_transaction();

    try {
        // Update pengguna table
        $update_password_clause = '';
        if (!empty($new_password)) {
            $update_password_clause = ", password = ?";
            $password_to_update = $new_password;
        }

        $stmt_pengguna_update = $conn->prepare("UPDATE pengguna SET nama = ?, email = ? " . $update_password_clause . " WHERE iduser = ?");
        
        if ($stmt_pengguna_update === FALSE) {
            throw new Exception("Prepare statement for pengguna update failed: " . $conn->error);
        }

        if (!empty($new_password)) {
            $stmt_pengguna_update->bind_param("sssi", $nama, $email, $password_to_update, $iduser_to_edit);
        } else {
            $stmt_pengguna_update->bind_param("ssi", $nama, $email, $iduser_to_edit);
        }

        if (!$stmt_pengguna_update->execute()) {
            throw new Exception("Gagal mengupdate Pengadu: " . $stmt_pengguna_update->error);
        }
        $stmt_pengguna_update->close();

        // Handle tb_telegram updates
        if ($old_role_from_post === 'Pengadu') { // Jika role lama adalah Pengadu
            // Coba update data Telegram (jika sudah ada) atau insert jika belum ada
            $stmt_check_telegram = $conn->prepare("SELECT COUNT(*) FROM tb_telegram WHERE user = ?");
            $stmt_check_telegram->bind_param("s", $old_nama);
            $stmt_check_telegram->execute();
            $telegram_exists = $stmt_check_telegram->get_result()->fetch_row()[0];
            $stmt_check_telegram->close();

            if ($telegram_exists > 0) {
                // Update existing Telegram data
                $stmt_telegram_update = $conn->prepare("UPDATE tb_telegram SET id_telegram = ?, id_chat = ?, user = ? WHERE user = ?");
                if ($stmt_telegram_update === FALSE) {
                    throw new Exception("Prepare update telegram failed: " . $conn->error);
                }
                $stmt_telegram_update->bind_param("ssss", $id_telegram, $id_chat, $nama, $old_nama);
                if (!$stmt_telegram_update->execute()) {
                    throw new Exception("Gagal mengupdate data Telegram: " . $stmt_telegram_update->error);
                }
                $stmt_telegram_update->close();
            } else {
                // Should not happen if old_role was Pengadu, but as fallback, insert
                 $stmt_telegram_insert = $conn->prepare("INSERT INTO tb_telegram (id_telegram, id_chat, user) VALUES (?, ?, ?)");
                if ($stmt_telegram_insert === FALSE) {
                    throw new Exception("Prepare insert telegram failed: " . $conn->error);
                }
                $stmt_telegram_insert->bind_param("sss", $id_telegram, $id_chat, $nama);
                if (!$stmt_telegram_insert->execute()) {
                    throw new Exception("Gagal menambahkan data Telegram baru: " . $stmt_telegram_insert->error);
                }
                $stmt_telegram_insert->close();
            }
        }
        // Jika role bukan Pengadu (old_role_from_post !== 'Pengadu'), tidak ada yang dilakukan pada tb_telegram


        $conn->commit();
        $_SESSION['form_message'] = "Data pengadu '" . htmlspecialchars($nama) . "' berhasil diperbarui!";
        $_SESSION['form_message_type'] = 'success';
        header("Location: pengadu_lihat.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['form_message'] = "Terjadi kesalahan: " . $e->getMessage();
        $_SESSION['form_message_type'] = 'error';
        header("Location: pengadu_ubah.php?id=" . $iduser_to_edit);
        exit();
    }

} else if (!isset($_GET['id'])) {
    // If not a POST request and no ID is provided in GET
    $_SESSION['form_message'] = "ID Pengadu tidak ditemukan untuk diubah.";
    $_SESSION['form_message_type'] = 'error';
    header("Location: pengadu_lihat.php");
    exit();
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
        <title>Ubah Data Pengadu - SiCepu</title>
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
                margin: 20px auto; /* Memusatkan form */
                max-width: 600px; /* Adjust width as needed */
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
                        <a href="#"><i class="fas fa-bell"></i></a>
                        <div class="user-profile">
                            <img src="../../assets/img/admin_pfp.jpg" alt="User Avatar" class="avatar"> 
                            <span><?php echo htmlspecialchars($admin_name); ?></span>
                            <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i></a> 
                        </div>
                    </div>
                </header>

                <section class="content-header" style="align-items: center; justify-content: center;">
                    <h2 style="text-align: center;">Ubah Data Pengadu</h2>
                </section>

                <section class="form-section">
                    <?php if ($message_from_session): ?>
                        <div class="message <?php echo $message_type_from_session; ?>">
                            <?php echo htmlspecialchars($message_from_session); ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <input type="hidden" name="iduser" value="<?php echo htmlspecialchars($pengadu_data['iduser'] ?? ''); ?>">
                        <input type="hidden" name="old_nama" value="<?php echo htmlspecialchars($pengadu_data['nama'] ?? ''); ?>">

                        <h3>Data Pengadu</h3>
                        <div class="form-group">
                            <label for="nama">Nama Pengguna (Username):</label>
                            <input type="text" id="nama" name="nama" placeholder="Masukkan nama pengguna" value="<?php echo htmlspecialchars($pengadu_data['nama'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" placeholder="Masukkan email (opsional)" value="<?php echo htmlspecialchars($pengadu_data['email'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="password">Password (Biarkan kosong jika tidak diubah):</label>
                            <input type="password" id="password" name="password" placeholder="********">
                        </div>
                        
                        <div class="form-actions">
                            <a href="pengadu_lihat.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
                            <button type="submit" name="ubah_pengadu_submit" class="btn-submit"><i class="fas fa-save"></i> Ubah Data Pengadu</button>
                        </div>
                    </form>
                </section>
            </main>
        </div>
        <script src="../../assets/js/jquery-1.10.2.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script><script src="../../assets/js/sweetlogout.js"></script> 
        <script>
            // Kirim variabel PHP ke JS
            const message_from_session = <?php echo json_encode($message_from_session); ?>;
            const message_type_from_session = <?php echo json_encode($message_type_from_session); ?>;
        </script>
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
                const message = message_from_session;
                const messageType = message_type_from_session;

                if (message) {
                    Swal.fire({
                        title: messageType === 'success' ? 'Berhasil!' : 'Gagal!',
                        text: message,
                        icon: messageType, // 'success' atau 'error'
                        confirmButtonText: 'Oke'
                    }).then((result) => {
                        // Jika pesan sukses, redirect ke pengadu_lihat.php setelah pop-up ditutup
                        if (messageType === 'success' && result.isConfirmed) {
                            window.location.href = 'pengadu_lihat.php';
                        }
                    });
                }
            });
        </script>
    </body>
</html>