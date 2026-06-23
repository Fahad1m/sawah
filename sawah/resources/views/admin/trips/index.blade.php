@extends('layouts.app')

@section('content')
<section class="section active">
  <div class="container admin-panel">

    {{-- كروت الإحصائيات --}}
    <div class="stats-grid" style="display:grid;gap:14px">
      <a href="{{ route('admin.trips.manage') }}" class="stat-card sky" style="display:block;text-decoration:none">
        <div class="stat-title">عدد الرحلات</div>
        <div class="stat-number">{{ $stats['tripsCount'] }}</div>
      </a>
      <div class="stat-card sky">
        <div class="stat-title">عدد المستخدمين</div>
        <div class="stat-number">{{ $stats['usersCount'] }}</div>
      </div>
      <div class="stat-card sky">
        <div class="stat-title">الحجوزات المعلّقة</div>
        <div class="stat-number" data-stat-pending>{{ $stats['pendingBookingsCount'] }}</div>
      </div>
      <div class="stat-card sky">
        <div class="stat-title">طلبات التعديل/الإلغاء المعلّقة</div>
        <div class="stat-number">{{ $stats['pendingRequestsCount'] }}</div>
      </div>
    </div>

    <hr style="border-color:#2d2d42;margin:14px 0 10px">

    {{-- القسم 1: الحجوزات المعلّقة (قبول/رفض فقط) --}}
    <h2 class="section-title" style="margin-top:6px">الحجوزات المعلّقة</h2>
    @forelse($pendingBookings as $bk)
      <div class="admin-req-card" id="bk-{{ $bk->id }}">
        <div class="admin-req-head" style="display:flex;justify-content:space-between;align-items:center">
          <span class="badge badge-pending">Pending</span>
          <div class="admin-req-actions" style="display:flex;gap:8px">
            <form onsubmit="return false;">
              <button class="btn-approve" data-approve-booking="{{ $bk->id }}">قبول</button>
            </form>
            <form onsubmit="return false;">
              <button class="btn-reject" data-reject-booking="{{ $bk->id }}">رفض</button>
            </form>
          </div>
        </div>
        <div class="admin-req-meta" style="margin-top:10px;display:grid;gap:6px;grid-template-columns:repeat(2,minmax(180px,1fr))">
          <div><span class="badge-type">المستخدم</span> {{ $bk->user->name ?? 'غير معروف' }}</div>
          <div><span class="badge-type">الرحلة</span> {{ $bk->trip->destination ?? '—' }}</div>
          <div><span class="badge-type">التاريخ</span> {{ $bk->start_date }}</div>
          <div><span class="badge-type">عدد الأشخاص</span> {{ $bk->people_count }}</div>
          <div><span class="badge-type">المبلغ</span> {{ number_format($bk->amount ?? 0) }} ر.س</div>
        </div>
      </div>
    @empty
      <p style="opacity:.7">لا توجد حجوزات معلّقة.</p>
    @endforelse

    <hr style="border-color:#2d2d42;margin:16px 0 10px">

    {{-- القسم 2: طلبات التعديل/الإلغاء (مع سبب رفض) --}}
    <h2 class="section-title">طلبات التعديل/الإلغاء</h2>
    @forelse($pendingRequests as $req)
      <div class="admin-req-card" id="req-{{ $req->id }}">
        <div class="admin-req-head" style="display:flex;justify-content:space-between;align-items:center">
          <span class="badge badge-pending">{{ ucfirst($req->type) }}</span>
          <div class="admin-req-actions" style="display:flex;gap:8px">
            <button class="btn-approve" data-approve="{{ $req->id }}">قبول</button>
            <button class="btn-reject"  data-reject="{{ $req->id }}">رفض</button>
          </div>
        </div>

        <div class="admin-req-meta" style="margin-top:10px;display:grid;gap:6px;grid-template-columns:repeat(2,minmax(200px,1fr))">
          <div><span class="badge-type">المستخدم</span> {{ $req->user->name ?? 'غير معروف' }}</div>
          <div><span class="badge-type">الرحلة</span> {{ $req->booking->trip->destination ?? '—' }}</div>

          @if($req->type === 'modify')
            <div><span class="badge-type">تاريخ جديد</span> {{ $req->new_start_date ?? '—' }}</div>
            <div><span class="badge-type">عدد جديد</span> {{ $req->new_people_count ?? '—' }}</div>
          @endif

          @if(!empty($req->reason_code))
            <div><span class="badge-type">السبب</span> {{ $req->reason_code }}</div>
          @endif

          @if(!empty($req->note))
            <div style="grid-column:1/-1">
              <span class="badge-type">ملاحظة العميل</span>
              <div class="box-note">{{ $req->note }}</div>
            </div>
          @endif
        </div>
      </div>
    @empty
      <p style="opacity:.7">لا توجد طلبات تعديل/إلغاء حالياً.</p>
    @endforelse

  </div>
