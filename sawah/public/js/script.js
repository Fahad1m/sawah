         let currentUser = null;
        let trips = [];
        let requests = [];
        let recommendations = [];
        let users = [];

      
        document.addEventListener('DOMContentLoaded', function() {
             fetchTrips();  
            //renderFeaturedTrips();
            //renderAllTrips();
            setupEventListeners();
            checkAuthState();
        });

 
async function fetchTrips(page = 1) {
  try {
    const res  = await fetch(`/api/trips?page=${page}`);
    const data = await res.json();

    trips = (data.data || []).map(t => ({
      id: t.id,
      destination: t.destination,
      description: t.description ?? '',
      price: Number(t.price ?? 0),
      duration: Number(t.duration ?? 0),
      rating: Number(t.rating ?? 0) || 4.5,
      image: t.image_url || t.image || 'https://images.pexels.com/photos/1008155/pexels-photo-1008155.jpeg',
      features: Array.isArray(t.features) ? t.features :
                (t.features ? String(t.features).split(',').map(s=>s.trim()) : [])
    }));

    loadFeaturedTrips();
    loadAllTrips();
  } catch (e) {
    console.error(e);
    showAlert('تعذّر جلب الرحلات من الخادم', 'error');
  }
}


// استبدال الدوال القديمة:
function renderFeaturedTrips() {
  const container = document.getElementById('featuredTrips');
  const featuredTrips = trips.slice(0, 3);
  container.innerHTML = featuredTrips.map(trip => createTripCard(trip)).join('');
}

