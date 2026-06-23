@extends('layouts.guest')

@section('content')
  <h2 style="margin-bottom:1rem;">إنشاء حساب</h2>

  <div class="form-container">
    <form method="POST" action="{{ route('register') }}">
      @csrf

      <div class="form-group">
        <label for="name">الاسم</label>
        <input id="name" name="name" value="{{ old('name') }}" required autofocus>
        @error('name') <div class="alert alert-error" style="margin-top:.5rem;">{{ $message }}</div> @enderror
      </div>

      <div class="form-group">
        <label for="email">البريد الإلكتروني</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required>
        @error('email') <div class="alert alert-error" style="margin-top:.5rem;">{{ $message }}</div> @enderror
      </div>

      <div class="form-group">
        <label for="password">كلمة المرور</label>
        <input id="password" type="password" name="password" required autocomplete="new-password">
        @error('password') <div class="alert alert-error" style="margin-top:.5rem;">{{ $message }}</div> @enderror
      </div>

      <div class="form-group">
        <label for="password_confirmation">تأكيد كلمة المرور</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required>
      </div>

      <button class="btn btn-primary" type="submit">تسجيل</button>

      <div style="margin-top:1rem;">
        <a class="btn btn-secondary" href="{{ route('login') }}">لديّ حساب بالفعل</a>
      </div>
    </form>
  </div>
@endsection
