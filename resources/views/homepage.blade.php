<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>{{ $company_name }}</title>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    @vite([])
</head>

<body class="index-page">
    <header id="header" class="header d-flex align-items-center sticky-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center">
            <a href="./" class="logo d-flex align-items-center me-auto">
                <h1 class="sitename">{{ $company_name }}</h1>
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="#hero" class="active">Beranda</a></li>
                    <li><a href="#contact">Hubungi Kami</a></li>
                    <!-- <li><a href="#features">Fitur</a></li>
          <li><a href="#contact">Hubungi Kami</a></li> -->
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>

            <!-- <a class="btn-getstarted" href="{{ route('customer.auth.login') }}">Masuk</a> -->
            <!-- <a class="btn-getstarted" href="#register">Pesan</a> -->

        </div>
    </header>

    <main class="main">

        <!-- Hero Section -->
        <section id="hero" class="section hero light-background">

            <div class="container">
                <div class="row justify-content-center text-center">
                    <div class="col-lg-12">
                        <h4 id="register">Selamat Datang di {{ $company_name }}</h4>
                        <p>Aplikasi koperasi ini digunakan oleh wali santri untuk memantau saldo, mutasi transaksi,
                            tagihan, dan pembayaran yang berkaitan dengan koperasi {{ $company_name }}.</p>
                        <p>
                            <a class="btn-get-started" href="{{ route('customer.auth.login') }}">Masuk Wali Santri</a>
                        </p>
                    </div>
                </div>
            </div>

        </section><!-- /Hero Section -->
    </main>

    <footer id="footer" class="footer position-relative">
        <div class="container">
            <div class="row text-center">
                <div class="col-lg-12 mt-5 text-center">
                    <h4 id="contact">Hubungi Kami:</h4>
                    <!-- Contact admin koperasi -->
                    <p class="mt-3"><strong>Telepon / WA:</strong> <a
                            href="https://wa.me/{{ $company_phone }}">{{ $company_phone }}</a></p>
                    @if ($company_email)
                        <p><strong>Email:</strong> <span>{{ $company_email }}</span></p>
                    @endif
                </div>
            </div>
        </div>

        <div class="copyright container mt-4 text-center">
            <p>Tentang <a href="{{ route('landing-page') }}">{{ env('APP_NAME') }} {{ env('APP_VERSION_STR') }}</a>
            </p>
            <p>© {{ date('Y') }} <strong class="sitename px-1"><a href="https://shiftech.my.id">Shiftech
                        Indonesia</a></strong> <span>All Rights Reserved</span></p>
        </div>

    </footer>

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Preloader -->
    <div id="preloader"></div>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    <!-- Main JS File -->
    <script src="assets/js/main.js"></script>

</body>

</html>