function renderAllTrips() {
  const container = document.getElementById('allTrips');
  container.innerHTML = trips.map(trip => createTripCard(trip)).join('');
}


        // Navigation functions
        function showSection(sectionId) {
            // Hide all sections
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => section.classList.remove('active'));

            // Show selected section
            document.getElementById(sectionId).classList.add('active');

            // Check if user needs to be logged in
            if ((sectionId === 'requests' || sectionId === 'admin') && !currentUser) {
                showAlert('يجب تسجيل الدخول أولاً', 'error');
                showLoginModal();
                return;
            }

            // Check admin access
            if (sectionId === 'admin' && currentUser?.type !== 'admin') {
                showAlert('ليس لديك صلاحية للوصول لهذه الصفحة', 'error');
                showSection('home');
                return;
            }

            // Load section-specific data
            if (sectionId === 'requests') {
                loadUserRequests();
            } else if (sectionId === 'recommendations') {
                loadRecommendations();
            } else if (sectionId === 'admin') {
                loadAdminData();
            }
        }

        // Modal functions
        function showLoginModal() {
            document.getElementById('loginModal').classList.add('active');
        }

        function showRegisterModal() {
            document.getElementById('registerModal').classList.add('active');
        }

        function showAddTripModal() {
            document.getElementById('addTripModal').classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Authentication functions
      function getCsrf() {
  const m = document.querySelector('meta[name="csrf-token"]');
  return m ? m.getAttribute('content') : '';
}

function checkAuthState() {
  if (window.laravelUser) {
    currentUser = {
      id: window.laravelUser.id,
      name: window.laravelUser.name,
      email: window.laravelUser.email,
      type: window.laravelUser.is_admin ? 'admin' : 'user'
    };
  } else {
    currentUser = null;
  }
  updateAuthUI();
}

        function updateAuthUI() {
            const authButtons = document.getElementById('authButtons');
            const userMenu = document.getElementById('userMenu');
            const welcomeText = document.getElementById('welcomeText');
            const adminBtn = document.getElementById('adminBtn');
            const userWelcome = document.getElementById('userWelcome');
            const welcomeMessage = document.getElementById('welcomeMessage');

            if (currentUser) {
                authButtons.style.display = 'none';
                userMenu.style.display = 'flex';
                userWelcome.style.display = 'flex';
                welcomeText.textContent = `مرحباً، ${currentUser.name}`;
                welcomeMessage.textContent = `مرحباً، ${currentUser.name}`;

                if (currentUser.type === 'admin') {
                    adminBtn.style.display = 'inline-block';
                }
            } else {
                authButtons.style.display = 'flex';
                userMenu.style.display = 'none';
                userWelcome.style.display = 'none';
                adminBtn.style.display = 'none';
            }
        }

        function login(email, password) {
            const user = users.find(u => u.email === email);
            if (user) {
                currentUser = user;
                localStorage.setItem('currentUser', JSON.stringify(user));
                updateAuthUI();
                closeModal('loginModal');
                showAlert('تم تسجيل الدخول بنجاح', 'success');
                return true;
            }
            return false;
        }

        function register(name, email, password) {
            if (users.find(u => u.email === email)) {
                return false; // User already exists
            }

            const newUser = {
                id: users.length + 1,
                name: name,
                email: email,
                type: 'user',
                joinDate: new Date().toISOString().split('T')[0]
            };

            users.push(newUser);
            currentUser = newUser;
            localStorage.setItem('currentUser', JSON.stringify(newUser));
            updateAuthUI();
            closeModal('registerModal');
            showAlert('تم إنشاء الحساب بنجاح', 'success');
            return true;
        }

        function logout() {
            currentUser = null;
            localStorage.removeItem('currentUser');
            updateAuthUI();
            showSection('home');
            showAlert('تم تسجيل الخروج بنجاح', 'success');
        }

        // Trip functions
        function loadFeaturedTrips() {
            const container = document.getElementById('featuredTrips');
            const featuredTrips = trips.slice(0, 3);

            container.innerHTML = featuredTrips.map(trip => createTripCard(trip)).join('');
        }

        function loadAllTrips() {
            const container = document.getElementById('allTrips');
            container.innerHTML = trips.map(trip => createTripCard(trip)).join('');
        }

        function createTripCard(trip) {
            return `
                <div class="trip-card fade-in">
                    <div class="trip-image" style="background-image: url('${trip.image}')">
                        <div class="trip-rating">
                            <i class="fas fa-star"></i> ${trip.rating}
                        </div>
                    </div>
                    <div class="trip-content">
                        <h3 class="trip-title">${trip.destination}</h3>
                        <p class="trip-description">${trip.description}</p>
                        <div class="trip-price">${trip.price} ريال</div>
                        <div class="trip-features">
                            ${trip.features.map(feature => `<span class="feature">${feature}</span>`).join('')}
                        </div>
                        <button class="btn btn-primary" onclick="requestTrip(${trip.id})" style="width: 100%;">
                            طلب الرحلة
                        </button>
                    </div>
                </div>
            `;
        }

        function requestTrip(tripId) {
            if (!currentUser) {
                showAlert('يجب تسجيل الدخول أولاً', 'error');
                showLoginModal();
                return;
            }

            const trip = trips.find(t => t.id === tripId);
            if (trip) {
                // Pre-fill the request form
                document.getElementById('requestDestination').value = trip.destination;
                document.getElementById('requestBudget').value = trip.price;
                showSection('requests');
                showAlert('تم تعبئة النموذج مسبقاً، يرجى إكمال البيانات', 'success');
            }
        }
async function loadUserRequests(){
  if (!currentUser) return;
  const container = document.getElementById('userRequests');
  try {
    const res = await fetch(`/api/requests/mine?user=${encodeURIComponent(currentUser.name)}`);
    const data = await res.json();
    if (!data.length){
      container.innerHTML = '<p>لا توجد طلبات سابقة</p>';
      return;
    }
    container.innerHTML = data.map(request => `
      <div class="recommendation-card">
        <h4>${request.destination}</h4>
        <p><strong>التاريخ:</strong> ${request.date}</p>
        <p><strong>عدد الأشخاص:</strong> ${request.people}</p>
        <p><strong>الميزانية:</strong> ${request.budget} ريال</p>
        <p><strong>الحالة:</strong> ${getStatusText(request.status)}</p>
        <p>${request.details ?? ''}</p>
      </div>
    `).join('');
  } catch(e){ container.innerHTML = '<p>تعذّر تحميل الطلبات</p>'; }
}


        function getStatusText(status) {
            const statusMap = {
                'pending': 'قيد المراجعة',
                'approved': 'مقبول',
                'rejected': 'مرفوض'
            };
            return statusMap[status] || status;
        }

        // Recommendation functions
        function loadRecommendations() {
            const container = document.getElementById('recommendationsList');

            container.innerHTML = recommendations.map(rec => `
                <div class="recommendation-card">
                    <div class="recommendation-header">
                        <div class="user-avatar">${rec.user.charAt(0)}</div>
                        <div>
                            <strong>${rec.user}</strong>
                            <div style="color: #4cc9f0;">
                                ${Array(rec.rating).fill('★').join('')}${Array(5-rec.rating).fill('☆').join('')}
                            </div>
                        </div>
                    </div>
                    <h4>${rec.place}</h4>
                    <p>${rec.text}</p>
                    <small style="color: #b8b8b8;">${rec.date}</small>
                </div>
            `).join('');
        }

        // Admin functions
        function showAdminTab(tabName) {
            // Update tab buttons
            document.querySelectorAll('.admin-tab').forEach(tab => tab.classList.remove('active'));
            event.target.classList.add('active');

            // Update content
            document.querySelectorAll('.admin-content').forEach(content => content.classList.remove('active'));
            document.getElementById(`admin${tabName.charAt(0).toUpperCase() + tabName.slice(1)}`).classList.add('active');

            // Load specific data
            if (tabName === 'trips') {
                loadAdminTrips();
            } else if (tabName === 'requests') {
                loadAdminRequests();
            } else if (tabName === 'users') {
                loadAdminUsers();
            } else if (tabName === 'recommendations') {
                loadAdminRecommendations();
            }
        }

        function loadAdminData() {
            // Update stats
            document.getElementById('totalTrips').textContent = trips.length;
            document.getElementById('totalRequests').textContent = requests.length;
            document.getElementById('totalUsers').textContent = users.length;
            document.getElementById('totalRecommendations').textContent = recommendations.length;

            // Load default tab
            loadAdminTrips();
        }

        function loadAdminTrips() {
            const tbody = document.getElementById('adminTripsTable');
            tbody.innerHTML = trips.map(trip => `
                <tr>
                    <td>${trip.destination}</td>
                    <td>${trip.price} ريال</td>
                    <td>${trip.rating} ⭐</td>
                    <td>
                        <button class="btn btn-secondary" onclick="editTrip(${trip.id})" style="margin-left: 5px;">تعديل</button>
                        <button class="btn btn-primary" onclick="deleteTrip(${trip.id})" style="background: #dc3545;">حذف</button>
                    </td>
                </tr>
            `).join('');
        }
async function loadAdminRequests(){
  const tbody = document.getElementById('adminRequestsTable');
  try{
    const res = await fetch('/api/requests');
    const data = await res.json();
    const items = data.data || data;
    tbody.innerHTML = items.map(request => `
      <tr>
        <td>${request.user_name}</td>
        <td>${request.destination}</td>
        <td>${request.date}</td>
        <td>${getStatusText(request.status)}</td>
        <td>
          <button class="btn btn-secondary" onclick="approveRequest(${request.id})" style="margin-left:5px;">قبول</button>
          <button class="btn btn-primary" onclick="rejectRequest(${request.id})" style="background:#dc3545;">رفض</button>
        </td>
      </tr>
    `).join('');
  } catch(e){
    tbody.innerHTML = '<tr><td colspan="5">تعذّر تحميل الطلبات</td></tr>';
  }
}
async function approveRequest(id){
  try{
    const res = await fetch(`/admin/requests/${id}/approve`, {
      method: 'PATCH',
      headers: { 'X-CSRF-TOKEN': getCsrf(), 'Accept':'application/json' }
    });
    if(!res.ok) throw new Error();
    showAlert('تم قبول الطلب','success');
    loadAdminRequests();
  }catch(e){ showAlert('تعذّر العملية','error'); }
}

async function rejectRequest(id){
  try{
    const res = await fetch(`/admin/requests/${id}/reject`, {
      method: 'PATCH',
      headers: { 'X-CSRF-TOKEN': getCsrf(), 'Accept':'application/json' }
    });
    if(!res.ok) throw new Error();
    showAlert('تم رفض الطلب','success');
    loadAdminRequests();
  }catch(e){ showAlert('تعذّر العملية','error'); }
}



        function loadAdminUsers() {
            const tbody = document.getElementById('adminUsersTable');
            tbody.innerHTML = users.map(user => `
                <tr>
                    <td>${user.name}</td>
                    <td>${user.email}</td>
                    <td>${user.type === 'admin' ? 'مدير' : 'عضو'}</td>
                    <td>${user.joinDate}</td>
                    <td>
                        <button class="btn btn-secondary" onclick="toggleUserType(${user.id})" style="margin-left: 5px;">
                            ${user.type === 'admin' ? 'إلغاء الإدارة' : 'جعل مدير'}
                        </button>
                        <button class="btn btn-primary" onclick="deleteUser(${user.id})" style="background: #dc3545;">حذف</button>
                    </td>
                </tr>
            `).join('');
        }

        function loadAdminRecommendations() {
            const tbody = document.getElementById('adminRecommendationsTable');
            tbody.innerHTML = recommendations.map(rec => `
                <tr>
                    <td>${rec.user}</td>
                    <td>${rec.place}</td>
                    <td>${rec.rating} ⭐</td>
                    <td>${rec.date}</td>
                    <td>
                        <button class="btn btn-primary" onclick="deleteRecommendation(${rec.id})" style="background: #dc3545;">حذف</button>
                    </td>
                </tr>
            `).join('');
        }

        // Admin actions
        function deleteTrip(tripId) {
            if (confirm('هل أنت متأكد من حذف هذه الرحلة؟')) {
                trips = trips.filter(t => t.id !== tripId);
                loadAdminTrips();
                loadFeaturedTrips();
                loadAllTrips();
                showAlert('تم حذف الرحلة بنجاح', 'success');
            }
        }

        function approveRequest(requestId) {
            const request = requests.find(r => r.id === requestId);
            if (request) {
                request.status = 'approved';
                loadAdminRequests();
                showAlert('تم قبول الطلب', 'success');
            }
        }

        function rejectRequest(requestId) {
            const request = requests.find(r => r.id === requestId);
            if (request) {
                request.status = 'rejected';
                loadAdminRequests();
                showAlert('تم رفض الطلب', 'success');
            }
        }

        function toggleUserType(userId) {
            const user = users.find(u => u.id === userId);
            if (user && user.id !== currentUser.id) {
                user.type = user.type === 'admin' ? 'user' : 'admin';
                loadAdminUsers();
                showAlert('تم تحديث نوع المستخدم', 'success');
            }
        }

        function deleteUser(userId) {
            if (userId === currentUser.id) {
                showAlert('لا يمكن حذف حسابك الخاص', 'error');
                return;
            }

            if (confirm('هل أنت متأكد من حذف هذا المستخدم؟')) {
                users = users.filter(u => u.id !== userId);
                loadAdminUsers();
                showAlert('تم حذف المستخدم بنجاح', 'success');
            }
        }

        function deleteRecommendation(recId) {
            if (confirm('هل أنت متأكد من حذف هذه التوصية؟')) {
                recommendations = recommendations.filter(r => r.id !== recId);
                loadAdminRecommendations();
                loadRecommendations();
                showAlert('تم حذف التوصية بنجاح', 'success');
            }
        }

        // Utility functions
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;

            document.body.appendChild(alertDiv);

            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }

        // Event listeners
        function setupEventListeners() {
            // Login form
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const email = document.getElementById('loginEmail').value;
                const password = document.getElementById('loginPassword').value;

                if (login(email, password)) {
                    document.getElementById('loginForm').reset();
                } else {
                    showAlert('بيانات الدخول غير صحيحة', 'error');
                }
            });

            // Register form
            document.getElementById('registerForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const name = document.getElementById('registerName').value;
                const email = document.getElementById('registerEmail').value;
                const password = document.getElementById('registerPassword').value;
                const confirmPassword = document.getElementById('registerConfirmPassword').value;

                if (password !== confirmPassword) {
                    showAlert('كلمات المرور غير متطابقة', 'error');
                    return;
                }

                if (register(name, email, password)) {
                    document.getElementById('registerForm').reset();
                } else {
                    showAlert('البريد الإلكتروني مستخدم بالفعل', 'error');
                }
            });

            // Trip request form
