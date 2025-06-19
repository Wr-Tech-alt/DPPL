<?php
// Include database connection file
include "inc/koneksi.php";
?>


<!doctype html>
<html lang="en">
  <head>
    <title>Login Admin SiCepu</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <!-- Link to Lato Font from Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">
    <!-- Link to Font Awesome for icons -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <!-- Link to SweetAlert2 for beautiful alerts -->
    <link rel="stylesheet" href="dist/swal/sweetalert2.min.css">
    
    <!-- Custom styles for the login page -->
    <style>
      /* Universal box-sizing for consistent layout */
      * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
      }

      /* Base styles for body and html, using Lato font */
      body, html {
        font-family: 'Lato', sans-serif;
        scroll-behavior: smooth;
        height: 100%;
        overflow-x: hidden;
      }

      /* Styling for the entire login page background */
      body.login {
        background-image: url('images/background.png'); /* Background image from login ijo.php */
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        display: flex; /* Use flexbox to center content vertically and horizontally */
        justify-content: center;
        align-items: center;
        min-height: 100vh; /* Ensure it takes full viewport height */
        margin: 0; /* Remove default body margin */
      }

      /* Main section container, ensuring it covers the full width */
      section {
        min-height: 100vh; /* Ensure section remains full height */
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
        width: 100%; /* Ensure section takes full width */
      }

      /* Styling for the login form container, adapted from .login-center */
      .login-center {
        background: rgba(255, 255, 255, 0.9); /* Slightly transparent white background */
        color: #372D35; /* Dark text color */
        padding: 30px; /* Ample padding inside the form box */
        border-radius: 8px; /* Rounded corners for the form box */
        box-shadow: 0 4px 15px rgba(0,0,0,0.3); /* Stronger shadow for depth */
        width: 400px; /* Fixed width for the form box */
        max-width: 90%; /* Responsive adjustment for smaller screens */
        text-align: center; /* Center align text inside the container */
      }

      /* Styling for form groups (spacing between input fields) */
      .login-center .form-group {
          margin-bottom: 20px;
      }

      /* Styling for the input group container (icon + input field) */
      .login-center .input-group {
          display: flex; /* Use flexbox to align icon and input horizontally */
          width: 100%; /* Ensure the group takes full width */
          border: 1px solid #ced4da; /* Single border around the whole group */
          border-radius: 5px; /* Rounded corners for the input group */
          overflow: hidden; /* Ensures content stays within rounded corners */
      }

      /* Styling for the icon addon part of the input group */
      .login-center .input-group-addon {
          background-color: #e9ecef; /* Light gray background for the addon */
          padding: 10px 15px;
          display: flex; /* Use flexbox to center the icon */
          align-items: center;
      }

      /* Styling for the actual input fields */
      .login-center .form-control {
          border: none; /* Remove individual border from input field to avoid double borders */
          box-shadow: none; /* Remove Bootstrap's default box shadow */
          padding: 10px 15px;
          height: auto; /* Adjust height automatically */
          font-size: 16px;
          flex-grow: 1; /* Allow the input field to take up remaining space */
      }

      /* Focus state styling for the input group when an input field is focused */
      .login-center .input-group:focus-within {
          border-color: #79a10d; /* Border color changes on focus */
          box-shadow: 0 0 0 0.2rem rgba(121, 161, 13, 0.25); /* Shadow on focus */
          outline: none; /* Remove default outline */
      }

      /* Styling for the submit button */
      .login-center .btn-primary {
          background: rgb(38, 79, 9); /* Dark green background */
          border-color: rgb(38, 79, 9); /* Matching border color */
          color: white;
          padding: 12px 28px;
          font-size: 18px;
          font-weight: bold;
          border-radius: 8px;
          transition: 0.3s ease-in-out; /* Smooth transition on hover */
          box-shadow: 0 4px 6px rgba(0,0,0,0.2); /* Shadow for the button */
          width: 100%; /* Make button full width */
      }

      /* Hover state for the submit button */
      .login-center .btn-primary:hover {
          background: rgb(26, 55, 6); /* Darker green on hover */
          border-color: rgb(26, 55, 6);
          transform: scale(1.02); /* Slightly scale up on hover */
          box-shadow: 0 6px 12px rgba(0,0,0,0.3); /* Stronger shadow on hover */
      }

      /* Styling for the main title "SICEPU" */
      .login-center h2 {
          font-size: 32px;
          color: #372D35; /* Dark text color */
          margin-bottom: 10px;
          text-shadow: none; /* Remove old text shadow */
      }

      /* Styling for the subtitle "Sistem Informasi Cepat Pengaduan Fasilitas Umum" */
      .login-center center:nth-of-type(2) {
          font-size: 16px;
          color: #555;
          margin-bottom: 30px;
      }

      /* Styling for the copyright text "SICEPU 2025" */
      .login-center center:last-of-type {
          font-size: 14px;
          color: #888;
          margin-top: 20px;
      }

      /* SweetAlert popup font size override */
      .swal2-popup {
        font-size: 1.6rem !important;
      }
    </style>

  </head>
  <body class="login">

    <!-- Main section for the login page, taking full viewport height -->
    <section id="hero">
      <!-- Login form container -->
      <div class="login-center">
          <!-- Main title -->
          <center>
            <h2>
              <b>SICEPU</b>
            </h2>
          </center>
          <!-- Subtitle -->
          <CENTER>Sistem Informasi Cepat Pengaduan Fasilitas Umum</CENTER>
          
          <!-- Login form -->
          <form action="" method="POST" enctype="multipart/form-data">
            <br />
            <!-- Username input group -->
            <div class="form-group input-group">
              <span class="input-group-addon">
                <i class="fa fa-user"></i> <!-- User icon for username -->
              </span>
              <input type="text" class="form-control" value="" placeholder="username" name="username" id="username" required/>
            </div>
            <!-- Password input group -->
            <div class="form-group input-group">
              <span class="input-group-addon">
                <i class="fa fa-lock"></i> <!-- Lock icon for password -->
              </span>
              <input type="password" class="form-control" value="" placeholder="password" name="password" id="password" required/>
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn btn-primary form-control" name="btnLogin" title="Masuk Sistem" id="clicker">MASUK</button>
            <br>
            <br>
            <!-- Copyright text -->
            <CENTER>SICEPU 2025</CENTER>
          </form>
      </div>
    </section>

    <!-- SCRIPTS - Loaded at the bottom to reduce page load time -->
    <!-- JQUERY SCRIPTS -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <!-- BOOTSTRAP SCRIPTS -->
    <script src="assets/js/bootstrap.min.js"></script>
    <!-- METISMENU SCRIPTS (Can be removed if not used on this page) -->
    <script src="assets/js/jquery.metisMenu.js"></script>
    <!-- CUSTOM SCRIPTS (Can be removed if not used on this page or adapted) -->
    <script src="assets/js/custom.js"></script>
    <!-- SWAL -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>

  </body>
