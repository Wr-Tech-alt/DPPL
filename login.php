<?php
include "inc/koneksi.php";
?>


<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Admin SiCepu</title>
  <link href="assets/css/bootstrap.css" rel="stylesheet" />
  <link href="assets/css/font-awesome.css" rel="stylesheet" />
  <link href="assets/css/style.css" rel="stylesheet" />
  <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">


  <link rel="stylesheet" href="dist/swal/sweetalert2.min.css">
  <style>
    .swal2-popup {
      font-size: 1.6rem !important;
    }

    /* Gaya untuk body agar memiliki latar belakang */
    body.login {
        font-family: 'Lato', sans-serif; /* Gunakan Lato sebagai font utama */
        background-image: url('images/background.png'); /* Ganti dengan path gambar latar belakangmu */
        background-size: cover; /* Pastikan gambar menutupi seluruh area */
        background-position: center; /* Posisikan gambar di tengah */
        background-attachment: fixed; /* Gambar tetap saat discroll */
        display: flex; /* Untuk memusatkan konten secara vertikal */
        align-items: center; /* Untuk memusatkan konten secara vertikal */
        justify-content: center; /* Untuk memusatkan konten secara horizontal */
        min-height: 100vh; /* Tinggi minimum 100% dari viewport */
        margin: 0; /* Hapus margin default body */
    }

    /* Gaya baru untuk panel login agar warnanya tidak transparan dan memiliki bayangan lebih baik */
    .panel.panel-primary.login-shadow {
        background-color: rgba(255, 255, 255, 0.95); /* Sedikit transparan agar background terlihat */
        border-radius: 0.75rem; /* Border radius lebih besar */
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); /* Bayangan yang lebih kuat */
        padding: 2.5rem; /* Padding internal */
        border: none; /* Hapus border default */
    }

    /* Penyesuaian untuk header H2 */
    .panel-body h2 {
        color: #374151; /* Warna teks gelap */
        font-weight: 700; /* Lebih tebal */
        margin-bottom: 0.5rem; /* Jarak bawah */
    }

    /* Penyesuaian untuk teks di bawah H2 */
    .panel-body center:nth-of-type(1) {
        color: #6B7280; /* Warna teks abu-abu */
        margin-bottom: 2rem; /* Jarak bawah */
        font-size: 0.875rem; /* Ukuran font lebih kecil */
    }

    /* Penyesuaian input group */
    .form-group.input-group {
        margin-bottom: 1.5rem; /* Jarak bawah antar input */
    }

    .form-control {
        border-radius: 0.5rem; /* Border radius pada input */
        padding: 0.75rem 1rem; /* Padding input */
        font-size: 1rem; /* Ukuran font input */
        border: 1px solid #D1D5DB; /* Warna border input */
    }

    .input-group-addon {
        background-color: #F9FAFB; /* Warna background ikon */
        border: 1px solid #D1D5DB; /* Warna border ikon */
        border-right: none; /* Hapus border kanan ikon */
        border-radius: 0.5rem 0 0 0.5rem; /* Border radius hanya di kiri */
        padding: 0.75rem 1rem; /* Padding ikon */
    }

    /* Gaya tombol "Masuk" yang diadaptasi dari .button-masuk-custom */
    .btn.btn-primary.form-control {
        background: #14B8A6; /* teal-600 */
        color: white;
        padding: 12px 28px;
        font-size: 18px;
        font-weight: bold;
        border-radius: 8px;
        text-decoration: none;
        transition: 0.3s ease-in-out;
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        display: flex; /* Untuk justify-center */
        justify-content: center; /* Untuk justify-center */
        width: 100%; /* Agar memenuhi lebar kontainer */
        border: none; /* Hapus border bawaan */
    }

    .btn.btn-primary.form-control:hover {
        background: #0D9488; /* teal-700 */
        transform: scale(1.02); /* Sedikit scaling */
        box-shadow: 0 6px 12px rgba(0,0,0,0.3);
    }
  </style>

</head>

<body class="login">
  <div class="container">
    <div class="row ">
      <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3 col-xs-10 col-xs-offset-1">
        <div class="panel panel-primary login-shadow">
          <div class="panel-body">
            <img src="assets/img/stmi.png" class="user-image img-responsive" style="max-width: 120px; margin: 0 auto 1.5rem auto; display: block;" /> <center>
              <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 text-center mb-2">
                <b>Masuk ke SiCepu</b>
              </h2>
            </center>
            <center class="text-sm text-gray-600 mb-8">Sistem Informasi Cepat Pengaduan Fasilitas Umum</center>
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
              <div class="form-group input-group">
                <span class="input-group-addon">
                  <i class="fa fa-tag"></i>
                </span>
                <input type="text" class="form-control" value="" placeholder="username" name="username" id="username" />
              </div>
              <div class="form-group input-group">
                <span class="input-group-addon">
                  <i class="fa fa-lock"></i>
                </span>
                <input type="password" class="form-control" value="" placeholder="password" name="password" id="password" />
              </div>

              <button type="submit" class="btn btn-primary form-control" name="btnLogin" title="Masuk Sistem" id="clicker">MASUK</button>
              <br>
              <CENTER>
                <p class="text-sm text-gray-600">Belum punya akun? Hubungi Administrator.</p>
              </CENTER>
              <CENTER class="mt-4">
                <p class="text-sm text-gray-600">SICEPU 2025</p>
              </CENTER>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

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
                    Swal.fire({title: 'SUKSES',text: '',icon: 'success',confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.value) {
                            window.location = 'index';
                        }
                    })</script>";
  } else {
    echo "<script>
                    Swal.fire({title: 'GAGAL',text: '',icon: 'error',confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.value) {
                            window.location = 'index';
                        }
                    })</script>";
  }
}
?>