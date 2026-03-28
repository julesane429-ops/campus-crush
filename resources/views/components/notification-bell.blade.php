{{-- 
    Notification Bell Component
    Include in any page header: @include('components.notification-bell')
    Requires Echo/Pusher to be loaded for real-time.
--}}

<div class="relative" id="notif-wrapper">
    <button onclick="toggleNotifications()" class="relative p-2 rounded-xl hover:bg-white/5 transition">
        <svg class="w-6 h-6 text-white/50 hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
        </svg>
        <span id="notif-badge" class="hidden absolute -top-0.5 -right-0.5 w-5 h-5 bg-gradient-to-r from-[#ff5e6c] to-[#ff8a5c] text-[10px] font-bold rounded-full flex items-center justify-center shadow-lg shadow-[#ff5e6c]/30">
            0
        </span>
    </button>

    {{-- Dropdown --}}
    <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-80 max-h-96 overflow-y-auto rounded-2xl z-50" style="background: rgba(26,17,69,0.97); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(40px); box-shadow: 0 20px 60px rgba(0,0,0,0.5);">
        
        <div class="flex items-center justify-between px-4 py-3 border-b border-white/5">
            <h3 class="font-semibold text-sm">Notifications</h3>
            <button onclick="markAllNotificationsRead()" class="text-[11px] text-[#ff5e6c] hover:text-[#ff8a5c] transition font-medium">
                Tout marquer lu
            </button>
        </div>

        <div id="notif-list" class="divide-y divide-white/5">
            <div class="px-4 py-8 text-center text-white/25 text-sm">
                Aucune notification
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const badge = document.getElementById('notif-badge');
    const dropdown = document.getElementById('notif-dropdown');
    const list = document.getElementById('notif-list');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    // Toggle dropdown
    window.toggleNotifications = function() {
        dropdown.classList.toggle('hidden');
        if (!dropdown.classList.contains('hidden')) {
            loadNotifications();
        }
    };

    // Close on outside click
    document.addEventListener('click', function(e) {
        if (!document.getElementById('notif-wrapper').contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Load notifications
    async function loadNotifications() {
        try {
            const res = await fetch('/notifications', { headers: { 'Accept': 'application/json' } });
            const data = await res.json();

            updateBadge(data.unread_count);

            if (data.notifications.length === 0) {
                list.innerHTML = '<div class="px-4 py-8 text-center text-white/25 text-sm">Aucune notification</div>';
                return;
            }

            list.innerHTML = data.notifications.map(n => {
                const d = n.data;
                const icon = d.type === 'new_match' ? '💕' : '💬';
                const href = d.type === 'new_match' 
                    ? `/messages/${d.match_id}` 
                    : `/messages/${d.match_id}`;
                const unreadClass = n.read ? '' : 'bg-white/[0.03]';

                return `
                    <a href="${href}" onclick="markNotifRead('${n.id}')" 
                       class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition ${unreadClass}">
                        <div class="flex-shrink-0">
                            ${d.user_photo || d.sender_photo 
                                ? `<img src="${d.user_photo || d.sender_photo}" class="w-10 h-10 rounded-full object-cover" alt="">`
                                : `<div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-lg">${icon}</div>`
                            }
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-white/70 truncate">${escapeH(d.message || '')}</p>
                            <p class="text-[10px] text-white/25 mt-0.5">${n.time}</p>
                        </div>
                        ${!n.read ? '<span class="w-2 h-2 bg-[#ff5e6c] rounded-full flex-shrink-0"></span>' : ''}
                    </a>
                `;
            }).join('');
        } catch(e) {
            console.error('Notifications error:', e);
        }
    }

    function updateBadge(count) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    window.markNotifRead = async function(id) {
        try {
            await fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
            });
        } catch(e) {}
    };

    window.markAllNotificationsRead = async function() {
        try {
            await fetch('/notifications/read-all', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
            });
            badge.classList.add('hidden');
            // Refresh list
            loadNotifications();
        } catch(e) {}
    };

    function escapeH(t) { const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

    // Auto-refresh badge every 30s
    async function refreshBadge() {
        try {
            const res = await fetch('/nav-counts', { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            updateBadge(data.notifications || 0);
        } catch(e) {}
    }
    setInterval(refreshBadge, 30000);
    refreshBadge(); // initial

    // Listen for real-time notifications via Echo (if available)
    if (window.Echo) {
        const userId = document.querySelector('meta[name="user-id"]')?.content;
        if (userId) {
            Echo.private(`user.${userId}`)
                .listen('.NewMatch', (data) => {
                    refreshBadge();
                    // Show a toast
                    showNotifToast(`💕 Match avec ${data.otherUserName} !`);
                });
        }
    }

    function showNotifToast(text) {
        const toast = document.createElement('div');
        toast.className = 'fixed top-6 left-1/2 -translate-x-1/2 z-[100] px-6 py-3 rounded-2xl text-sm font-medium text-white shadow-xl';
        toast.style.cssText = 'background: linear-gradient(135deg, rgba(255,94,108,0.9), rgba(255,138,92,0.9)); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(20px); animation: fadeUp 0.4s ease both;';
        toast.innerHTML = `<style>@keyframes fadeUp{from{opacity:0;transform:translateX(-50%) translateY(20px)}to{opacity:1;transform:translateX(-50%) translateY(0)}}</style>${text}`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }
})();
</script>
