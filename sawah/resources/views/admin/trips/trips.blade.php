@extends('layouts.app')

@section('content')
<section class="section active">
  <div class="container">

    <div class="page-header">
      <h2 class="page-title">إدارة الرحلات</h2>
      <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary" style="margin-top:8px">← رجوع للوحة</a>
    </div>

    {{-- إضافة رحلة --}}
    <form class="form-container" method="POST" action="{{ route('admin.trips.store') }}" style="margin:12px 0 22px">
      @csrf
      <div class="grid-2">
        <div class="form-group">
          <label>الوجهة</label>
          <input type="text" name="destination" required>
        </div>
        <div class="form-group">
          <label>السعر (ر.س)</label>
          <input type="number" name="price" min="0" required>
        </div>
                <div class="form-group">
          <label>المدة (يوم)</label>
          <input type="number" name="duration" min="0" required>
        </div>
      </div>
      <div class="form-group">
        <label>رابط صورة (اختياري)</label>
        <input type="file" name="image" accept="image/png, image/gif, image/jpeg" />
      </div>
      <div class="form-group">
        <label>الوصف</label>
        <textarea name="description" rows="3"></textarea>
      </div>
      <button class="btn btn-primary" type="submit">إضافة الرحلة</button>
    </form>

    {{-- قائمة الرحلات --}}
    <table class="data-table">
      <thead>
        <tr><th>الوجهة</th><th>السعر</th><th>صورة</th><th style="width:120px">إجراءات</th></tr>
      </thead>
      <tbody>
        @forelse($trips as $trip)
          <tr>
            <td>{{ $trip->destination }}</td>
            <td>{{ number_format($trip->price) }} ر.س</td>
            <td>
              @if($trip->image)
                <img src="{{ \Illuminate\Support\Str::startsWith($trip->image,['http','/']) ? $trip->image : asset('storage/'.$trip->image) }}"
                     alt="" style="width:72px;border-radius:8px">
              @endif
            </td>
            <td>
              <form method="POST" action="{{ route('admin.trips.destroy',$trip) }}"
                    onsubmit="return confirm('حذف الرحلة؟')">
                @csrf @method('DELETE')
                <button class="btn btn-reject" type="submit">حذف</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" style="opacity:.7">لا توجد رحلات حالياً.</td></tr>
        @endforelse
      </tbody>
    </table>

    <div style="margin-top:12px">{{ $trips->links() }}</div>
  </div>
</section>
@endsection
