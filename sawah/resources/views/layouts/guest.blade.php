<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>سُوّاح — الحساب</title>
  {{-- @vite(['resources/css/app.css','resources/js/app.js']) --}}

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="{{ asset('styles.css') }}">
</head>
<body>
  <header class="header">
    <div class="container">
      <nav class="nav">
        <a href="{{ url('/') }}" class="logo"><i class="fa-solid fa-plane-departure"></i> سُوّاح</a>
        <ul class="nav-links">
          <li><a href="{{ url('/') }}">الرئيسية</a></li>
        </ul>
        <div class="auth-buttons">
          @guest
            <a class="btn btn-secondary" href="{{ route('login') }}">تسجيل الدخول</a>
            <a class="btn btn-primary"   href="{{ route('register') }}">إنشاء حساب</a>
          @else
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
              @csrf
              <button class="btn btn-primary" type="submit">تسجيل الخروج</button>
            </form>
          @endguest
        </div>
      </nav>
    </div>
  </header>

  <main class="main-content">
    <section class="section active">
      <div class="container">
        @yield('content')
      </div>
    </section>
  </main>
</body>
</html>
