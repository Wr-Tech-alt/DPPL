<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// IMPORTANT: Ensure this is the very first thing to protect the page.
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['Admin', 'Petugas', 'Pengadu'])) {
    // If not logged in or role not recognized, redirect to login page.
    header("Location: ../login.php"); 
    exit();
}

// You can fetch user-specific data here if needed
$user_name = $_SESSION['nama']; // Get user's name from session
$user_role = $_SESSION['role']; // Get user's role from session
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SiCepu</title>
    <link rel="stylesheet" href="../assets/css/dashadmin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/img/shodai_logo.png" alt="SiCepu Logo" class="logo"> 
                <span class="logo-text">SiCepu</span>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="#" class="nav-link active"><i class="fas fa-th-large"></i> Dashboard</a></li>
                    <li><a href="#" class="nav-link"><i class="fas fa-boxes"></i> Aduan Fasilitas</a></li>
                    <li><a href="../admin/pengguna/pengguna_lihat.php" class="nav-link"><i class="fas fa-users"></i> Pengadu </a></li>
                    <li><a href="#" class="nav-link"><i class="fas fa-envelope"></i> Pengguna </a></li>
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
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search...">
                    <span class="shortcut">⌘K</span>
                </div>
                <div class="nav-icons">
                    <a href="#"><i class="fas fa-bell"></i></a>
                    <a href="#"><i class="fas fa-comment"></i></a>
                    <div class="user-profile">
                        <img src="../assets/img/user_avatar.jpg" alt="User Avatar" class="avatar"> 
                        <span><?php echo htmlspecialchars($user_name); ?></span>
                        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i></a> 
                    </div>
                </div>
            </header>

            <section class="content-header">
                <h2>Selamat Datang di Halaman Dashboard!</h2>
                </section>

            <section style="padding: 20px; background-color: #fff; margin: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <h3>Ini adalah halaman kosong untuk konten dashboard Anda.</h3>
                <p>Anda bisa menambahkan statistik, grafik, atau ringkasan lainnya di sini.</p>
                <div style="margin-top: 20px; padding: 15px; border: 1px dashed #ccc; text-align: center; color: #666;">
                    Konten Utama Dashboard Akan Tampil Disini.
                </div>
            </section>

            </main>
    </div>
    </body>
</html>