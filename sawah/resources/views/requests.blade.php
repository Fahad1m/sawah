@extends('layouts.app')

@section('content')
<section class="section active">
  <div class="container">

    <div class="page-header">
      <h2 class="page-title">طلباتي</h2>
      <p class="page-subtitle">هنا تشاهد طلبات الإلغاء والتعديل وحالة كل طلب</p>
    </div>

    @if($requests->isEmpty())
      <p class="center" style="opacity:.8">لا توجد طلبات حتى الآن</p>
    @else
      <div class="filter-pills pills-lg" style="margin-bottom:12px">
        <span class="pill active" data-switch="all"><span class="label">الكل</span></span>
        <span class="pill" data-switch="pending"><span class="label">معلّق</span></span>
        <span class="pill" data-switch="approved"><span class="label">مقبول</span></span>
        <span class="pill" data-switch="rejected"><span class="label">مرفوض</span></span>
      </div>

      <div id="userRequestsList" class="trips-grid bookings-grid">
        @foreach($requests as $r)
          @php
            $trip  = optional($r->booking)->trip;
            $img   = $trip? ($trip->image ?? '') : '';
          @endphp
          <div class="trip-card fade-in" data-status="{{ $r->status }}" data-type="{{ $r->type }}">
            <div class="trip-image" style="background-image:url('{{ Str::startsWith($img, ['http','/']) ? $img : asset('storage/'.$img) }}')">
              <div class="trip-rating"><i class="fa fa-file-alt"></i> {{ $r->type === 'cancel' ? 'إلغاء' : 'تعديل' }}</div>
            </div>
            <div class="trip-content">
              <h3 class="trip-title">{{ $trip->destination ?? '—' }}</h3>

              <div class="trip-features" style="margin:.25rem 0 0.4rem">
                <span class="chip {{ $r->status==='pending' ? '' : ($r->status==='approved'?'chip-success':'chip-danger') }}">
                  {{ $r->status }}
                </span>
                @if($r->type==='modify')
                  @if($r->new_start_date)<span class="chip">تاريخ جديد: {{ $r->new_start_date }}</span>@endif
                  @if($r->new_people_count)<span class="chip">عدد جديد: {{ $r->new_people_count }}</span>@endif
                @endif
              </div>

              @if($r->status==='rejected' && $r->admin_reason)
                <div class="box-note" style="margin-top:6px">
                  <strong>سبب الرفض:</strong> {{ $r->admin_reason }}
                </div>
              @endif

              <p class="trip-description" style="margin-top:.5rem">
                @if($r->type==='cancel')
                  @if($r->reason_code) سبب الإلغاء: {{ $r->reason_code }} — @endif
                  {{ $r->note }}
                @else
                  @if($r->reason_code) سبب التعديل: {{ $r->reason_code }} — @endif
                  {{ $r->note }}
                @endif
              </p>
            </div>
          </div>
        @endforeach
      </div>
    @endif

  </div>
</section>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function(){
    const pills = document.querySelectorAll('.filter-pills [data-switch]');
    const cards = document.querySelectorAll('#userRequestsList .trip-card');
    if (!pills.length) return;
    pills.forEach(p=>p.addEventListener('click', ()=>{
      pills.forEach(x=>x.classList.remove('active'));
      p.classList.add('active');
      const st = p.getAttribute('data-switch');
      cards.forEach(c=>{
        c.style.display = (st === 'all' || c.getAttribute('data-status') === st) ? '' : 'none';
      });
    }));
  });
</script>
@endpush
@endsection
