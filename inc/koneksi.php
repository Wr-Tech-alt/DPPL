<?php
// inc/koneksi.php
$db_host = "localhost";
$db_user = "u951570841_hadi";
$db_pass = "Hadi333#"; // Your MySQL password
$db_name = "u951570841_hadi"; // Your database name

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
$conn->set_charset("utf8");
?>