document.getElementById('tripRequestForm').addEventListener('submit', async function(e){
  e.preventDefault();
  if (!currentUser) { showAlert('يجب تسجيل الدخول أولاً','error'); return; }

  const payload = {
    user_name: currentUser.name,
    user_email: currentUser.email || null,
    destination: document.getElementById('requestDestination').value,
    date: document.getElementById('requestDate').value,
    people: parseInt(document.getElementById('requestPeople').value),
    budget: parseInt(document.getElementById('requestBudget').value),
    details: document.getElementById('requestDetails').value
  };

  try {
    const res = await fetch('/api/requests', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(payload)
    });
    if (!res.ok) throw new Error();
    document.getElementById('tripRequestForm').reset();
    await loadUserRequests();
    showAlert('تم إرسال طلب الرحلة بنجاح','success');
  } catch(err){
    showAlert('تعذّر إرسال الطلب','error');
  }
});


            // Recommendation form
            document.getElementById('recommendationForm').addEventListener('submit', function(e) {
                e.preventDefault();

                if (!currentUser) {
                    showAlert('يجب تسجيل الدخول أولاً', 'error');
                    return;
                }

                const newRecommendation = {
                    id: recommendations.length + 1,
                    user: currentUser.name,
                    place: document.getElementById('recPlace').value,
                    rating: parseInt(document.getElementById('recRating').value),
                    text: document.getElementById('recText').value,
                    date: new Date().toISOString().split('T')[0]
                };

                recommendations.push(newRecommendation);
                document.getElementById('recommendationForm').reset();
                loadRecommendations();
                showAlert('تم إضافة التوصية بنجاح', 'success');
            });

            // Add trip form
            document.getElementById('addTripForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const newTrip = {
                    id: trips.length + 1,
                    destination: document.getElementById('tripDestination').value,
                    description: document.getElementById('tripDescription').value,
                    price: parseInt(document.getElementById('tripPrice').value),
                    duration: parseInt(document.getElementById('tripDuration').value),
                    rating: 4.5,
                    image: document.getElementById('tripImage').value || 'https://images.pexels.com/photos/1008155/pexels-photo-1008155.jpeg',
                    features: document.getElementById('tripFeatures').value.split(',').map(f => f.trim())
                };

                trips.push(newTrip);
                document.getElementById('addTripForm').reset();
                closeModal('addTripModal');
                loadAdminTrips();
                loadFeaturedTrips();
                loadAllTrips();
                showAlert('تم إضافة الرحلة بنجاح', 'success');
            });

            // Search and filter
            document.getElementById('searchTrips').addEventListener('input', filterTrips);
            document.getElementById('filterDestination2').addEventListener('change', filterTrips);
            document.getElementById('filterPrice2').addEventListener('change', filterTrips);

            // Close modals when clicking outside
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.classList.remove('active');
                    }
                });
            });
        }

        function filterTrips() {
            const searchTerm = document.getElementById('searchTrips').value.toLowerCase();
            const destinationFilter = document.getElementById('filterDestination2').value;
            const priceFilter = document.getElementById('filterPrice2').value;

            let filteredTrips = trips.filter(trip => {
                const matchesSearch = trip.destination.toLowerCase().includes(searchTerm) ||
                                    trip.description.toLowerCase().includes(searchTerm);

                const matchesDestination = !destinationFilter || trip.destination.includes(destinationFilter);

                let matchesPrice = true;
                if (priceFilter === 'low') matchesPrice = trip.price < 1000;
                else if (priceFilter === 'medium') matchesPrice = trip.price >= 1000 && trip.price <= 3000;
                else if (priceFilter === 'high') matchesPrice = trip.price > 3000;

                return matchesSearch && matchesDestination && matchesPrice;
            });

            const container = document.getElementById('allTrips');
            container.innerHTML = filteredTrips.map(trip => createTripCard(trip)).join('');
        }

        function bookingCardTemplate(b) {
  const t = b.trip || {};
  const img = (t.image && typeof t.image === 'string') ? t.image : 'https://picsum.photos/800/450?blur=1';
  const price = Number(t.price || 0).toLocaleString('ar-EG');
  const people = Number(b.people || 1);
  const date = b.start_date || '';
  const status = b.status || 'pending';

  return `
    <div class="trip-card fade-in" data-status="${status}">
      <div class="trip-image" style="background-image:url('${img}')">
        <div class="trip-rating"><i class="fa fa-user"></i> ${people}</div>
      </div>
      <div class="trip-content">
        <h3 class="trip-title">${t.destination || '—'}</h3>
        <div class="trip-price">${price} ريال</div>
        <div class="trip-features">
          <span class="chip">${date}</span>
          <span class="chip">${status}</span>
        </div>
        <div class="admin-req-actions" style="display:flex; gap:8px; flex-wrap:wrap;">
          <button class="btn btn-secondary" data-cancel="${b.id}">طلب إلغاء</button>
          <button class="btn btn-primary"  data-modify="${b.id}">طلب تعديل</button>
        </div>
      </div>
    </div>
  `;
}

