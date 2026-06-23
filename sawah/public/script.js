
(() => {
  'use strict';

  // ---------- Helpers ----------
  const BASE = (window.appBase || '').replace(/\/+$/, '');
  const csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const authUser = () => window.laravelUser || null;
  const $  = (sel, p=document) => p.querySelector(sel);
  const $$ = (sel, p=document) => Array.from(p.querySelectorAll(sel));
  const fmt = (n, digits=0) => Number(n||0).toLocaleString('ar-EG', { minimumFractionDigits: digits });
const PLACEHOLDER = 'https://picsum.photos/800/450?blur=1';
const safeImg = (url) => {
  if (!url) return PLACEHOLDER;
  if (/^https?:\/\//i.test(url)) return url;                
  if (url.startsWith('/')) return `${BASE}${url}`;          
  if (url.startsWith('storage/') || url.startsWith('uploads/') || url.startsWith('images/'))
    return `${BASE}/${url}`;
  if (url.startsWith('trips/'))                             
    return `${BASE}/storage/${url}`;
  return `${BASE}/storage/${url}`;
};
const getDest = (t) =>
  t?.destination ?? t?.title ?? t?.name ?? t?.trip?.destination ?? '';
const getDesc = (t) =>
  t?.description ?? t?.details ?? t?.trip?.description ?? '';
const getPrice = (t) =>
  Number(t?.price ?? t?.amount ?? t?.trip?.price ?? 0);

const BOOKING_STATUS = {
  pending:   { text: 'قيد المعالجة', cls: 'status-pending',   icon: 'fa-hourglass-half' },
  confirmed: { text: 'مؤكّد',        cls: 'status-confirmed', icon: 'fa-check-circle'  },
  cancelled: { text: 'ملغي',         cls: 'status-cancelled', icon: 'fa-times-circle'  },
  rejected:  { text: 'مرفوض',       cls: 'status-cancelled', icon: 'fa-times-circle'  },
};

  // ---------- TRIPS (Home) ----------
  async function fetchTripsGeneric(url) {
    const r = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials:'same-origin' });
    if (!r.ok) throw new Error('HTTP '+r.status);
    const json = await r.json();
    if (Array.isArray(json)) return json;
    if (Array.isArray(json?.data)) return json.data;
    if (Array.isArray(json?.trips)) return json.trips;
    if (Array.isArray(json?.items)) return json.items;
    if (Array.isArray(json?.data?.data)) return json.data.data;
    return [];
  }

  async function fetchTrips() {
    const candidates = [];
    if (BASE) candidates.push(`${BASE}/api/trips`);
    candidates.push('/api/trips');
    let lastErr = null;
    for (const u of candidates) {
      try { return await fetchTripsGeneric(u); }
      catch (e) { lastErr = e; }
    }
    console.warn('fetchTrips failed:', lastErr);
    return [];
  }

  function tripCardHtml(trip){
    const dest   = getDest(trip) || '—';
    const desc   = getDesc(trip) ;
    const priceN = getPrice(trip) ;
    const rating = trip.rating ?? '4.5';
    const img    = safeImg(trip.image || trip.image_url || trip.photo || trip.cover || '');

    const features = Array.isArray(trip.features) ? trip.features
                  : Array.isArray(trip.tags) ? trip.tags : [];
    const featuresHtml = features.slice(0,3).map(f=>`<span class="chip">${f}</span>`).join('');

    return `
      <div class="trip-card fade-in">
        <div class="trip-image" style="background-image:url('${img}')">
          <div class="trip-rating"><i class="fas fa-star"></i> ${rating}</div>
        </div>
        <div class="trip-content">
          <h3 class="trip-title">${dest}</h3>
          <p class="trip-description">${desc}</p>
          <div class="trip-price">${fmt(priceN)} ريال</div>
          <div class="trip-features">${featuresHtml}</div>
          <button class="book-btn" data-trip="${trip.id ?? trip.trip_id ?? ''}">حجز فوري</button>
        </div>
      </div>
    `;
  }

  function attachBookHandlers(scope, trips){
    scope.querySelectorAll('.book-btn[data-trip]').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const id = btn.getAttribute('data-trip');
        const trip = (trips || []).find(t => String(t.id)===(id));
        if (trip) openBookModal(trip);
      });
    });
  }

