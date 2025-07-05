<?php
session_start();
require '../../inc/koneksi.php'; // Sesuaikan path file koneksinya

$pengadu_data = [
    'iduser' => '',
    'nama' => '',
    'email' => ''
];

$message_from_session = '';
$message_type_from_session = '';

// Ambil data pengguna berdasarkan ID dari URL
if (isset($_GET['id'])) {
    $iduser = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT iduser, nama, email FROM pengguna WHERE iduser = ?");
    $stmt->bind_param("i", $iduser);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $pengadu_data = $result->fetch_assoc();
    } else {
        $message_from_session = "Data pengguna tidak ditemukan.";
        $message_type_from_session = "error";
    }

    $stmt->close();
}

// Proses Update (dari jawaban sebelumnya)
if (isset($_POST['ubah_pengadu_submit'])) {
    $iduser = $_POST['iduser'];
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($iduser) || empty($nama)) {
        $message_from_session = "ID dan Nama wajib diisi!";
        $message_type_from_session = "error";
    } else {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE pengguna SET nama = ?, email = ?, password = ? WHERE iduser = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssi", $nama, $email, $hashed_password, $iduser);
        } else {
            $query = "UPDATE pengguna SET nama = ?, email = ? WHERE iduser = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $nama, $email, $iduser);
        }

        if ($stmt->execute()) {
            $message_from_session = "Data berhasil diperbarui!";
            $message_type_from_session = "success";
        } else {
            $message_from_session = "Gagal memperbarui data. Silakan coba lagi.";
            $message_type_from_session = "error";
        }

        $stmt->close();
    }
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
            /* .message (SweetAlert2 will handle this) */
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
                        <li><a href="pengadu_lihat.php" class="nav-link active"><i class="fas fa-users"></i> Pengadu</a></li>
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
                            <span id="currentDateTime"></span> | <span>STMI JAKARTA, Dki Jakarta, Indonesia</span>
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
                    <h2>Ubah Data Pengadu</h2>
                </section>

                <section class="form-section">
                <form action="pengadu_ubah.php?id=<?php echo htmlspecialchars($pengadu_data['iduser']); ?>" method="POST">
                        <input type="hidden" name="iduser" value="<?php echo htmlspecialchars($pengadu_data['iduser'] ?? ''); ?>">

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
                        
                        <button type="submit" name="ubah_pengadu_submit" class="btn-submit">Ubah Data Pengadu</button>
                    </form>
                </section>
            </main>
        </div>
        <script src="../../assets/js/jquery-1.10.2.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
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