async function loadMyBookings() {
  const box = document.getElementById('userBookings');
  if (!box) return;

  box.innerHTML = `<p class="center" style="opacity:.8">جارِ التحميل…</p>`;

  try {
    const r = await fetch(`${(window.appBase||'').replace(/\/+$/,'')}/bookings/mine`, {
      headers: { 'Accept': 'application/json' }
    });

    if (r.status === 401) { window.location.href = `${window.appBase||''}login`; return; }
    if (!r.ok) { box.innerHTML = `<p class="center" style="opacity:.8">تعذّر جلب البيانات</p>`; return; }

    const data = await r.json();
    const list = Array.isArray(data) ? data : (data.data || []);

    if (!list.length) {
      box.innerHTML = `<p class="center" style="opacity:.8">لا توجد حجوزات حتى الآن</p>`;
      updateBookingCounters({all:0,pending:0,confirmed:0,cancelled:0});
      return;
    }

    // خزنها مؤقتًا للفلترة
    window._myBookings = list;

    // عرض أولي + عدادات
    renderMyBookings(list);
    updateBookingCounters(calcBookingCounts(list));

    // اربط أزرار الطلب/التعديل (لو مفعل عندك مودالاتها)
    bindBookingActionButtons(box);
  } catch (e) {
    console.warn(e);
    box.innerHTML = `<p class="center" style="opacity:.8">تعذّر جلب البيانات</p>`;
  }
}
function renderMyBookings(list) {
  const box = document.getElementById('userBookings');
  if (!box) return;
  box.innerHTML = list.map(bookingCardTemplate).join('');
}

