<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ env('APP_NAME') }}</title>
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
  <header class="header d-flex align-items-center sticky-top" id="header">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">
      <a class="logo d-flex align-items-center me-auto" href="./">
        <h1 class="sitename">{{ env('APP_NAME') }}</h1>
      </a>

      <nav class="navmenu" id="navmenu">
        <ul>
          <li><a class="active" href="#hero">Beranda</a></li>
          <li><a href="#about">Tentang</a></li>
          <li><a href="#features">Fitur</a></li>
          <li><a href="#contact">Hubungi Kami</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <!-- <a class="btn-getstarted" href="{{ route('admin.auth.login') }}">Masuk</a> -->
      <!-- <a class="btn-getstarted" href="#register">Pesan</a> -->

    </div>
  </header>

  <main class="main">

    <!-- Hero Section -->
    <section class="section hero light-background" id="hero">

      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-6 order-lg-1 d-flex flex-column justify-content-center order-2" data-aos="fade-up">
            <h2>Kelola Koperasi dan Saldo Santri dengan Transparan dan Profesional</h2>
            <p>{{ env('APP_NAME') }} adalah solusi digital untuk mencatat dan mengelola saldo santri di
              lingkungan
              pesantren secara terstruktur, transparan, dan mudah diawasi — mulai dari proses top up,
              pembelian,
              hingga penarikan dana oleh santri.</p>
          </div>
          <div class="col-lg-6 order-lg-2 hero-img order-1" data-aos="zoom-out" data-aos-delay="200">
            <img class="img-fluid" src="assets/img/hero-img.jpg" alt="" style="border-radius: 10px;">
          </div>
        </div>
      </div>

    </section><!-- /Hero Section -->

    <!-- About Section -->
    <section class="section about" id="about">

      <div class="container">

        <h3 class="text-center">Sistem Terintegrasi untuk Manajemen Keuangan Santri yang Aman dan Rapi</h3>
        <p class="mb-5 text-center">
          {{ env('APP_NAME') }} dirancang khusus untuk koperasi pesantren, sekolah Islam, dan lingkungan
          pendidikan
          berbasis boarding. Aplikasi ini memudahkan operator koperasi untuk mencatat setiap transaksi
          keuangan santri secara akurat, sekaligus memberikan akses informasi saldo bagi orang tua atau
          wali secara real time.
        </p>
        <div class="row gy-3 items-center">
          <div class="col-lg-6 d-flex flex-column justify-content-center" data-aos="fade-up" data-aos-delay="100">
            <img class="img-fluid" src="assets/img/about-img.jpg" alt="" style="border-radius:10px;">
          </div>
          <div class="col-lg-6 d-flex flex-column justify-content-center" data-aos="fade-up" data-aos-delay="200">
            <div class="about-content ps-lg-3 ps-0">
              <ul>
                <li>
                  <i class="bi bi-activity"></i>
                  <div>
                    <h4>Pencatatan Top Up Saldo</h4>
                    <p>Operator koperasi dapat mencatat setoran dari orang tua atau wali ke akun
                      saldo masing-masing santri dengan cepat dan aman.</p>
                  </div>
                </li>
                <li>
                  <i class="bi bi-person-bounding-box"></i>
                  <div>
                    <h4>Pencatatan Top Up Saldo</h4>
                    <p>Setiap transaksi pembelian di koperasi atau penarikan tunai oleh santri
                      tercatat secara otomatis, memastikan transparansi dana yang keluar.</p>
                  </div>
                </li>
                <li>
                  <i class="bi bi-hand-thumbs-up"></i>
                  <div>
                    <h4>Riwayat Transaksi Digital</h4>
                    <p>Orang tua dan santri dapat melihat semua aktifitas keuangan secara rinci,
                      mulai dari tanggal, jenis transaksi, hingga sisa saldo.</p>
                  </div>
                </li>
                <li>
                  <i class="bi bi-journal-check"></i>
                  <div>
                    <h4>Akses Online untuk Wali Santri</h4>
                    <p>Orang tua bisa memantau saldo anak mereka melalui tautan pribadi atau akun
                      login yang aman — tanpa perlu bertanya ke koperasi.</p>
                  </div>
                </li>
                <li>
                  <i class="bi bi-journal-check"></i>
                  <div>
                    <h4>Integrasi dengan POS Koperasi</h4>
                    <p>{{ env('APP_NAME') }} siap diintegrasikan dengan sistem penjualan koperasi
                      (Point of Sale) untuk mencatat pembelian secara otomatis dan langsung
                      memotong saldo.</p>
                  </div>
                </li>
                <li>
                  <i class="bi bi-journal-check"></i>
                  <div>
                    <h4>Laporan Keuangan Koperasi</h4>
                    <p>Manajemen koperasi dapat mengakses laporan harian, mingguan, atau bulanan
                      mengenai pemasukan, pengeluaran, dan total transaksi saldo santri.</p>
                  </div>
                </li>
                <li>
                  <i class="bi bi-journal-check"></i>
                  <div>
                    <h4>Notifikasi & Pengingat</h4>
                    <p>Sistem memberikan notifikasi otomatis untuk transaksi besar, saldo minim,
                      atau top up terbaru — membantu pengawasan dan pengendalian konsumsi.</p>
                  </div>
                </li>
              </ul>
            </div>

          </div>
        </div>

      </div>

    </section><!-- /About Section -->

    <!-- Features Section -->
    <!-- <section class="services section light-background" id="features">
      <div class="section-title container" data-aos="fade-up">
        <h2>Mendukung Dua Skema Produksi</h2>
      </div>

      <div class="container">

        <div class="row gy-4">

          <div class="col-6 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="service-item position-relative">
              <div class="icon"><i class="bi bi-activity icon"></i></div>
              <h4><a class="stretched-link" href="service-details.html">Maklun (CMT / Cut, Make, Trim)</a></h4>
              <p>Brand menyediakan bahan baku, sistem hanya mencatat pekerjaan dan perhitungan ongkos jahit.</p>
            </div>
          </div>
          <div class="col-6 col-md-6" data-aos="fade-up" data-aos-delay="200">
            <div class="service-item position-relative">
              <div class="icon"><i class="bi bi-bell icon"></i></div>
              <h4><a class="stretched-link" href="service-details.html">Full Production (FOB / Full Order Basis)</a></h4>
              <p>Konveksi menyuplai bahan hingga pengemasan akhir. StitchFlow siap menangani proses yang lebih kompleks pada fase pengembangan berikutnya.</p>
            </div>
          </div>

        </div>

      </div>

    </section> -->
    <!-- /Services Section -->

    <!-- Contact Section -->
    <section class="contact section" id="features">

      <!-- Section Title -->
      <div class="container" data-aos="fade-up">
        <h2 class="text-center">Kenapa Memilih {{ env('APP_NAME') }}?</h2>
        <div class="text-center">
          <p><strong>1. Transparansi & Amanah:</strong> Semua transaksi tercatat secara digital, meminimalkan
            kecurangan, dan membangun kepercayaan antara koperasi, santri, dan wali.</p>
          <p><strong>2. Mendukung Pendidikan Karakter:</strong> Membantu santri belajar mengelola keuangan
            sejak dini dengan sistem yang terpantau dan bertanggung jawab.</p>
          <p><strong>3. Efisiensi Operasional Koperasi:</strong> Kurangi pencatatan manual yang memakan waktu,
            sehingga petugas koperasi bisa fokus pada pelayanan.</p>
          <p><strong>4. Siap Dikembangkan Lebih Lanjut:</strong> Dirancang modular untuk mendukung fitur
            tambahan seperti tabungan, belanja online internal, atau pelaporan pajak koperasi di masa depan.
          </p>
        </div>
      </div><!-- End Section Title -->

    </section><!-- /Contact Section -->

  </main>

  <footer class="footer position-relative" id="footer">

    <div class="footer-newsletter">
      <div class="container">
        <div class="row justify-content-center text-center">
          <div class="col-lg-12">
            <h4 id="register">Mulai Sekarang!</h4>
            <p>Daftar Sekarang dan nikmati pengalaman manajemen koperasi yang lebih mudah dan modern dengan
              {{ env('APP_NAME') }} !</p>
            <a class="btn-get-started"
              href="https://wa.me/6285317404760?text=Bismillah,+Assalamu'alaikum.+Saya+ingin+mendaftar+aplikasi+Amanah+POS+untuk+manajemen+koperasi+instansi+kami.+Mohon+info+selanjutnya."
              target="_blank">
              Pesan Sekarang!!!
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="container">
      <div class="row text-center" id="contact">
        <div class="col-lg-12 mt-5 text-center">
          <h4>Hubungi Kami</h4>
          <p class="mt-3"><strong>Telepon / WA:</strong> <a
              href="https://wa.me/6285317404760">+6285-3174-04760</a>
          </p>
          <p><strong>Email:</strong> <span>amanahpos@shiftech.my.id</span></p>
        </div>
      </div>
      <!--
        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Tautan Lainnya</h4>
          <ul>
            <li><i class="bi bi-chevron-right"></i> <a href="#">Home</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#about">Tentang</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#features">Fitur</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#contact">Hubungi Kami</a></li>
          </ul>
        </div>


        <div class="col-lg-4 col-md-12">
          <h4>Follow Us</h4>
          <p>Cras fermentum odio eu feugiat lide par naso tierra videa magna derita valies</p>
          <div class="social-links d-flex">
            <a href=""><i class="bi bi-twitter-x"></i></a>
            <a href=""><i class="bi bi-facebook"></i></a>
            <a href=""><i class="bi bi-instagram"></i></a>
            <a href=""><i class="bi bi-linkedin"></i></a>
          </div>
        </div>-->

    </div>

    <div class="copyright container mt-4 text-center">
      <p>© {{ date('Y') }} <strong class="sitename px-1"><a href="https://shiftech.my.id">Shiftech
            Indonesia</a></strong> <span>All Rights Reserved</span></p>
      <!-- <div class="credits"> -->
      <!-- All the links in the footer should remain intact. -->
      <!-- You can delete the links only if you've purchased the pro version. -->
      <!-- Licensing information: https://bootstrapmade.com/license/ -->
      <!-- Purchase the pro version with working PHP/AJAX contact form: [buy-url] -->
      <!-- Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
      </div> -->
    </div>

  </footer>

  <!-- Scroll Top -->
  <a class="scroll-top d-flex align-items-center justify-content-center" id="scroll-top" href="#"><i
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
