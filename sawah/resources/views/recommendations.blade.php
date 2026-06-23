@extends('layouts.app')

@section('content')
<section class="section active">
  <div class="container">

    <div class="page-header">
      <h2 class="page-title">التوصيات</h2>
      <p class="page-subtitle">قيّم رحلاتك بعد انتهائها وشاهد توصيات الآخرين</p>
    </div>

    @if($rateableTrips->count())
      <form class="form-container" method="POST" action="{{ route('recommendations.store') }}" style="margin-bottom:18px">
        @csrf

        <div class="form-group">
          <label>اختر الرحلة التي تريد تقييمها</label>
          <select name="trip_id" required>
            @foreach($rateableTrips as $item)
              <option value="{{ $item['trip_id'] }}">{{ $item['label'] }}</option>
            @endforeach
          </select>
          @error('trip_id') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        {{-- النجوم (SVG) --}}
<label class="form-label" style="display:block;margin:.5rem 0 .25rem">التقييم</label>

<div class="rating-stars" data-input="#recRating" aria-label="تقييم بالنجوم" role="group" dir="rtl">
  @for ($i = 1; $i <= 5; $i++)
    <button type="button"
            class="star"
            data-value="{{ $i }}"
            aria-label="{{ $i }} نجوم"
            aria-pressed="false">
      <svg viewBox="0 0 24 24" width="32" height="32" class="star-svg" aria-hidden="true">
        <defs>
          <linearGradient id="grad-cyan" x1="0" x2="1">
            <stop offset="0%" stop-color="#3aa0ff"/>
            <stop offset="100%" stop-color="#00d4ff"/>
          </linearGradient>
        </defs>
        <path class="stroke" fill="none" stroke="url(#grad-cyan)" stroke-width="1.6"
              d="M12 17.3l-5.4 3.2 1.4-6.1L3 9.7l6.2-.6L12 3l2.8 6.1 6.2.6-5 4.7 1.4 6.1z"/>
        <path class="fill" fill="url(#grad-cyan)" opacity="0"
              d="M12 17.3l-5.4 3.2 1.4-6.1L3 9.7l6.2-.6L12 3l2.8 6.1 6.2.6-5 4.7 1.4 6.1z"/>
      </svg>
    </button>
  @endfor
</div>

<input type="hidden" id="recRating" name="rating" value="5">

        <div class="form-group">
          <label>اكتب رأيك</label>
          <textarea name="text" rows="3" required minlength="5" maxlength="2000" placeholder="ما الذي أعجبك/لم يعجبك؟"></textarea>
          @error('text') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <button class="btn btn-primary" type="submit">إرسال التقييم</button>
      </form>
    @else
      <div class="info-box" style="margin-bottom:18px">
        لا توجد رحلات متاحة لديك للتقييم الآن.
      </div>
    @endif

    {{-- قائمة كل التوصيات --}}
    <h3 class="page-title" style="margin-top:10px">جميع التوصيات</h3>
    @if($recommendations->count())
      <div class="trips-grid">
        @foreach($recommendations as $rec)
          <div class="trip-card">
            <div class="trip-content">
              <h3 class="trip-title">{{ $rec->trip->destination ?? '—' }}</h3>
              <div class="trip-features" style="display:flex; gap:8px; align-items:center">
                {{-- عرض النجوم حسب التقييم --}}
                <div class="stars-view" dir="ltr" style="display:flex; gap:4px">
                  @for($i=1; $i<=5; $i++)
                    <svg width="18" height="18" viewBox="0 0 24 24"
                         fill="{{ $i <= $rec->rating ? '#FFD166' : 'none' }}"
                         stroke="{{ $i <= $rec->rating ? '#FFD166' : '#9fb0d1' }}"
                         stroke-width="1.4">
                      <path d="M12 2l2.9 6.26L22 9.27l-5 4.86L18.2 22 12 18.77 5.8 22 7 14.13l-5-4.86 7.1-1.01L12 2z"/>
                    </svg>
                  @endfor
                </div>
                <span class="chip">{{ $rec->user->name ?? 'مستخدم' }}</span>
              </div>
              @if($rec->text)
                <p class="trip-description" style="margin-top:.6rem">{{ $rec->text }}</p>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @else
      <p class="center" style="opacity:.8">لا توجد توصيات بعد.</p>
    @endif

  </div>
</section>
@endsection

@push('scripts')
<script>
(function(){
  function bindStarRating(scope=document){
    const widgets = scope.querySelectorAll('.rating-stars');
    widgets.forEach(w=>{
      if (w.__bound) return; w.__bound = true;
      const inputSel = w.getAttribute('data-input') || '#recRating';
      const input = document.querySelector(inputSel);

   const stars = Array.from(w.querySelectorAll('.star'));


      function setVal(n){
        if (input) input.value = n;
        stars.forEach((s, i)=> s.classList.toggle('is-on', i < n));
        // ARIA
        stars.forEach((s, i)=> s.setAttribute('aria-pressed', i < n ? 'true' : 'false'));
      }

      stars.forEach(btn=>{
        btn.addEventListener('mouseenter', ()=> setVal(+btn.dataset.value));
        btn.addEventListener('click',      ()=> setVal(+btn.dataset.value));
        btn.addEventListener('keydown', (e)=>{
          // الأسهم لزيادة/نقصان (RTL)
          let v = +(input?.value || 5);
          if (e.key === 'ArrowLeft')  v = Math.min(5, v + 1);
          if (e.key === 'ArrowRight') v = Math.max(1, v - 1);
          if (e.key === 'Home') v = 1;
          if (e.key === 'End')  v = 5;
          setVal(v);
        });
      });

      setVal(+input?.value || 5);
    });
  }

  document.addEventListener('DOMContentLoaded', ()=> bindStarRating());
  window.bindStarRating = bindStarRating;
})();
</script>
@endpush

