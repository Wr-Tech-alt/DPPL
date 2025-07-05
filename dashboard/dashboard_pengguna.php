<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// IMPORTANT: Ensure this is the very first thing in dashboard_admin.php to protect the page.
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php"); 
    exit();
}

require_once '../inc/koneksi.php'; // Correct path to koneksi.php from dashboard/

// Add this check immediately after including koneksi.php
if (!isset($conn) || $conn->connect_error) {
    die("Fatal Error: Database connection object (\$conn) is not available or connection failed in dashboard_admin.php. Please check inc/koneksi.php.");
}

$admin_name = $_SESSION['nama']; // Get admin's name from session

// Fetch users from the 'pengguna' table
$users = [];
$sql = "SELECT iduser, nama, email, Role FROM pengguna ORDER BY nama ASC"; // Select relevant columns
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
    <title>Admin Dashboard - SiCepu</title>
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
                    <li><a href="#" class="nav-link"><i class="fas fa-th-large"></i> Dashboard</a></li>
                    <li><a href="#" class="nav-link"><i class="fas fa-boxes"></i> Pengaduan</a></li>
                    <li><a href="#" class="nav-link active"><i class="fas fa-users"></i> Pengguna</a></li>
                    <li><a href="#" class="nav-link"><i class="fas fa-envelope"></i> Ditanggapi</a></li>
                </ul>
                <div class="nav-section-title">LAINNYA</div>
                <ul>
                    <li><a href="#" class="nav-link"><i class="fas fa-box"></i> Selesai</a></li>
                    <li><a href="#" class="nav-link"><i class="fas fa-puzzle-piece"></i> Pending</a></li>
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
                        <span><?php echo htmlspecialchars($admin_name); ?></span>
                        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i></a> 
                    </div>
                </div>
            </header>

            <section class="content-header">
                <div class="customer-tabs">
                    <button class="tab-button active">All Users</button> </div>
                <div class="header-actions">
                    <button class="btn-secondary"><i class="fas fa-download"></i> Export</button>
                    <button class="btn-primary"><i class="fas fa-plus"></i> Add User</button> </div>
            </section>

            <section class="customer-table-section">
                <div class="filter-bar">
                    <button class="btn-filter"><i class="fas fa-filter"></i> Filter</button>
                    <div class="search-customer">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search user email..."> </div>
                    <div class="sort-icons">
                        <i class="fas fa-sort-up"></i>
                        <i class="fas fa-sort-down"></i>
                    </div>
                </div>

                <div class="table-container">
                    <table>
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
                                            <img src="../assets/img/avatar1.jpg" alt="Avatar" class="table-avatar"> 
                                            <?php echo htmlspecialchars($user['nama']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['Role']); ?></td>
                                        <td><i class="fas fa-ellipsis-h action-icon"></i></td>
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

                <div class="pagination">
                    <a href="#" class="page-arrow"><i class="fas fa-chevron-left"></i></a>
                    <a href="#" class="page-number active">1</a>
                    <a href="#" class="page-arrow"><i class="fas fa-chevron-right"></i></a>
                </div>
            </section>
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.customer-tabs .tab-button');
            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                });
            });
        });
    </script>
</body>
</html>