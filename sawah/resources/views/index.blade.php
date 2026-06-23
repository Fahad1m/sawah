@extends('layouts.app')

@section('content')

</header>

{{-- قبل: <main class="main-content container"> --}}
<main class="main-content">

  {{-- HERO full-bleed --}}
  <section class="hero hero-bleed">
    <div class="hero-content">
      <h1>اكتشف رحلتك القادمة</h1>
      <p>ابحث وفلتر بين الرحلات المتاحة</p>

      <div class="search-container">
        <div class="search-box">
          <input id="searchTrips" type="text" placeholder="ابحث عن وجهة...">
          <select id="filterDestination2"><option value="">كل الوجهات</option></select>
          <select id="filterPrice2">
            <option value="">كل الأسعار</option>
            <option value="low">منخفض (&lt; 1000)</option>
            <option value="medium">متوسط (1000–3000)</option>
            <option value="high">مرتفع (&gt; 3000)</option>
          </select>
        </div>
      </div>
    </div>
  </section>

  {{-- باقي الصفحة داخل container --}}
  <section class="container" style="margin-top:2rem">
    <h2 class="page-title">عروض مميزة</h2>
    <div id="featuredTrips" class="trips-grid" style="margin-bottom:1.5rem"></div>

    <h2 class="page-title" style="margin-top:8px">كل الرحلات</h2>
    <div id="allTrips" class="trips-grid" style="margin-top:1rem"></div>
    <div id="tripsPager" style="margin-top:1rem"></div>
  </section>
</main>

{{-- Book Trip Modal --}}
<div id="bookTripModal" class="modal" >
  <div class="modal-content">
    <button class="modal-close" onclick="closeModal('bookTripModal')">&times;</button>
    <h3>حجز رحلة</h3>
    <form id="bookTripForm">
      <input type="hidden" id="bookingTripId">
      <div class="form-group">
        <label>الوجهة</label>
        <input id="bookingDestination" type="text" readonly>
      </div>
      <div class="form-group">
        <label>التاريخ</label>
        <input id="bookingDate" type="date" required>
      </div>
      <div class="form-group">
        <label>عدد الأشخاص</label>
        <input id="bookingPeople" type="number" min="1" value="1" required>
      </div>
      <div class="form-group">
        <label>المبلغ (تقريبي)</label>
        <input id="bookingAmount" type="text" readonly>
      </div>
      <button class="btn btn-primary" type="submit">تأكيد الحجز</button>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // يجلب الرحلات ويرسمها تلقائياً إذا وجد الحاويات
    if (typeof bootstrapTrips === 'function') bootstrapTrips();

    // تفعيل نموذج الحجز والفلاتر
    if (typeof bindBookForm === 'function') bindBookForm();
    if (typeof bindFilters === 'function') bindFilters();
  });
</script>
@endpush
