<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $company_name }} | Member Area</title>
  <meta name="description" content="Akses layanan member area eksklusif dari {{ $company_name }}. Cek riwayat belanja dan kelola pesanan Anda.">

  <!-- Load Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Menggunakan font Inter secara default */
    body {
      font-family: 'Inter', sans-serif;
    }

    /* Custom scroll-top button position, just in case */
    .scroll-top {
      position: fixed;
      bottom: 20px;
      right: 20px;
      z-index: 999;
    }
  </style>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'primary-blue': '#3B82F6',
            /* Blue 500 */
            'primary-dark': '#1F2937',
            /* Gray 800 */
          }
        }
      }
    }
  </script>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col">

  <!-- Header / Navbar -->
  <header class="sticky top-0 z-50 bg-white shadow-md">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
      <a class="text-xl font-bold text-primary-dark tracking-tight" href="./">
        {{ $company_name }}
      </a>

      <div class="hidden sm:flex space-x-6 items-center">
        <a class="text-gray-600 hover:text-primary-blue transition duration-200" href="#about">Tentang Kami</a>
        <a class="text-gray-600 hover:text-primary-blue transition duration-200" href="#contact">Hubungi Kami</a>
        <a class="bg-primary-blue hover:bg-blue-600 text-white font-semibold py-2 px-5 rounded-lg shadow-lg transition duration-300 transform hover:scale-105"
          href="{{ route('admin.auth.login') }}">
          Staff Area
        </a>
      </div>

      <button class="sm:hidden text-gray-700 hover:text-primary-blue focus:outline-none" id="mobile-menu-btn">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
        </svg>
      </button>
    </div>

    <div class="hidden sm:hidden bg-white border-t border-gray-100" id="mobile-menu">
      <div class="px-4 pt-2 pb-6 space-y-2">
        <a class="block py-2 text-gray-600 border-b border-gray-50" href="#about">Tentang Kami</a>
        <a class="block py-2 text-gray-600 border-b border-gray-50" href="#contact">Hubungi Kami</a>
        <div class="pt-4">
          <a class="block w-full text-center bg-primary-blue text-white font-semibold py-3 rounded-lg shadow-md" href="{{ route('admin.auth.login') }}">
            Staff Area
          </a>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="main flex-grow" id="about">

    <!-- Hero Section: POS Landing & Member Focus -->
    <section class="py-20 md:py-32 bg-white text-center">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
        <h1 class="text-4xl md:text-6xl font-extrabold text-primary-dark leading-tight mb-4 tracking-tighter">
          Selamat Datang di <span class="text-primary-blue">{{ $company_name }}</span>
        </h1>

        <p class="text-xl md:text-2xl text-gray-600 mb-8 max-w-3xl mx-auto">
          Akses riwayat belanja dan transaksi wallet Anda kapan saja dan di mana saja.
        </p>

        <!-- Prominent Call to Action (CTA) -->
        <div class="space-y-4 sm:space-y-0 sm:space-x-4 flex flex-col sm:flex-row justify-center">
          <a class="inline-block px-10 py-3 text-lg font-bold text-white bg-primary-blue rounded-xl shadow-2xl shadow-blue-400/50 hover:bg-blue-600 transition duration-300 transform hover:scale-[1.03] focus:outline-none focus:ring-4 focus:ring-primary-blue/50"
            href="{{ route('customer.auth.login') }}">
            Masuk ke Customer Area
          </a>
          {{-- <a class="inline-block px-10 py-3 text-lg font-bold text-white bg-primary-blue rounded-xl shadow-2xl shadow-blue-400/50 hover:bg-blue-600 transition duration-300 transform hover:scale-[1.03] focus:outline-none focus:ring-4 focus:ring-primary-blue/50"
            href="{{ route('admin.auth.login') }}">
            Masuk ke Staff Area
          </a> --}}
        </div>
      </div>
    </section>

    <!-- Features/Contact Teaser Section (optional but good for landing page) -->
    <section class="py-16 bg-gray-100" id="contact">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl text-center">
        <h2 class="text-3xl font-bold text-primary-dark mb-4">Butuh Bantuan atau Ada Pertanyaan?</h2>
        <p class="text-lg text-gray-600 mb-8">Tim kami siap membantu Anda seputar riwayat transaksi dan layanan
          member.</p>

        <!-- Contact Details -->
        <div class="flex flex-col md:flex-row justify-center space-y-6 md:space-y-0 md:space-x-12">
          <div class="text-center">
            <svg class="w-8 h-8 mx-auto text-primary-blue mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            <strong class="block text-primary-dark">Telepon / WA</strong>
            <a class="text-gray-600 hover:text-primary-blue" href="https://wa.me/{{ $company_phone }}">{{ $company_phone }}</a>
          </div>
          @if (isset($company_email))
            <div class="text-center">
              <svg class="w-8 h-8 mx-auto text-primary-blue mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-10 12h10a2 2 0 002-2V6a2 2 0 00-2-2H4a2 2 0 00-2 2v12a2 2 0 002 2z">
                </path>
              </svg>
              <strong class="block text-primary-dark">Email</strong>
              <span class="text-gray-600">{{ $company_email }}</span>
            </div>
          @endif
        </div>
      </div>
    </section>

  </main>

  <!-- Footer -->
  <footer class="mt-auto bg-primary-dark text-white py-8">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
      <p class="mb-2 text-sm text-gray-400">
        Tentang <a class="hover:text-primary-blue transition duration-200" href="{{ route('landing-page') }}">{{ config('app.name') }}
          {{ config('app.version_str') }}</a>
      </p>
      <p class="text-sm">
        &copy; {{ date('Y') }} <strong class="font-semibold">{{ $company_name }}</strong>. All Rights
        Reserved.
      </p>
      <p class="text-xs text-gray-500 mt-1">Powered by <a class="hover:text-primary-blue" href="https://shiftech.my.id">Shiftech Indonesia</a></p>
    </div>
  </footer>

  <!-- Scroll Top (Basic implementation using JS for visual) -->
  <a class="scroll-top bg-primary-blue text-white p-3 rounded-full shadow-lg hover:bg-blue-600 transition duration-300 flex items-center justify-center w-12 h-12" id="scroll-top"
    href="#" style="display: none;">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
    </svg>
  </a>

  <!-- Simple Scroll Top JS Logic -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {

      // Logic untuk Toggle Mobile Menu
      const menuBtn = document.getElementById('mobile-menu-btn');
      const mobileMenu = document.getElementById('mobile-menu');

      menuBtn.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
      });

      // Otomatis tutup menu saat link diklik (opsional)
      const mobileLinks = mobileMenu.querySelectorAll('a');
      mobileLinks.forEach(link => {
        link.addEventListener('click', () => {
          mobileMenu.classList.add('hidden');
        });
      });

      const scrollTopButton = document.getElementById('scroll-top');

      const toggleScrollTop = () => {
        if (window.scrollY > 100) {
          scrollTopButton.style.display = 'flex';
        } else {
          scrollTopButton.style.display = 'none';
        }
      };

      window.addEventListener('scroll', toggleScrollTop);
      toggleScrollTop(); // Check on load
    });
  </script>
</body>

</html>