</html>

<?php
// PHP login processing logic
if (isset($_POST['btnLogin'])) {
  // SQL query to check username and password
  // WARNING: Storing passwords in plain text is highly insecure.
  // Consider using password_hash() and password_verify() for production.
  $sql_login = "SELECT * FROM tb_pengguna WHERE username='" . $_POST['username'] . "' AND password='" . $_POST['password'] . "'";
  $query_login = mysqli_query($koneksi, $sql_login);
  $data_login = mysqli_fetch_array($query_login, MYSQLI_BOTH);
  $jumlah_login = mysqli_num_rows($query_login);


  if ($jumlah_login == 1) {
    // Start session and set session variables
    session_start();
    $_SESSION["ses_id"] = $data_login["id_pengguna"];
    $_SESSION["ses_nama"] = $data_login["nama_pengguna"];
    $_SESSION["ses_level"] = $data_login["level"];
    $_SESSION["ses_grup"] = $data_login["grup"];

    // Display success message and redirect
    echo "<script>
                    Swal.fire({title: 'SUKSES',text: 'Login Berhasil!',icon: 'success',confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.value) {
                            window.location = 'index'; // Redirect to index page
                        }
                    })</script>";
  } else {
    // Display failure message
    echo "<script>
                    Swal.fire({title: 'GAGAL',text: 'Username atau Password salah!',icon: 'error',confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.value) {
                            window.location = 'login'; // Stay on login page or redirect to index
                        }
                    })</script>";
  }
}
?>

<!-- END -->
