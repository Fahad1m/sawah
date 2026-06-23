@extends('layouts.app')

@section('content')
<section class="section active">
  <div class="container">

    {{-- عنوان الصفحة --}}
    <div class="page-header">
      <h2 class="page-title">حجوزاتي</h2>
    </div>

    {{-- شريط الفلاتر الجديد (أكبر وأوضح) --}}
    <div class="filters-wrap">
      <div id="bookingStatusPills" class="filter-pills pills-lg">
        <span class="pill active" data-status="">
          <span class="label">الكل</span>
          <span class="count">0</span>
        </span>
        <span class="pill" data-status="pending">
          <span class="label">معلّق</span>
          <span class="count">0</span>
        </span>
        <span class="pill" data-status="confirmed">
          <span class="label">مؤكد</span>
          <span class="count">0</span>
        </span>
        <span class="pill" data-status="cancelled">
          <span class="label">ملغي</span>
          <span class="count">0</span>
        </span>
      </div>
    </div>

    {{-- قائمة الحجوزات --}}
    <div id="userBookings" class="trips-grid bookings-grid"></div>
  </div>
</section>


{{-- Modal: طلب إلغاء --}}
<div id="cancelRequestModal" class="modal" aria-hidden="true">
  <div class="modal-content">
    <button class="modal-close" onclick="closeModal('cancelRequestModal')">&times;</button>
    <h3>طلب إلغاء الحجز</h3>
    <form id="cancelRequestForm">
      <input type="hidden" id="cancelBookingId">
      <div class="form-group">
        <label>السبب</label>
        <select id="cancelReason" required>
          <option value="تغير الخطط">تغير الخطط</option>
          <option value="مشكلة شخصية">مشكلة شخصية</option>
          <option value="حجز بالخطأ">حجز بالخطأ</option>
          <option value="سعر أعلى مما توقعت">سعر أعلى مما توقعت</option>
          <option value="سبب آخر">سبب آخر</option>
        </select>
      </div>
      <div class="form-group">
        <label>ملاحظة (اختياري)</label>
        <textarea id="cancelNote"></textarea>
      </div>
      <button class="btn btn-primary" type="submit">إرسال الطلب</button>
    </form>
  </div>
</div>

{{-- Modal: طلب تعديل --}}
<div id="modifyRequestModal" class="modal" aria-hidden="true">
  <div class="modal-content">
    <button class="modal-close" onclick="closeModal('modifyRequestModal')">&times;</button>
    <h3>طلب تعديل الحجز</h3>
    <form id="modifyRequestForm">
      <input type="hidden" id="modifyBookingId">
      <div class="form-group">
        <label>تاريخ جديد</label>
        <input id="modifyDate" type="date" required>
      </div>
      <div class="form-group">
        <label>عدد الأشخاص</label>
        <input id="modifyPeople" type="number" min="1" value="1" required>
      </div>
      <div class="form-group">
        <label>السبب</label>
        <select id="modifyReason" required>
          <option value="تغيير التاريخ">تغيير التاريخ</option>
          <option value="زيادة عدد الأشخاص">زيادة عدد الأشخاص</option>
          <option value="مطلوب تفاصيل إضافية">مطلوب تفاصيل إضافية</option>
          <option value="سبب آخر">سبب آخر</option>
        </select>
      </div>
      <div class="form-group">
        <label>ملاحظة (اختياري)</label>
        <textarea id="modifyNote"></textarea>
      </div>
      <button class="btn btn-primary" type="submit">إرسال الطلب</button>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof loadMyBookings === 'function') loadMyBookings();
    if (typeof bindBookingFilters === 'function') bindBookingFilters();
    if (typeof bindCancelModifyForms === 'function') bindCancelModifyForms();
  });
</script>
@endpush