function renderFeatured(box, trips, state = {}){
  if (!box) return;
  const filtered = applyFilters(trips, state);
  const items = filtered.slice(0, 3);
  box.innerHTML = items.length ? items.map(tripCardHtml).join('') :
    `<p class="center" style="opacity:.8">لا توجد رحلات مطابقة للبحث</p>`;
}


  function applyFilters(trips, state){
  let list = [...trips];
  const q = (state?.q || '').trim().toLowerCase();

  if (q){
    list = list.filter(t => {
      const dest = (t.destination || t.title || t.name || '').toLowerCase();
      const desc = (t.description || t.details || '').toLowerCase();
      return dest.includes(q) || desc.includes(q);
    });
  }
  if (state?.dest){
    list = list.filter(t => (t.destination || '').toLowerCase().includes(String(state.dest).toLowerCase()));
  }
  if (state?.price){
    list = list.filter(t=>{
      const p = Number(t.price ?? t.amount ?? t.cost ?? 0);
      if (state.price==='low')    return p < 1000;
      if (state.price==='medium') return p >= 1000 && p <= 3000;
      if (state.price==='high')   return p > 3000;
      return true;
    });
  }
  return list;
}
function renderAllTrips(box, trips, pagerBox, state = {}){
  if (!box) return;

  const filtered = applyFilters(trips, state);

  const pageSize = 6;
  const currentPage = Number(box.getAttribute('data-page') || 1);
  const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));

  let page = currentPage;
  if (page > totalPages) page = totalPages;
  if (page < 1) page = 1;

  box.setAttribute('data-page', page);

  const start = (page - 1) * pageSize;
  const items = filtered.slice(start, start + pageSize);

  if (!items.length){
    box.innerHTML = `
      <p class="center" style="opacity:.8; margin-top:20px;">
        لا توجد رحلات مطابقة للبحث أو الفلاتر.
      </p>`;
  } else {
    box.innerHTML = items.map(tripCardHtml).join('');
  }

  attachBookHandlers(box, filtered);

  if (pagerBox){
    pagerBox.innerHTML = "";
    if (totalPages > 1){
      for (let p = 1; p <= totalPages; p++){
        const btn = document.createElement('button');
        btn.textContent = p;
        btn.className = (p === page) ? "pager-btn active" : "pager-btn";
        btn.addEventListener('click', ()=>{
          box.setAttribute('data-page', p);
          renderAllTrips(box, trips, pagerBox, state);
        });
        pagerBox.appendChild(btn);
      }
    }
  }
}

