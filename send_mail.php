<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Pastikan kamu sudah install PHPMailer via Composer atau manual
require 'vendor/PHPMailer/src/Exception.php';
require 'vendor/PHPMailer/src/PHPMailer.php';
require 'vendor/PHPMailer/src/SMTP.php';

function kirimEmailDonatur($email, $nama, $status) {
    $mail = new PHPMailer(true);

    // Ganti sesuai akun admin kamu
    $adminEmail = 'emailanda@gmail.com'; 
    $adminName  = 'Admin Donasi';
    $appPassword = 'passwordaplikasi'; // gunakan password aplikasi Gmail

    try {
        // =============== KONFIGURASI SMTP ===============
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $adminEmail;
        $mail->Password   = $appPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // =============== INFORMASI PENGIRIM & PENERIMA ===============
        $mail->setFrom($adminEmail, $adminName);
        $mail->addAddress($email, $nama);

        // =============== ISI EMAIL ===============
        $mail->isHTML(true);
        $mail->Subject = "Notifikasi Donasi - Status: " . ucfirst($status);

        if ($status == "diterima") {
            $mail->Body = "
                <html>
                <body style='font-family: Arial, sans-serif; color: #333;'>
                    <h2 style='color: green;'>Donasi Anda Telah Diterima 🎉</h2>
                    <p>Halo <b>$nama</b>,</p>
                    <p>Terima kasih atas kebaikan hati Anda. Donasi Anda telah <b>DITERIMA</b> oleh admin kami.</p>
                    <p>Donasi ini akan segera kami salurkan kepada pihak yang membutuhkan.</p>
                    <br>
                    <p>Hormat kami,</p>
                    <p><b>$adminName</b></p>
                </body>
                </html>
            ";
        } else {
            $mail->Body = "
                <html>
                <body style='font-family: Arial, sans-serif; color: #333;'>
                    <h2 style='color: orange;'>Donasi Anda Masih Pending ⏳</h2>
                    <p>Halo <b>$nama</b>,</p>
                    <p>Terima kasih telah melakukan donasi. Saat ini status donasi Anda masih <b>PENDING</b>.</p>
                    <p>Admin kami akan melakukan verifikasi terlebih dahulu. Anda akan mendapatkan email konfirmasi kembali setelah donasi diterima.</p>
                    <br>
                    <p>Hormat kami,</p>
                    <p><b>$adminName</b></p>
                </body>
                </html>
            ";
        }

        // =============== KIRIM EMAIL ===============
        $mail->send();
        return true;

    } catch (Exception $e) {
        // Jika gagal, kamu bisa log error untuk debugging
        error_log("Gagal mengirim email ke $email: {$mail->ErrorInfo}");
        return false;
    }
}
?>
