<?php
session_start();

// Check if the user is logged in and is an Admin
// IMPORTANT: Ensure this is the very first thing in dashboard_admin.php to protect the page.
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') {
    // If not logged in or not an Admin, redirect to login page.
    // The path 'Location: ../login.php' means "go up one directory from 'dashboard/'
    // to the project root, then find 'login.php'".
    header("Location: ../login.php"); 
    exit();
}

// You can fetch admin-specific data here if needed
$admin_name = $_SESSION['nama']; // Get admin's name from session
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
                    <button class="tab-button active">All Customers</button>
                    <button class="tab-button">New Customers</button>
                    <button class="tab-button">From Europe</button>
                    <button class="tab-button">Asia</button>
                    <button class="tab-button">Others</button>
                </div>
                <div class="header-actions">
                    <button class="btn-secondary"><i class="fas fa-download"></i> Export</button>
                    <button class="btn-primary"><i class="fas fa-plus"></i> Add Customers</button>
                </div>
            </section>

            <section class="customer-table-section">
                <div class="filter-bar">
                    <button class="btn-filter"><i class="fas fa-filter"></i> Filter</button>
                    <div class="search-customer">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search customer email...">
                    </div>
                    <div class="sort-icons">
                        <i class="fas fa-sort-up"></i>
                        <i class="fas fa-sort-down"></i>
                    </div>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Location</th>
                                <th>Orders</th>
                                <th>Spent</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <img src="../assets/img/avatar1.jpg" alt="Avatar" class="table-avatar">
                                    Ramisa Sanjana
                                </td>
                                <td>ramisa@gmail.com</td>
                                <td>14 Clifton Down Road, UK</td>
                                <td>7</td>
                                <td>$3331.00</td>
                                <td><i class="fas fa-ellipsis-h action-icon"></i></td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="../assets/img/avatar2.jpg" alt="Avatar" class="table-avatar">
                                    Mohua Amin
                                </td>
                                <td>mohua@gmail.com</td>
                                <td>405 Kings Road, Chelsea, London</td>
                                <td>44</td>
                                <td>$74,331.00</td>
                                <td><i class="fas fa-ellipsis-h action-icon"></i></td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="../assets/img/avatar3.jpg" alt="Avatar" class="table-avatar">
                                    Estiaq Noor
                                </td>
                                <td>estiaqnoor@gmail.com</td>
                                <td>176 Finchley Road, London</td>
                                <td>5</td>
                                <td>$12,746.00</td>
                                <td><i class="fas fa-ellipsis-h action-icon"></i></td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="../assets/img/avatar4.jpg" alt="Avatar" class="table-avatar">
                                    Reaz Nahid
                                </td>
                                <td>reaz@hotmail.com</td>
                                <td>12 South Bridge, Edinburgh, UK</td>
                                <td>27</td>
                                <td>$44,131.89</td>
                                <td><i class="fas fa-ellipsis-h action-icon"></i></td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="../assets/img/avatar5.jpg" alt="Avatar" class="table-avatar">
                                    Rabbi Nahid
                                </td>
                                <td>amin@yourmail.com</td>
                                <td>80 High Street, Winchester</td>
                                <td>16</td>
                                <td>$7331.00</td>
                                <td><i class="fas fa-ellipsis-h action-icon"></i></td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="../assets/img/avatar6.jpg" alt="Avatar" class="table-avatar">
                                    Sakib Al Baky
                                </td>
                                <td>sakib@yahoo.com</td>
                                <td>80 High Street, Winchester</td>
                                <td>47</td>
                                <td>$8231.00</td>
                                <td><i class="fas fa-ellipsis-h action-icon"></i></td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="../assets/img/avatar7.jpg" alt="Avatar" class="table-avatar">
                                    Maria Nur
                                </td>
                                <td>maria@gmail.com</td>
                                <td>80 High Street, Winchester</td>
                                <td>12</td>
                                <td>$9631.00</td>
                                <td><i class="fas fa-ellipsis-h action-icon"></i></td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="../assets/img/avatar8.jpg" alt="Avatar" class="table-avatar">
                                    Ahmed Baky
                                </td>
                                <td>maria@gmail.com</td>
                                <td>80 High Street, Winchester</td>
                                <td>12</td>
                                <td>$9631.00</td>
                                <td><i class="fas fa-ellipsis-h action-icon"></i></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <a href="#" class="page-arrow"><i class="fas fa-chevron-left"></i></a>
                    <a href="#" class="page-number active">1</a>
                    <a href="#" class="page-number">2</a>
                    <a href="#" class="page-number">3</a>
                    <a href="#" class="page-number">4</a>
                    <a href="#" class="page-number">5</a>
                    <a href="#" class="page-number">6</a>
                    <span>...</span>
                    <a href="#" class="page-number">24</a>
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