function bindFilters(){
  const sInput = document.getElementById('searchTrips');       
  const fDest  = document.getElementById('filterDestination2'); 
  const fPrice = document.getElementById('filterPrice2');     
  const pager  = document.getElementById('tripsPager');
  const featured = document.getElementById('featuredTrips');
  const allBox   = document.getElementById('allTrips');

  const state = { q:'', dest:'', price:'' };
function rerender(){
  const trips = window._trips || [];

  renderFeatured(featured, trips, state);
  attachBookHandlers(featured, trips); 
  
  renderAllTrips(allBox, trips, pager, state);
}


  if (sInput && !sInput.__bound){
    sInput.__bound = true;
    let t;
    sInput.addEventListener('input', ()=>{
      clearTimeout(t);
      t = setTimeout(()=>{
        state.q = sInput.value || '';
        allBox?.removeAttribute('data-page'); 
        rerender();
      }, 200);
    });
  }

  if (fDest && !fDest.__bound){
    fDest.__bound = true;
    fDest.addEventListener('change', ()=>{
      state.dest = fDest.value || '';
      allBox?.removeAttribute('data-page');
      rerender();
    });
  }

  if (fPrice && !fPrice.__bound){
    fPrice.__bound = true;
    fPrice.addEventListener('change', ()=>{
      state.price = fPrice.value || '';
      allBox?.removeAttribute('data-page');
      rerender();
    });
  }
}


  function openBookModal(trip){
    const m = $('#bookTripModal'); if(!m) return;
    $('#bookingTripId')?.setAttribute('value', trip.id);
    $('#bookingDestination') && ($('#bookingDestination').value = trip.destination || '');
    $('#bookingPeople') && ($('#bookingPeople').value = 1);
    $('#bookingAmount') && ($('#bookingAmount').value = fmt(Number(trip.price||0)));
    $('#bookingDate') && ($('#bookingDate').value = '');
    m.classList.add('show'); m.setAttribute('aria-hidden','false');
  }
  function closeModal(id){
    const m = document.getElementById(id);
    if(!m) return;
    m.classList.remove('show'); m.setAttribute('aria-hidden','true');
  }
  window.closeModal = closeModal;

  function bindBookForm(){
    const form = $('#bookTripForm');
    if(!form || form.__bound) return; form.__bound = true;
    form.addEventListener('submit', async (e)=>{
      e.preventDefault();
      if(!authUser()){ window.location.href = `${BASE}/login`; return; }
      const payload = {
        trip_id: $('#bookingTripId')?.value,
        start_date: $('#bookingDate')?.value,
        people_count: Number($('#bookingPeople')?.value || 1),
      }; 
      if(!payload.start_date){ showToast('الرجاء اختيار التاريخ','error'); return; }
      try{
        const r = await fetch(`${BASE}/bookings`, {
          method:'POST',
          headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf(), 'Accept':'application/json' },
          credentials:'same-origin',
          body: JSON.stringify(payload),
        });
        if(r.status===201 || r.ok){
          showToast('تم إرسال الحجز بنجاح 🎉','success');
          closeModal('bookTripModal');
        }else{
          console.warn('booking failed', await r.text());
          showToast('تعذّر إرسال الحجز','error');
        }
      }catch(err){ console.error(err); showToast('حدث خطأ أثناء الإرسال','error'); }
    });
  }

  async function bootstrapTrips(){
    const featured = $('#featuredTrips');
    const allBox   = $('#allTrips');
    if (!featured && !allBox) return; 

    const trips = await fetchTrips();
    window._trips = trips;
    populateDestinations(trips);
    renderFeatured(featured, trips);
    renderAllTrips(allBox, trips, $('#tripsPager'), { q:'', dest:'', price:'' });
  }

  // ---------- MY BOOKINGS ----------
