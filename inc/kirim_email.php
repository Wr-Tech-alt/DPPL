<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';

function kirimEmailPengaduan($email, $nama, $judul, $status) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'emailkamu@gmail.com'; // GANTI
        $mail->Password   = 'app_password';        // GANTI: App Password Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('emailkamu@gmail.com', 'Sistem Pengaduan');
        $mail->addAddress($email, $nama);

        $mail->isHTML(true);
        $mail->Subject = "Status Pengaduan Anda: $status";
        $mail->Body    = "
            <h3>Halo, $nama</h3>
            <p>Pengaduan Anda dengan judul: <b>$judul</b> telah ditandai sebagai <b>$status</b>.</p>
            <p>Terima kasih telah menggunakan sistem pengaduan kami.</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Gagal kirim email: " . $mail->ErrorInfo);
        return false;
    }
}
?>
