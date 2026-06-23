<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'سُوّاح')</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="{{ asset('styles.css') }}"/>
<style>
  /* افتراضياً: صفحات SSR (التي تضع data-page على <body>) يجب أن تُظهر الأقسام */
  body[data-page] .section { display: block !important; }

  /* صفحة الرئيسية فقط (لا يوجد data-page) تستخدم منطق SPA */
  body:not([data-page]) .section { display: none; }
  body:not([data-page]) .section.active { display: block; }
  .trip-card { display:block; }
  .trip-image { min-height: 180px; background-size: cover; background-position: center; }
</style>
  <script>
window.appBase = "{{ url('') }}";
    @php
      $u = auth()->user();
      $jsUser = $u ? [
          'id'       => $u->id,
          'name'     => $u->name,
          'email'    => $u->email,
          'is_admin' => (bool) ($u->is_admin ?? ($u->role === 'admin')),
      ] : null;
    @endphp
    window.laravelUser = @json($jsUser, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    window.currentUser = window.laravelUser;
  </script>

  {{-- حمّل السكربت العام مرة واحدة فقط --}}
  <script src="{{ asset('script.js') }}?v=20" defer></script>
  @stack('head') {{-- لو احتجت أكواد رأسية إضافية لكل صفحة --}}
</head>

<body class="site-body" @yield('body-attrs')>
  @php
    $isAdmin = auth()->check() && ((auth()->user()->is_admin ?? false) || (auth()->user()->role === 'admin'));
  @endphp

  <header class="header">
    <div class="container nav">
      <a href="{{ route('home') }}" class="logo"><i class="fa-solid fa-plane-departure"></i> سوّاح</a>
    
      <ul class="nav-links">
        <li><a href="{{ route('home') }}">الرئيسية</a></li>
          <li><a href="{{ route('recommendations') }}">التوصيات</a></li>
        @unless($isAdmin)
          @auth
            <li><a href="{{ route('requests.index') }}">الطلبات</a></li>

            <li><a href="{{ route('bookings') }}">حجوزاتي</a></li>
            
          @endauth
        @endunless

        @if($isAdmin)
          <li><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
        @endif
      </ul>

      @guest
        <div class="auth-buttons">
          <a class="btn btn-primary" href="{{ route('login') }}">تسجيل الدخول</a>
          <a class="btn btn-secondary" href="{{ route('register') }}">إنشاء حساب</a>
        </div>
      @endguest
      @auth
      
        <div class="auth-buttons">
          <span class="welcome-message">مرحبًا، {{ Auth::user()->name }}</span>
          <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button class="btn btn-secondary" type="submit">تسجيل خروج</button>
          </form>
        </div>
      @endauth
    </div>
    
  </header>

  <main>
    @yield('content')
    {{-- {{ $slot ?? '' }}  // احذفها إذا لا تستخدم Components --}}
  </main>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')  {{-- علشان الصفحات تضيف سكريبتاتها --}}

</body>
</html>