function bookingCardTemplate(b) {
  const t = b.trip || {};
  const img = safeImg(t.image);
  const price = fmt(t.price);
  const people = Number(b.people || 1);
  const date = b.start_date || '';

  const statusKey = (b.status || 'pending').toLowerCase();
  const sm = BOOKING_STATUS[statusKey] || BOOKING_STATUS.pending;

  return `
    <div class="trip-card fade-in" data-status="${statusKey}">
      <div class="trip-image" style="background-image:url('${img}')">
        <div class="trip-rating"><i class="fa fa-user"></i> ${people}</div>
      </div>
      <div class="trip-content">
        <h3 class="trip-title">${t.destination || '—'}</h3>
        <div class="trip-price">${price} ريال</div>

        <div class="trip-features">
          <span class="chip">${date || ''}</span>
          <span class="status-badge ${sm.cls}">
            <i class="fa ${sm.icon}"></i>
            ${sm.text}
          </span>
        </div>

        <div style="display:flex; gap:8px; flex-wrap:wrap;">
          <button class="btn btn-secondary" data-cancel="${b.id}">طلب إلغاء</button>
          <button class="btn btn-primary"  data-modify="${b.id}">طلب تعديل</button>
        </div>
      </div>
    </div>
  `;
}


  function renderMyBookings(list){
    const cont = $('#userBookings'); if(!cont) return;
    cont.innerHTML = list.map(bookingCardTemplate).join('');
    bindBookingActionButtons(cont);
  }

  function calcBookingCounts(list){
    const c = { all:list.length, pending:0, confirmed:0, cancelled:0 };
    list.forEach(b=>{
      const s = (b.status||'pending').toLowerCase();
      if(s==='pending') c.pending++;
      else if(s==='confirmed') c.confirmed++;
      else if(s==='cancelled' || s==='rejected') c.cancelled++;
    });
    return c;
  }

  function updateBookingCounters(cnt){
    $$('#bookingStatusPills .pill').forEach(p=>{
      const st = p.getAttribute('data-status') || '';
      const el = p.querySelector('.count'); if(!el) return;
      if(st==='') el.textContent = cnt.all;
      else if(st==='pending') el.textContent = cnt.pending;
      else if(st==='confirmed') el.textContent = cnt.confirmed;
      else if(st==='cancelled') el.textContent = cnt.cancelled;
    });
  }

  function bindBookingFilters(){
    const pills = $$('#bookingStatusPills .pill');
    if(!pills.length || bindBookingFilters.__bound) return;
    bindBookingFilters.__bound = true;
    pills.forEach(btn=>{
      btn.addEventListener('click', ()=>{
        pills.forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        const st = btn.getAttribute('data-status') || '';
        const all = window._myBookings || [];
        const filtered = st ? all.filter(b => (b.status||'pending').toLowerCase()===st) : all;
        renderMyBookings(filtered);
      });
    });
  }

  function bindBookingActionButtons(scope){
    scope.querySelectorAll('[data-cancel]').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const id = btn.getAttribute('data-cancel');
        $('#cancelBookingId') && ($('#cancelBookingId').value = id);
        const m = $('#cancelRequestModal');
        if (m) { m.classList.add('show'); m.setAttribute('aria-hidden','false'); }
      });
    });
    scope.querySelectorAll('[data-modify]').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const id = btn.getAttribute('data-modify');
        $('#modifyBookingId') && ($('#modifyBookingId').value = id);
        const m = $('#modifyRequestModal');
        if (m) { m.classList.add('show'); m.setAttribute('aria-hidden','false'); }
      });
    });
  }

  async function loadMyBookings(){
    const cont = $('#userBookings'); if(!cont) return;
    cont.innerHTML = `<p class="center" style="opacity:.8">جارِ التحميل…</p>`;
    try{
      const r = await fetch(`${BASE}/bookings/mine`, {
        headers:{ 'Accept':'application/json' },
        credentials:'same-origin'
      });
      const ct = (r.headers.get('content-type') || '').toLowerCase();
      if (ct.includes('text/html')) { window.location.href = `${BASE}/login`; return; }
      if (!r.ok){ cont.innerHTML = `<p class="center" style="opacity:.8">تعذّر جلب البيانات</p>`; return; }
      const data = await r.json();
      const list = Array.isArray(data) ? data : (data.data || []);
      if(!list.length){
        cont.innerHTML = `<p class="center" style="opacity:.8">لا توجد حجوزات حتى الآن</p>`;
        updateBookingCounters({all:0,pending:0,confirmed:0,cancelled:0});
        return;
      }
      window._myBookings = list;
      renderMyBookings(list);
      updateBookingCounters(calcBookingCounts(list));
    }catch(e){ console.warn('loadMyBookings error', e); cont.innerHTML = `<p class="center" style="opacity:.8">تعذّر جلب البيانات</p>`; }
  }

  function bindCancelModifyForms(){
    const cancelForm = $('#cancelRequestForm');
    if(cancelForm && !cancelForm.__bound){
      cancelForm.__bound = true;
      cancelForm.addEventListener('submit', async (e)=>{
        e.preventDefault();
        if(!authUser()){ window.location.href = `${BASE}/login`; return; }
        const bookingId = $('#cancelBookingId')?.value;
        const payload = { reason: $('#cancelReason')?.value || '', note: $('#cancelNote')?.value || '' };
        try{
          const r = await fetch(`${BASE}/bookings/${bookingId}/cancel-request`, {
            method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf(),'Accept':'application/json'},
            credentials:'same-origin', body: JSON.stringify(payload)
          });
          if(r.ok){ showToast('تم إرسال طلب الإلغاء','success'); closeModal('cancelRequestModal'); loadMyBookings(); }
          else{ showToast('تعذّر إرسال الطلب','error'); }
        }catch(err){ console.error(err); showToast('خطأ أثناء الإرسال','error'); }
      });
    }

    const modifyForm = $('#modifyRequestForm');
    if(modifyForm && !modifyForm.__bound){
      modifyForm.__bound = true;
      modifyForm.addEventListener('submit', async (e)=>{
        e.preventDefault();
        if(!authUser()){ window.location.href = `${BASE}/login`; return; }
        const bookingId = $('#modifyBookingId')?.value;
        const payload = {
          new_date:   $('#modifyDate')?.value || '',
          new_people: Number($('#modifyPeople')?.value || 1),
          reason:     $('#modifyReason')?.value || '',
          note:       $('#modifyNote')?.value || ''
        };
        if(!payload.new_date){ showToast('الرجاء اختيار التاريخ الجديد','error'); return; }
        try{
          const r = await fetch(`${BASE}/bookings/${bookingId}/modify-request`, {
            method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf(),'Accept':'application/json'},
            credentials:'same-origin', body: JSON.stringify(payload)
          });
          if(r.ok){ showToast('تم إرسال طلب التعديل','success'); closeModal('modifyRequestModal'); loadMyBookings(); }
          else{ showToast('تعذّر إرسال الطلب','error'); }
        }catch(err){ console.error(err); showToast('خطأ أثناء الإرسال','error'); }
      });
    }
  }

  // ---------- Recommendations ----------
