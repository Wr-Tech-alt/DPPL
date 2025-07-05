<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';

function kirimEmailPengaduan($emailTujuan, $namaPengadu, $judulAduan, $status) {
    require_once __DIR__ . '/../inc/koneksi.php';
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $mail = new PHPMailer(true);

    // Ambil akun pengirim dari user yang sedang login (Admin/Petugas)
    $id_admin = $_SESSION['user_id'];
    $q = mysqli_query($conn, "SELECT email, pass, nama FROM pengguna WHERE iduser = '$id_admin' LIMIT 1");
    $config = mysqli_fetch_assoc($q);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['email'];         // Email pengirim dari DB
        $mail->Password   = $config['pass'];          // App Password Gmail dari DB
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($config['email'], $config['nama']);
        $mail->addAddress($emailTujuan, $namaPengadu);

        $mail->isHTML(true);
        $mail->Subject = "Status Aduan Anda: $status";
        $mail->Body    = "
            <p>Halo <strong>$namaPengadu</strong>,</p>
            <p>Aduan Anda dengan judul <strong>$judulAduan</strong> telah diperbarui menjadi <strong>$status</strong>.</p>
            <p>Terima kasih telah menggunakan layanan pengaduan kami.</p>
            <hr>
            <small>Email ini dikirim otomatis oleh sistem SiCepu.</small>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Gagal kirim email ke $emailTujuan: " . $mail->ErrorInfo);
    }
}