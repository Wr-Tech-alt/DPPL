<!doctype html>
<html lang="en">
  <head>
    <title>Beranda - Bengkel Service</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
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

      section {
        min-height: 100vh;
        padding: 60px 30px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background-size: cover;
        background-position: center;
        color: white;
        text-align: center;
      }

      header {
        position: fixed;
        top: 0;
        width: 100%;
        padding: 15px 30px;
        background: rgba(215, 197, 0, 0.66);
        color: white;
        z-index: 1000;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      header h1 {
        font-size: 24px;
      }

      header a.button-login {
        background: white;
        color:rgb(143, 143, 42);
        padding: 8px 16px;
        border-radius: 5px;
        text-decoration: none;
        transition: 0.3s ease;
      }

      header a.button-login:hover {
        background:rgb(126, 153, 4);
        color: white;
        box-shadow: 0 0 10px rgba(255,255,255,0.3);
      }

      #hero h1 {
        font-size: 48px;
        font-weight: bold;
        margin-bottom: 20px;
        text-shadow: 2px 2px 5px #000;
      }

      #hero p {
        font-size: 20px;
        text-shadow: 1px 1px 3px #000;
      }

      footer {
        background: #1e2e6e;
        color: white;
        text-align: center;
        padding: 20px;
      }
    </style>
  </head>
  <body>
    <header>
      <h1>Sistem Informasi Cepat Pengaduan Umum</h1>
      <a class="button-login" href="login.php">Login</a>
    </header>
          
      <section style="background-image: url('images/background.png');"></section>

    <footer>
      &copy; 2025 CV. Gemilang Sejati
    </footer>
  </body>
</html>