async function loadRecommendations(){
  const listEl = document.getElementById('recommendationsList'); if(!listEl) return;
  listEl.innerHTML = `<p class="center" style="opacity:.8">جارِ التحميل...</p>`;
  try{
    const r = await fetch(`${BASE}/api/recommendations`, { headers:{ 'Accept':'application/json' }});
    const data = await r.json();
    const items = Array.isArray(data) ? data : (data.data || []);

    if(!items.length){
      listEl.innerHTML = `<p class="center" style="opacity:.8">لا توجد توصيات حالياً</p>`;
      return;
    }

    listEl.innerHTML = items.map(rec => {
      const user = rec.user?.name || 'مستخدم';
      const dest = rec.destination || rec.trip?.destination || rec.place || '—';
      const rating = rec.rating || 5;
      const text = rec.text || rec.note || '';
      const when = (rec.created_at || '').slice(0,10);
      return `
        <div class="trip-card">
          <div class="trip-content">
            <h3 class="trip-title">${dest}</h3>
            <div class="trip-features">
              <span class="chip">التقييم: ${rating}</span>
              <span class="chip">${user}</span>
              ${when ? `<span class="chip">${when}</span>` : ''}
            </div>
            ${text ? `<p class="trip-description" style="margin-top:.5rem">${text}</p>` : ''}
          </div>
        </div>
      `;
    }).join('');
  }catch(e){
    console.warn('loadRecommendations failed', e);
    listEl.innerHTML = `<p class="center" style="opacity:.8">تعذّر جلب التوصيات</p>`;
  }
}

  async function populateCompletedBookingsForRating(){
  const sel = document.getElementById('recBooking');
  if (!sel) return;

  try{
    const r = await fetch(`${BASE}/bookings/mine`, { headers:{'Accept':'application/json'}, credentials:'same-origin' });
    if(!r.ok){ sel.innerHTML = `<option value="">تعذّر جلب الحجوزات</option>`; return; }
    const data = await r.json();
    const list = Array.isArray(data) ? data : (data.data || []);

    const today = new Date().toISOString().slice(0,10);
    const completed = list.filter(b => {
      const d = (b.end_date || b.start_date || '').slice(0,10);
      const st = (b.status || '').toLowerCase();
      return d && d < today && (st === 'confirmed' || st === 'completed' || st === 'done' || st === 'approved');
    });

    if (!completed.length){
      sel.innerHTML = `<option value="">لا توجد حجوزات مكتملة للتقييم</option>`;
      return;
    }

    sel.innerHTML = `<option value="">اختر رحلة حجزتها وانتهت</option>` + completed.map(b=>{
      const t = b.trip || {};
      const dest = t.destination || '—';
      const date = (b.end_date || b.start_date || '').slice(0,10);
      return `<option value="${b.id}">${dest} — ${date}</option>`;
    }).join('');

  }catch(e){
    console.warn(e);
    sel.innerHTML = `<option value="">تعذّر جلب الحجوزات</option>`;
  }
}


