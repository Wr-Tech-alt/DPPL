<?php
// inc/koneksi.php
$servername = "localhost"; // Your database server name
$username = "root";        // Your database username
$password = "";            // Your database password (empty for XAMPP default)
$dbname = "sicepu";          // Your database name (e.g., 'dppl' as per previous discussions)

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
// Optional: Set character set to UTF-8
$conn->set_charset("utf8");
?>