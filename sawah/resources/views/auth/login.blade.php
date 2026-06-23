@extends('layouts.guest')

@section('content')
  <h2 style="margin-bottom:1rem;">تسجيل الدخول</h2>

  @if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  <div class="form-container">
    <form method="POST" action="{{ route('login') }}">
      @csrf

      <div class="form-group">
        <label for="email">البريد الإلكتروني</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
        @error('email') <div class="alert alert-error" style="margin-top:.5rem;">{{ $message }}</div> @enderror
      </div>

      <div class="form-group">
        <label for="password">كلمة المرور</label>
        <input id="password" type="password" name="password" required autocomplete="current-password">
        @error('password') <div class="alert alert-error" style="margin-top:.5rem;">{{ $message }}</div> @enderror
      </div>

      <div class="form-group" style="display:flex;align-items:center;gap:.5rem;">
        <input id="remember_me" type="checkbox" name="remember" style="width:auto;">
        <label for="remember_me" style="margin:0;">تذكرني</label>
      </div>

      <button class="btn btn-primary" type="submit">دخول</button>

      <div style="margin-top:1rem;display:flex;gap:1rem;flex-wrap:wrap;">
        @if (Route::has('password.request'))
          <a class="btn btn-secondary" href="{{ route('password.request') }}">نسيت كلمة المرور؟</a>
        @endif
        <a class="btn btn-secondary" href="{{ route('register') }}">إنشاء حساب جديد</a>
      </div>
    </form>
  </div>
@endsection