function bindRecommendationForm(){
  const form = document.getElementById('recommendationForm');
  if(!form || form.__bound) return; form.__bound = true;

  form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    if(!authUser()){ window.location.href = `${BASE}/login`; return; }

    const bookingId = document.getElementById('recBooking')?.value || '';
    const rating    = Number(document.getElementById('recRating')?.value || 5);
    const text      = document.getElementById('recText')?.value?.trim() || '';

    if (!bookingId){ showToast('اختر رحلة مكتملة أولاً', 'error'); return; }
    if (!text){ showToast('اكتب رأيك عن الرحلة', 'error'); return; }

    try{
      const r = await fetch(`${BASE}/api/recommendations`, {
        method:'POST',
        headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN': csrf(), 'Accept':'application/json' },
        credentials:'same-origin',
        body: JSON.stringify({ booking_id: bookingId, rating, text })
      });
      if (r.ok){
        form.reset();
        showToast('تم إضافة توصيتك ✅','success');
        loadRecommendations();
        populateCompletedBookingsForRating(); 
      } else {
        const err = await r.json().catch(()=> ({}));
        console.warn('recommendation failed', err);
        showToast(err?.message || 'تعذّر إضافة التوصية', 'error');
      }
    }catch(err){
      console.error(err);
      showToast('حدث خطأ أثناء الإرسال', 'error');
    }
  });
}


  // ---------- Trip Requests (----------
  async function loadTripRequests(){
    const listEl = $('#userRequests'); if(!listEl) return;
    if(!authUser()){ listEl.innerHTML = `<p class="center" style="opacity:.8">سجّل الدخول لعرض طلباتك</p>`; return; }
    listEl.innerHTML = `<p class="center" style="opacity:.8">جارِ التحميل...</p>`;
    try{
      const r = await fetch(`${BASE}/api/trip-requests/mine`, { headers:{ 'Accept':'application/json' }, credentials:'same-origin' });
      const data = await r.json();
      const items = Array.isArray(data) ? data : (data.data || []);
      if(!items.length){ listEl.innerHTML = `<p class="center" style="opacity:.8">لا توجد طلبات حتى الآن</p>`; return; }
      listEl.innerHTML = items.map(req => `
        <div class="trip-card">
          <div class="trip-content">
            <h3 class="trip-title">${req.destination || '—'}</h3>
            <div class="trip-features">
              <span class="chip">${(req.status||'pending')}</span>
              <span class="chip">${req.date || ''}</span>
              <span class="chip">${req.people || 1} أشخاص</span>
              ${req.budget ? `<span class="chip">ميزانية: ${fmt(req.budget)} ر.س</span>` : ''}
            </div>
            ${req.details ? `<p class="trip-description" style="margin-top:.5rem">${req.details}</p>` : ''}
          </div>
        </div>
      `).join('');
    }catch(e){ console.warn('loadTripRequests failed', e); listEl.innerHTML = `<p class="center" style="opacity:.8">تعذّر جلب الطلبات</p>`; }
  }

  function bindTripRequestForm(){
    const form = $('#tripRequestForm');
    if(!form || form.__bound) return; form.__bound = true;
    form.addEventListener('submit', async (e)=>{
      e.preventDefault();
      if(!authUser()){ window.location.href = `${BASE}/login`; return; }
      const payload = {
        destination: $('#requestDestination')?.value || '',
        date:        $('#requestDate')?.value || '',
        people:      Number($('#requestPeople')?.value || 1),
        budget:      Number($('#requestBudget')?.value || 0),
        details:     $('#requestDetails')?.value || '',
      };
      if(!payload.destination || !payload.date){ showToast('الوجهة والتاريخ مطلوبة','error'); return; }
      try{
        const r = await fetch(`${BASE}/api/trip-requests`, {
          method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf(),'Accept':'application/json'},
          credentials:'same-origin', body: JSON.stringify(payload)
        });
        if(r.ok){ showToast('تم إرسال الطلب ✅','success'); form.reset(); loadTripRequests(); }
        else{ showToast('تعذّر إرسال الطلب'); }
      }catch(err){ console.error(err); showToast('خطأ أثناء الإرسال','error'); }
    });
  }

