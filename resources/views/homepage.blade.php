<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
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
  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">
      <a href="./" class="logo d-flex align-items-center me-auto">
        <h1 class="sitename">{{ env('APP_NAME') }}</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#hero" class="active">Beranda</a></li>
          <li><a href="#about">Tentang</a></li>
          <li><a href="#features">Fitur</a></li>
          <li><a href="#contact">Hubungi Kami</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <a class="btn-getstarted" href="{{ route('admin.auth.login') }}">Masuk</a>
      <a class="btn-getstarted" href="{{ route('admin.auth.register') }}">Daftar</a>

    </div>
  </header>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="section hero light-background">

      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-6 order-lg-1 d-flex flex-column justify-content-center order-2" data-aos="fade-up">
            <h2>Kelola Produksi Konveksi dengan Efisien dan Profesional</h2>
            <p>SewFlow adalah solusi digital untuk mengelola proses produksi maklun dan konveksi secara transparan, terstruktur, dan mudah diawasi — dari penyerahan bahan hingga pembayaran hasil kerja.</p>
          </div>
          <div class="col-lg-6 order-lg-2 hero-img order-1" data-aos="zoom-out" data-aos-delay="200">
            <img src="assets/img/hero-img.jpg" class="img-fluid" style="border-radius: 10px;" alt="">
          </div>
        </div>
      </div>

    </section><!-- /Hero Section -->

    <!-- About Section -->
    <section id="about" class="section about">

      <div class="container">

        <h3 class="text-center">Sistem Terintegrasi untuk Manajemen Produksi yang Lebih Baik</h3>
        <p class="mb-5 text-center">
          SewFlow dirancang khusus untuk usaha konveksi yang bekerja sama dengan penjahit lepas
          atau tim produksi internal. Aplikasi ini membantu Anda mencatat setiap proses produksi
          secara akurat dan meminimalkan kesalahan administrasi.
        </p>
        <div class="row gy-3 items-center">
          <div class="col-lg-6 d-flex flex-column justify-content-center" data-aos="fade-up" data-aos-delay="100">
            <img src="assets/img/about-img.jpg" alt="" class="img-fluid" style="border-radius:10px;">
          </div>
          <div class="col-lg-6 d-flex flex-column justify-content-center" data-aos="fade-up" data-aos-delay="200">
            <div class="about-content ps-lg-3 ps-0">
              <ul>
                <li>
                  <i class="bi bi-activity"></i>
                  <div>
                    <h4>Manajemen Order Produksi</h4>
                    <p>Catat dan pantau setiap pesanan dari brand — mulai dari jenis produk, model, ukuran, jumlah, hingga status pengerjaan.</p>
                  </div>
                </li>
                <li>
                  <i class="bi bi-person-bounding-box"></i>
                  <div>
                    <h4>Penugasan ke Penjahit</h4>
                    <p>Distribusikan pekerjaan kepada penjahit dengan pencatatan detail bahan yang diambil, jumlah potongan, dan waktu target penyelesaian.</p>
                  </div>
                </li>
                <li>
                  <i class="bi bi-hand-thumbs-up"></i>
                  <div>
                    <h4>Proses Serah Terima</h4>
                    <p>Pencatatan otomatis saat penjahit menyerahkan hasil jahitan. Sistem akan mencocokkan jumlah, status kualitas, dan progres pengerjaan.</p>
                  </div>
                </li>
                <li>
                  <i class="bi bi-journal-check"></i>
                  <div>
                    <h4>Penghitungan Upah Otomatis</h4>
                    <p>Perhitungan upah dilakukan secara otomatis berdasarkan jumlah potongan yang diselesaikan, sesuai dengan kesepakatan tarif per model atau ukuran.</p>
                  </div>
                </li>
                <li>
                  <i class="bi bi-journal-check"></i>
                  <div>
                    <h4>Laporan Produksi dan Pembayaran</h4>
                    <p>Dapatkan laporan ringkas dan mendetail mengenai kinerja produksi, status pembayaran, dan performa penjahit dalam satu dashboard terpusat.</p>
                  </div>
                </li>
                <li>
                  <i class="bi bi-journal-check"></i>
                  <div>
                    <h4>Notifikasi dan Riwayat Aktivitas</h4>
                    <p>Dapatkan notifikasi terkait penyerahan, keterlambatan, atau pembayaran. Semua aktivitas tercatat untuk memudahkan evaluasi.</p>
                  </div>
                </li>
              </ul>
            </div>

          </div>
        </div>

      </div>

    </section><!-- /About Section -->

    <!-- Features Section -->
    <section id="features" class="services section light-background">

      <!-- Section Title -->
      <div class="section-title container" data-aos="fade-up">
        <h2>Mendukung Dua Skema Produksi</h2>
      </div><!-- End Section Title -->

      <div class="container">

        <div class="row gy-4">

          <div class="col-6 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="service-item position-relative">
              <div class="icon"><i class="bi bi-activity icon"></i></div>
              <h4><a href="service-details.html" class="stretched-link">Maklun (CMT / Cut, Make, Trim)</a></h4>
              <p>Brand menyediakan bahan baku, sistem hanya mencatat pekerjaan dan perhitungan ongkos jahit.</p>
            </div>
          </div>
          <div class="col-6 col-md-6" data-aos="fade-up" data-aos-delay="200">
            <div class="service-item position-relative">
              <div class="icon"><i class="bi bi-bell icon"></i></div>
              <h4><a href="service-details.html" class="stretched-link">Full Production (FOB / Full Order Basis)</a></h4>
              <p>Konveksi menyuplai bahan hingga pengemasan akhir. StitchFlow siap menangani proses yang lebih kompleks pada fase pengembangan berikutnya.</p>
            </div>
          </div>

        </div>

      </div>

    </section><!-- /Services Section -->

    <!-- Contact Section -->
    <section id="contact" class="contact section">

      <!-- Section Title -->
      <div class="container" data-aos="fade-up">
        <h2 class="text-center">Bagaimana SewFlow Membantu Bisnis Anda?</h2>
        <div class="text-center">
          <p><strong>1. Transparansi & Kejelasan Proses:</strong> Setiap pengambilan bahan, pengerjaan, hingga pembayaran tercatat secara digital.</p>
          <p><strong>2. Efisiensi Waktu dan Administrasi:</strong> Kurangi pekerjaan manual yang repetitif dan fokus pada peningkatan produktivitas.</p>
          <p><strong>3. Meningkatkan Kepercayaan Mitra dan Tim:</strong> Brand dapat memantau prosesnya, dan penjahit merasa lebih adil dan profesional karena semua data terdokumentasi dengan baik.</p>
          <p><strong>4. Skalabilitas Usaha:</strong> Siap digunakan baik untuk usaha kecil maupun konveksi berskala besar dengan banyak mitra penjahit.</p>
        </div>
      </div><!-- End Section Title -->

    </section><!-- /Contact Section -->

  </main>

  <footer id="footer" class="footer position-relative">

    <div class="footer-newsletter">
      <div class="container">
        <div class="row justify-content-center text-center">
          <div class="col-lg-12">
            <h4>Mulai Sekarang!</h4>
            <p>Daftar Sekarang dan nikmati pengalaman manajemen konveksi yang lebih mudah dan modern dengan SewFlow!</p>
            <a href="https://wa.me/6285317404760?text=Halo+saya+ingin+mendaftar+aplikasi+SewFlow+untuk+manajemen+produksi+konveksi+usaha+saya.+Mohon+info+selanjutnya." target="_blank" class="btn-get-started">
              Pesan Sekrang
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="container">
      <div class="row text-center">
        <div class="col-lg-12 mt-5 text-center">
          <h4>Hubungi Kami</h4>
          <p class="mt-3"><strong>Telepon / WA:</strong> <a href="https://wa.me/6285317404760">+6285-3174-04760</a>
          </p>
          <p><strong>Email:</strong> <span>sewflow@shiftech.my.id</span></p>
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