function calcBookingCounts(list) {
  const cnt = { all: list.length, pending:0, confirmed:0, cancelled:0 };
  list.forEach(b => {
    const s = (b.status || 'pending').toLowerCase();
    if (s === 'pending') cnt.pending++;
    else if (s === 'confirmed') cnt.confirmed++;
    else if (s === 'cancelled' || s === 'rejected') cnt.cancelled++;
  });
  return cnt;
}

function updateBookingCounters(cnt) {
  const pills = document.querySelectorAll('#bookingStatusPills .pill');
  pills.forEach(p => {
    const st = p.getAttribute('data-status') || '';
    const small = p.querySelector('.count');
    if (!small) return;
    if (st === '') small.textContent = cnt.all;
    else if (st === 'pending') small.textContent = cnt.pending;
    else if (st === 'confirmed') small.textContent = cnt.confirmed;
    else if (st === 'cancelled') small.textContent = cnt.cancelled;
  });
}

function bindBookingFilters() {
  const pills = document.querySelectorAll('#bookingStatusPills .pill');
  if (!pills.length || bindBookingFilters.__bound) return;
  bindBookingFilters.__bound = true;

  pills.forEach(btn => {
    btn.addEventListener('click', () => {
      pills.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const st = btn.getAttribute('data-status') || '';
      const all = window._myBookings || [];
      const filtered = st ? all.filter(b => (b.status||'').toLowerCase() === st) : all;
      renderMyBookings(filtered);
      // ما نلمس العدادات هنا لأنها ثابتة للكل
    });
  });
}

function bindBookingActionButtons(scope) {
  scope.querySelectorAll('[data-cancel]').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-cancel');
      const el = document.getElementById('cancelBookingId');
      if (el) el.value = id;
      openModal && openModal('cancelRequestModal');
    });
  });

  scope.querySelectorAll('[data-modify]').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-modify');
      const el = document.getElementById('modifyBookingId');
      if (el) el.value = id;
      openModal && openModal('modifyRequestModal');
    });
  });
}

