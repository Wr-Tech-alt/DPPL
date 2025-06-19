<?php
include "inc/koneksi.php";
?>


<!doctype html>
<html lang="en">
  <head>
    <title>Login Admin SiCepu</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <link rel="stylesheet" href="dist/swal/sweetalert2.min.css">
    <style>
      /* CSS dari login ijo.php, diadaptasi untuk form login */
      * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
      }

      body, html {
        font-family: 'Lato', sans-serif;
        scroll-behavior: smooth;
        height: 100%;
        overflow-x: hidden;
      }

      /* Background untuk seluruh halaman login */
      body.login {
        background-image: url('images/background.png'); /* Gambar latar belakang */
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0; /* Pastikan tidak ada margin default */
      }

      section {
        min-height: 100vh; /* Pastikan section tetap setinggi viewport */
        padding: 60px 30px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background-size: cover;
        background-position: center;
        color: white;
        text-align: center;
        position: relative;
        width: 100%; /* Pastikan section mengambil lebar penuh */
      }

      /* Styling untuk container form login, diadaptasi dari .login-center di login ijo.php */
      .login-center {
        background: rgba(255, 255, 255, 0.9); /* Sedikit transparan putih */
        color: #372D35;
        padding: 30px; /* Padding lebih besar untuk form */
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3); /* Shadow lebih kuat */
        width: 400px; /* Lebar tetap untuk form */
        max-width: 90%; /* Agar responsif di layar kecil */
        text-align: center; /* Pastikan teks di dalam kontainer rata tengah */
      }

      /* Menyesuaikan input field agar terlihat modern */
      .login-center .form-group {
          margin-bottom: 20px;
      }

      .login-center .input-group-addon {
          background-color: #e9ecef;
          border: 1px solid #ced4da;
          border-right: none;
          padding: 10px 15px;
          border-radius: 5px 0 0 5px;
          display: flex; /* Untuk menyelaraskan ikon */
          align-items: center;
      }

      .login-center .form-control {
          border-radius: 0 5px 5px 0;
          padding: 10px 15px;
          height: auto; /* Agar tidak terpotong */
          font-size: 16px;
          border: 1px solid #ced4da;
      }

      .login-center .form-control:focus {
          border-color: #79a10d; /* Warna border focus */
          box-shadow: 0 0 0 0.2rem rgba(121, 161, 13, 0.25); /* Warna shadow focus */
          outline: none;
      }

      /* Tombol MASUK */
      .login-center .btn-primary {
          background: rgb(38, 79, 9); /* Warna hijau gelap */
          border-color: rgb(38, 79, 9);
          color: white;
          padding: 12px 28px;
          font-size: 18px;
          font-weight: bold;
          border-radius: 8px;
          transition: 0.3s ease-in-out;
          box-shadow: 0 4px 6px rgba(0,0,0,0.2);
          width: 100%; /* Agar memenuhi lebar kontainer */
      }

      .login-center .btn-primary:hover {
          background: rgb(26, 55, 6); /* Hijau lebih gelap saat hover */
          border-color: rgb(26, 55, 6);
          transform: scale(1.02);
          box-shadow: 0 6px 12px rgba(0,0,0,0.3);
      }

      /* Style untuk judul dan deskripsi di dalam form */
      .login-center h2 {
          font-size: 32px;
          color: #372D35; /* Warna teks gelap */
          margin-bottom: 10px;
          text-shadow: none; /* Hapus text-shadow lama */
      }

      /* Styling untuk teks di bawah judul utama "SICEPU" */
      .login-center center:nth-of-type(2) {
          font-size: 16px;
          color: #555;
          margin-bottom: 30px;
      }

      /* Styling untuk teks "SICEPU 2025" */
      .login-center center:last-of-type {
          font-size: 14px;
          color: #888;
          margin-top: 20px;
      }

      /* SweetAlert default style */
      .swal2-popup {
        font-size: 1.6rem !important;
      }
    </style>

  </head>
  <body class="login">

    <section id="hero">
      <div class="login-center">
          <center>
            <h2>
              <b>SICEPU</b>
            </h2>
          </center>
          <CENTER>Sistem Informasi Cepat Pengaduan Fasilitas Umum</CENTER>
          <form action="" method="POST" enctype="multipart/form-data">
            <br />
            <div class="form-group input-group">
              <span class="input-group-addon">
                <i class="fa fa-user"></i>
              </span>
              <input type="text" class="form-control" value="" placeholder="username" name="username" id="username" required/>
            </div>
            <div class="form-group input-group">
              <span class="input-group-addon">
                <i class="fa fa-lock"></i>
              </span>
              <input type="password" class="form-control" value="" placeholder="password" name="password" id="password" required/>
            </div>

            <button type="submit" class="btn btn-primary form-control" name="btnLogin" title="Masuk Sistem" id="clicker" />MASUK</button>
            <br>
            <br>
            <CENTER>SICEPU 2025</CENTER>
          </form>
      </div>
    </section>

    <script src="assets/js/jquery-1.10.2.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/jquery.metisMenu.js"></script>
    <script src="assets/js/custom.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>

  </body>
</html>

<?php
if (isset($_POST['btnLogin'])) {
  $sql_login = "SELECT * FROM tb_pengguna WHERE username='" . $_POST['username'] . "' AND password='" . $_POST['password'] . "'";
  $query_login = mysqli_query($koneksi, $sql_login);
  $data_login = mysqli_fetch_array($query_login, MYSQLI_BOTH);
  $jumlah_login = mysqli_num_rows($query_login);


  if ($jumlah_login == 1) {
    session_start();
    $_SESSION["ses_id"] = $data_login["id_pengguna"];
    $_SESSION["ses_nama"] = $data_login["nama_pengguna"];
    $_SESSION["ses_level"] = $data_login["level"];
    $_SESSION["ses_grup"] = $data_login["grup"];

    echo "<script>
                    Swal.fire({title: 'SUKSES',text: 'Login Berhasil!',icon: 'success',confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.value) {
                            window.location = 'index';
                        }
                    })</script>";
  } else {
    echo "<script>
                    Swal.fire({title: 'GAGAL',text: 'Username atau Password salah!',icon: 'error',confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.value) {
                            window.location = 'index';
                        }
                    })</script>";
  }
}
?>