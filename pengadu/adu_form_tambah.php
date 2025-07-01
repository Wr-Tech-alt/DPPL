<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Sertakan file koneksi database
require_once __DIR__ . '/../inc/koneksi.php';

// Cek apakah user sudah login dan memiliki peran 'Pengadu'
if (!isset($_SESSION['iduser']) || $_SESSION['role'] !== 'Pengadu') {
    header('Location: ../login.php');
    exit();
}

// Mengambil data pengguna dari session
$iduser = $_SESSION['iduser'];
$user_name = $_SESSION['nama'] ?? 'Pengadu';
$user_email = ''; // Bisa diambil jika diperlukan

// Mengambil daftar jenis aduan dari database untuk dropdown
$jenis_aduan_list = [];
$result_jenis = $conn->query("SELECT idjenis, jenis FROM jenis_pengaduan ORDER BY jenis ASC");
if ($result_jenis) {
    $jenis_aduan_list = $result_jenis->fetch_all(MYSQLI_ASSOC);
}

// Inisialisasi variabel untuk pesan error atau sukses
$errors = [];
$success_message = '';

// Proses form jika metode adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $idjenis = $_POST['idjenis'] ?? '';
    $judul = $_POST['judul'] ?? '';
    $notelp = $_POST['notelp'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    $lokasi = $_POST['lokasi'] ?? '';
    $gambar_path = '';

    // --- Validasi Input ---
    if (empty($judul)) $errors[] = "Judul aduan tidak boleh kosong.";
    if (empty($keterangan)) $errors[] = "Keterangan aduan tidak boleh kosong.";
    if (empty($idjenis)) $errors[] = "Jenis aduan harus dipilih.";
    if (empty($lokasi)) $errors[] = "Lokasi kejadian tidak boleh kosong.";

    // --- Proses Upload Gambar ---
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $max_size = 2 * 1024 * 1024; // 2 MB

        if (in_array($_FILES['gambar']['type'], $allowed_types) && $_FILES['gambar']['size'] <= $max_size) {
            // Buat nama file yang unik untuk menghindari penimpaan
            $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $unique_filename = time() . '_' . uniqid() . '.' . $file_extension;
            $upload_dir = __DIR__ . '/../uploads/'; // Pastikan folder 'uploads' ada di root project
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $upload_path = $upload_dir . $unique_filename;

            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_path)) {
                $gambar_path = $unique_filename; // Simpan nama filenya saja di database
            } else {
                $errors[] = "Gagal memindahkan file yang diunggah.";
            }
        } else {
            $errors[] = "File tidak valid. Pastikan formatnya (JPG, PNG) dan ukuran maksimal 2MB.";
        }
    } else {
        $errors[] = "Gambar bukti wajib diunggah.";
    }

    // --- Simpan ke Database jika tidak ada error ---
    if (empty($errors)) {
        $waktu_aduan = date('Y-m-d H:i:s');
        $status = 'Pending'; // Status awal saat aduan dibuat
        $author = $user_name; // Nama pengguna yang login

        $stmt = $conn->prepare("INSERT INTO pengaduan (iduser, idjenis, waktu_aduan, judul, notelp, keterangan, lokasi, status, gambar, author) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissssssss", $iduser, $idjenis, $waktu_aduan, $judul, $notelp, $keterangan, $lokasi, $status, $gambar_path, $author);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Aduan Anda telah berhasil dikirim!";
            header('Location: ../dashboard/dashboard_pengadu.php');
            exit();
        } else {
            $errors[] = "Terjadi kesalahan saat menyimpan aduan: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Aduan Baru - SiCepu</title>
    <link rel="stylesheet" href="../assets/css/dash_pengadu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Menggunakan CSS dari dashboard_pengadu.php dan menambahkan style untuk form */
        :root {
            --primary-color: #007bff; --primary-hover: #0056b3; --text-color: #333;
            --bg-color: #f4f7f6; --sidebar-bg: #ffffff; --card-bg: #ffffff;
            --border-color: #e0e0e0; --error-bg: #f8d7da; --error-text: #721c24;
        }
        body { font-family: 'Raleway', sans-serif; margin: 0; background-color: var(--bg-color); color: var(--text-color); }
        .app-container { display: flex; }
        .sidebar { width: 260px; background-color: var(--sidebar-bg); border-right: 1px solid var(--border-color); display: flex; flex-direction: column; height: 100vh; position: fixed; top: 0; left: 0; transition: transform 0.3s ease-in-out; z-index: 1000; }
        .sidebar-header, .user-profile, .sidebar-nav ul, .sidebar-footer { padding: 1.2rem 1.5rem; }
        .sidebar-header { display: flex; align-items: center; gap: 10px; font-size: 1.5em; font-weight: 700; }
        .user-profile { display: flex; align-items: center; gap: 15px; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); }
        .user-avatar { width: 45px; height: 45px; border-radius: 50%; background-color: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2em; }
        .user-name { font-weight: 600; }
        .user-email { font-size: 0.85em; color: #777; }
        .sidebar-nav { flex-grow: 1; }
        .sidebar-nav ul { list-style: none; margin: 0; padding: 0; }
        .sidebar-nav li a { display: flex; align-items: center; gap: 15px; padding: 0.9rem 1.5rem; text-decoration: none; color: #555; border-radius: 8px; margin-bottom: 5px; transition: background-color 0.2s, color 0.2s; }
        .sidebar-nav li a:hover, .sidebar-nav li.active a { background-color: var(--primary-color); color: white; }
        .sidebar-footer a { text-decoration: none; color: #555; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 1.5rem; transition: margin-left 0.3s ease-in-out; }
        .navbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        .hamburger-menu { display: none; background: none; border: none; font-size: 1.5em; cursor: pointer; color: var(--text-color); }
        
        /* Form Styling */
        .form-container {
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #555;
        }
        .form-group input[type="text"],
        .form-group input[type="tel"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1em;
            font-family: 'Raleway', sans-serif;
            box-sizing: border-box;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        .form-group input[type="file"] {
            padding: 8px;
        }
        .btn-submit {
            background-color: var(--primary-color); color: white; padding: 12px 25px; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1.1em; font-weight: 600;
        }
        .btn-submit:hover { background-color: var(--primary-hover); }
        .error-messages {
            background-color: var(--error-bg);
            color: var(--error-text);
            border: 1px solid #f5c6cb;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .error-messages ul {
            margin: 0;
            padding-left: 1.2rem;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; width: 100%; }
            .hamburger-menu { display: block; }
            .navbar { justify-content: flex-start; gap: 15px; }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="../assets/img/logos.png" alt="SiCepu Logo" class="logo" style="width:30px; height:30px;">
                <span class="logo-text">SiCepu</span>
            </div>
            <div class="user-profile">
                <div class="user-avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                <div class="user-info">
                    <span class="user-name"><?php echo $user_name; ?></span>
                    <span class="user-email"><?php echo $user_email; ?></span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="../dashboard/dashboard_pengadu.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="active"><a href="form_tambahaduan.php"><i class="fas fa-plus-circle"></i> Buat Aduan Baru</a></li>
                    <li><a href="#"><i class="fas fa-clipboard-list"></i> Riwayat Pengaduan</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> Pengaturan Akun</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </aside>

        <main class="main-content">
            <header class="navbar">
                <button class="hamburger-menu" id="hamburgerMenu"><i class="fas fa-bars"></i></button>
                <h1>Buat Aduan Baru</h1>
            </header>

            <div class="form-container">
                <?php if (!empty($errors)): ?>
                    <div class="error-messages">
                        <strong>Terjadi kesalahan:</strong>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="adu_form_tambah.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="judul">Judul Aduan</label>
                        <input type="text" id="judul" name="judul" placeholder="Contoh: Lampu jalan mati di depan rumah" required>
                    </div>
                    <div class="form-group">
                        <label for="idjenis">Jenis Aduan</label>
                        <select id="idjenis" name="idjenis" required>
                            <option value="">-- Pilih Jenis Aduan --</option>
                            <?php foreach ($jenis_aduan_list as $jenis): ?>
                                <option value="<?php echo htmlspecialchars($jenis['idjenis']); ?>">
                                    <?php echo htmlspecialchars($jenis['jenis']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="notelp">Nomor Telepon (Opsional)</label>
                        <input type="tel" id="notelp" name="notelp" placeholder="Contoh: 081234567890">
                    </div>
                    <div class="form-group">
                        <label for="lokasi">Lokasi Kejadian</label>
                        <input type="text" id="lokasi" name="lokasi" placeholder="Contoh: Jl. Merdeka No. 10, RT 01/RW 02" required>
                    </div>
                    <div class="form-group">
                        <label for="keterangan">Keterangan Lengkap</label>
                        <textarea id="keterangan" name="keterangan" rows="6" placeholder="Jelaskan detail aduan Anda di sini..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="gambar">Unggah Gambar Bukti (JPG, PNG, maks. 2MB)</label>
                        <input type="file" id="gambar" name="gambar" accept="image/jpeg, image/png" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Kirim Aduan</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hamburgerMenu = document.getElementById('hamburgerMenu');
            const sidebar = document.getElementById('sidebar');
            hamburgerMenu.addEventListener('click', () => sidebar.classList.toggle('show'));
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 992 && !sidebar.contains(e.target) && !hamburgerMenu.contains(e.target) && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>