function populateDestinations(trips){
  const sel = document.getElementById('filterDestination2');
  if (!sel) return;
  const keepFirst = sel.querySelector('option')?.outerHTML || '<option value="">كل الوجهات</option>';
  const uniq = Array.from(new Set(trips.map(getDest).filter(Boolean))).sort((a,b)=>a.localeCompare(b,'ar'));
  sel.innerHTML = keepFirst + uniq.map(d => `<option value="${d}">${d}</option>`).join('');
}

  document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('allTrips') || document.getElementById('featuredTrips')) {
      bootstrapTrips();
      bindBookForm();
      bindFilters();
    }

    if (document.getElementById('userBookings')) {
      loadMyBookings();
      bindBookingFilters();
      bindCancelModifyForms();
    }

    if (document.getElementById('recommendationsList')) {
      loadRecommendations();
      bindRecommendationForm();
    }
    if (document.getElementById('userRequests')) {
      loadTripRequests();
      bindTripRequestForm();
    }
      if (document.querySelector('[data-approve],[data-reject]')) {
    bindAdminRequestActions();
  }
  });
 //  ------------------ Admin ---------------- 

function bindAdminRequestActions(){
  const BASE = (window.appBase || '').replace(/\/+$/, '');
  const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
  const pendingNum = document.querySelector('[data-stat-pending]');

  function afterSuccess(id, badgeClass, badgeText){
    const card = document.getElementById(`req-${id}`);
    if (card){
      const badge = card.querySelector('.badge');
      if (badge){ badge.className = `badge ${badgeClass}`; badge.textContent = badgeText; }
      card.style.transition = 'opacity .25s, transform .25s, height .25s, margin .25s, padding .25s';
      card.style.opacity = '0';
      card.style.transform = 'translateY(-6px)';
      setTimeout(()=>{ card.style.height='0px'; card.style.margin='0'; card.style.padding='0'; }, 80);
      setTimeout(()=> card.remove(), 300);
    }
    if (pendingNum){
      const n = parseInt((pendingNum.textContent || '0').replace(/[^\d]/g,''),10)||0;
      pendingNum.textContent = Math.max(0, n-1);
    }
  }

  document.querySelectorAll('[data-approve]').forEach(btn=>{
    if (btn.__bound) return; btn.__bound = true;
    btn.addEventListener('click', async ()=>{
      const id = btn.getAttribute('data-approve');
      btn.disabled = true;
      try{
        const r = await fetch(`${BASE}/admin/booking-requests/${id}/approve`, {
          method:'POST', headers:{'X-CSRF-TOKEN':csrf(),'Accept':'application/json'}, credentials:'same-origin'
        });
        if(!r.ok) throw new Error('HTTP '+r.status);
        afterSuccess(id, 'badge-approved', 'Approved');
      }catch(e){ console.warn(e); btn.disabled=false; showToast('تعذّر القبول','error'); }
    });
  });

  document.querySelectorAll('[data-reject]').forEach(btn=>{
    if (btn.__bound) return; btn.__bound = true;
    btn.addEventListener('click', ()=>{
      const id = btn.getAttribute('data-reject');
      document.getElementById('rejectRequestId').value = id;
      const m = document.getElementById('rejectReasonModal');
      if (m){ m.classList.add('show'); m.setAttribute('aria-hidden','false'); }
    });
  });

  const form = document.getElementById('rejectReasonForm');
  if (form && !form.__bound){
    form.__bound = true;
    form.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const id  = document.getElementById('rejectRequestId').value;
      const txt = document.getElementById('rejectReasonText').value.trim();
      if (!txt){ showToast('فضلاً اكتب سبب الرفض','error'); return; }
      try{
        const r = await fetch(`${BASE}/admin/booking-requests/${id}/reject`, {
          method:'POST',
          headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf(),'Accept':'application/json'},
          credentials:'same-origin',
          body: JSON.stringify({ admin_reason: txt })
        });
        if(!r.ok) throw new Error('HTTP '+r.status);
        closeModal('rejectReasonModal');
        afterSuccess(id, 'badge-rejected', 'Rejected');
      }catch(e){ console.warn(e); showToast('تعذّر الرفض','error'); }
    });
  }
}