</section>

{{-- مودال سبب الرفض لطلبات التعديل/الإلغاء --}}
<div id="rejectReasonModal" class="modal" aria-hidden="true">
  <div class="modal-content">
    <button class="modal-close" onclick="closeModal('rejectReasonModal')">&times;</button>
    <h3>سبب الرفض</h3>
    <form id="rejectReasonForm">
      <input type="hidden" id="rejectRequestId">
      <div class="form-group">
        <textarea id="rejectReasonText" rows="4" placeholder="اذكر سبب الرفض..." required></textarea>
      </div>
      <button class="btn btn-reject" type="submit">رفض الطلب</button>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function(){

    // ===== قبول/رفض الحجوزات (bookings) =====
    const BASE = (window.appBase || '').replace(/\/+$/, '');
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
    const pendingNum = document.querySelector('[data-stat-pending]');

    function fadeOutAndDec(el){
      if (!el) return;
      el.style.transition = 'opacity .25s, transform .25s, height .25s, margin .25s, padding .25s';
      el.style.opacity = '0';
      el.style.transform = 'translateY(-6px)';
      setTimeout(()=>{ el.style.height='0'; el.style.margin='0'; el.style.padding='0'; }, 80);
      setTimeout(()=> el.remove(), 300);
      if (pendingNum){
        const n = parseInt((pendingNum.textContent||'0').replace(/[^\d]/g,''),10)||0;
        pendingNum.textContent = Math.max(0, n-1);
      }
    }

    document.querySelectorAll('[data-approve-booking]').forEach(btn=>{
      if (btn.__bound) return; btn.__bound = true;
      btn.addEventListener('click', async ()=>{
        const id = btn.getAttribute('data-approve-booking');
        btn.disabled = true;
        const r = await fetch(`${BASE}/admin/bookings/${id}/approve`, {
          method:'POST', headers:{'X-CSRF-TOKEN': csrf(), 'Accept':'application/json'}, credentials:'same-origin'
        });
        if (r.ok) fadeOutAndDec(document.getElementById(`bk-${id}`)); else btn.disabled=false;
      });
    });

    document.querySelectorAll('[data-reject-booking]').forEach(btn=>{
      if (btn.__bound) return; btn.__bound = true;
      btn.addEventListener('click', async ()=>{
        const id = btn.getAttribute('data-reject-booking');
        btn.disabled = true;
        const r = await fetch(`${BASE}/admin/bookings/${id}/reject`, {
          method:'POST', headers:{'X-CSRF-TOKEN': csrf(), 'Accept':'application/json'}, credentials:'same-origin'
        });
        if (r.ok) fadeOutAndDec(document.getElementById(`bk-${id}`)); else btn.disabled=false;
      });
    });

    // ===== طلبات التعديل/الإلغاء (approve / reject with reason) =====
    if (typeof bindAdminRequestActions === 'function') {
      bindAdminRequestActions(); // نفس الدالة اللي أعطيتك إياها سابقًا
    }
  });
</script>
@endpush