// ====== Toasts ======
(function(){
  const mount = () => {
    let c = document.querySelector('.toast-container');
    if (!c){ c = document.createElement('div'); c.className = 'toast-container'; document.body.appendChild(c); }
    return c;
  };
window.showToast = function(msg, type='info', timeout=2600){
  let c = document.querySelector('.toast-container');
  if (!c){
    c = document.createElement('div');
    c.className = 'toast-container';
    document.body.appendChild(c);
  }

  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.textContent = msg;
  c.appendChild(el);

  requestAnimationFrame(() => el.classList.add('show'));

  setTimeout(() => {
    el.classList.remove('show');
    setTimeout(() => el.remove(), 250);
  }, timeout);
};

})();

(function(){
  const BASE = (window.appBase || '').replace(/\/+$/, '');
  const $ = (s,p=document)=>p.querySelector(s);

  const userBtn = $('#userBtn'), userDD = $('#userDropdown');
  const bellBtn = $('#notifBtn'), bellDD = $('#notifDropdown');
  function toggle(el){ el?.classList.toggle('show'); }
  userBtn?.addEventListener('click', (e)=>{ e.stopPropagation(); toggle(userDD); bellDD?.classList.remove('show'); });
  bellBtn?.addEventListener('click', (e)=>{ e.stopPropagation(); toggle(bellDD); userDD?.classList.remove('show'); });
  docment.addEventListener('click', ()=>{ userDD?.classList.remove('show'); bellDD?.classList.remove('show'); });

  let lastSeenAt = 0;
  async function fetchAlerts(){
    try{
      const r = await fetch(`${BASE}/api/alerts?since=${lastSeenAt}`, { headers:{'Accept':'application/json'}, credentials:'same-origin' });
      if(!r.ok) return;
      const data = await r.json();
      const items = Array.isArray(data) ? data : (data.data||[]);
      renderAlerts(items);
      items.filter(x => x._new).forEach(x => {
        const t = (x.kind==='booking') ? 'الحجز' : (x.kind==='modify' ? 'التعديل' : 'الإلغاء');
        const st = (x.status||'').toLowerCase();
        if (st==='approved')      showToast(`تم قبول ${t} للرحلة "${x.destination}" ✅`, 'success');
        else if (st==='rejected') showToast(`تم رفض ${t} للرحلة "${x.destination}" ❌ ${x.admin_reason?(' — السبب: '+x.admin_reason):''}`, 'error', 3600);
        else                      showToast(`تحديث على ${t}: ${st}`, 'info');
      });
      lastSeenAt = Date.now();
    }catch(e){ /* صامت */ }
  }

  function renderAlerts(items){
    const list = $('#notifList'), badge = $('#notifBadge'), empty = $('#notifEmpty');
    if (!list) return;
    list.innerHTML = items.map(x => `
      <div class="item">
        <div style="font-weight:700">${x.destination || '—'}</div>
        <div class="sub">${x.title}</div>
        ${x.admin_reason ? `<div class="sub">السبب: ${x.admin_reason}</div>` : ''}
      </div>
    `).join('');
    const count = items.length;
    if (count > 0){ badge.style.display='inline-block'; badge.textContent = count; empty.style.display='none'; }
    else { badge.style.display='none'; empty.style.display='block'; }
  }

  if (bellBtn) { fetchAlerts(); setInterval(fetchAlerts, 30000); }
})();



  window.bootstrapTrips = bootstrapTrips;
  window.loadMyBookings = loadMyBookings;
  window.bindBookForm = bindBookForm;
  window.bindFilters = bindFilters;
  window.bindBookingFilters = bindBookingFilters;
  window.bindCancelModifyForms = bindCancelModifyForms;
  window.loadRecommendations = loadRecommendations;
  window.bindRecommendationForm = bindRecommendationForm;
  window.loadTripRequests = loadTripRequests;
  window.bindTripRequestForm = bindTripRequestForm;
})();

