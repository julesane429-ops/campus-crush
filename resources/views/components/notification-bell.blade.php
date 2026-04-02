{{--
    Notification Bell Component
    Include in any page header: @include('components.notification-bell')
--}}

<div class="relative" id="notif-wrapper">
    <button type="button" onclick="toggleNotifications()" class="relative p-2 rounded-xl hover:bg-white/5 transition">
        <svg class="w-6 h-6 text-white/50 hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
        </svg>
        <span id="notif-badge" class="hidden absolute -top-0.5 -right-0.5 w-5 h-5 bg-gradient-to-r from-[#ff5e6c] to-[#ff8a5c] text-[10px] font-bold rounded-full flex items-center justify-center shadow-lg shadow-[#ff5e6c]/30">0</span>
    </button>

    <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-80 max-h-96 overflow-y-auto rounded-2xl z-50"
         style="background: rgba(26,17,69,0.97); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(40px); box-shadow: 0 20px 60px rgba(0,0,0,0.5);">

        <div class="flex items-center justify-between px-4 py-3 border-b border-white/5 sticky top-0"
             style="background: rgba(26,17,69,0.97);">
            <h3 class="font-semibold text-sm">Notifications</h3>
            <button type="button" id="notif-mark-all"
                    class="hidden text-[11px] text-[#ff5e6c] hover:text-[#ff8a5c] transition font-medium">
                Tout marquer comme lu
            </button>
        </div>

        <div id="notif-list">
            <div class="px-4 py-8 text-center text-white/25 text-sm">Chargement…</div>
        </div>
    </div>
</div>

<script>
(function () {
    const badge      = document.getElementById('notif-badge');
    const dropdown   = document.getElementById('notif-dropdown');
    const list       = document.getElementById('notif-list');
    const markAllBtn = document.getElementById('notif-mark-all');
    const wrapper    = document.getElementById('notif-wrapper');
    const csrf       = document.querySelector('meta[name="csrf-token"]')?.content;

    let isBusy = false;

    // ── Ouverture / fermeture ──────────────────────────────────────
    window.toggleNotifications = function () {
        const wasHidden = dropdown.classList.toggle('hidden');
        if (!wasHidden) loadNotifications();
    };

    document.addEventListener('click', function (e) {
        if (!wrapper.contains(e.target)) dropdown.classList.add('hidden');
    });

    // ── Chargement des notifications ──────────────────────────────
    async function loadNotifications() {
        list.innerHTML = '<div class="px-4 py-8 text-center text-white/25 text-sm">Chargement…</div>';
        try {
            const res = await fetch('/notifications', { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            renderNotifications(await res.json());
        } catch (err) {
            console.error('Notifications error:', err);
            list.innerHTML = '<div class="px-4 py-8 text-center text-white/25 text-sm">Erreur de chargement</div>';
        }
    }

    // ── Rendu ──────────────────────────────────────────────────────
    function renderNotifications(data) {
        const notifications = data.notifications || [];
        const count         = data.unread_count ?? notifications.length;

        updateBadge(count);

        // Le bouton s'affiche uniquement si des notifs non lues existent
        if (notifications.length > 0) {
            markAllBtn.classList.remove('hidden');
        } else {
            markAllBtn.classList.add('hidden');
        }

        if (notifications.length === 0) {
            list.innerHTML = '<div class="px-4 py-8 text-center text-white/25 text-sm">Aucune notification</div>';
            return;
        }

        list.innerHTML = notifications.map(n => {
            const d      = n.data || {};
            const icon   = d.type === 'new_match' ? '💕' : '💬';
            const href   = d.match_id ? `/messages/${d.match_id}` : '#';
            const avatar = d.user_photo || d.sender_photo;

            return `
                <a href="${href}"
                   onclick="notifMarkRead('${escH(n.id)}', event)"
                   class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition">
                    <div class="flex-shrink-0">
                        ${avatar
                            ? `<img src="${escH(avatar)}" class="w-10 h-10 rounded-full object-cover" alt="">`
                            : `<div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-lg">${icon}</div>`
                        }
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-white/70 truncate">${escH(d.message || '')}</p>
                        <p class="text-[10px] text-white/25 mt-0.5">${escH(n.time || '')}</p>
                    </div>
                    <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:#ff5e6c;" id="dot-${escH(n.id)}"></span>
                </a>`;
        }).join('');
    }

    // ── Tout marquer comme lu ──────────────────────────────────────
    markAllBtn.addEventListener('click', async function (e) {
        e.preventDefault();
        e.stopPropagation();

        if (isBusy) return;
        isBusy = true;

        // Feedback visuel immédiat
        markAllBtn.textContent = '…';
        markAllBtn.classList.add('opacity-50');

        try {
            const res = await fetch('/notifications/read-all', {
                method : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    Accept        : 'application/json',
                },
                body: JSON.stringify({}),
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            // Succès : vider l'UI
            updateBadge(0);
            markAllBtn.classList.add('hidden');
            list.innerHTML = '<div class="px-4 py-8 text-center text-white/25 text-sm">Aucune notification</div>';

        } catch (err) {
            console.error('Mark-all error:', err);
            // Échec : recharger l'état réel
            await loadNotifications();
        } finally {
            markAllBtn.textContent = 'Tout marquer comme lu';
            markAllBtn.classList.remove('opacity-50');
            isBusy = false;
        }
    });

    // ── Marquer une notif individuelle comme lue ──────────────────
    window.notifMarkRead = function (id, _e) {
        const dot = document.getElementById('dot-' + id);
        if (dot) dot.style.display = 'none';

        fetch(`/notifications/${id}/read`, {
            method   : 'POST',
            keepalive: true,
            headers  : { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
        }).catch(() => {});
    };

    // ── Badge ──────────────────────────────────────────────────────
    function updateBadge(count) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    // ── Rafraîchissement du badge (polling) ───────────────────────
    async function refreshBadge() {
        if (isBusy) return;
        try {
            const res  = await fetch('/nav-counts', { headers: { Accept: 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            updateBadge(data.notifications ?? 0);
        } catch (_) {}
    }

    setInterval(refreshBadge, 30000);
    refreshBadge();

    // ── Temps réel (Echo / Pusher) ────────────────────────────────
    if (window.Echo) {
        const userId = document.querySelector('meta[name="user-id"]')?.content;
        if (userId) {
            window.Echo.private(`user.${userId}`)
                .listen('.NewMatch', (data) => {
                    refreshBadge();
                    showToast(`💕 Match avec ${escH(data.otherUserName)} !`);
                });
        }
    }

    // ── Toast ──────────────────────────────────────────────────────
    function showToast(text) {
        const t = document.createElement('div');
        t.className = 'fixed top-6 left-1/2 -translate-x-1/2 z-[100] px-6 py-3 rounded-2xl text-sm font-medium text-white shadow-xl';
        t.style.cssText = 'background:linear-gradient(135deg,rgba(255,94,108,0.9),rgba(255,138,92,0.9));border:1px solid rgba(255,255,255,0.1);backdrop-filter:blur(20px);';
        t.textContent = text;
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 4000);
    }

    // ── Helpers ────────────────────────────────────────────────────
    function escH(text) {
        const d = document.createElement('div');
        d.textContent = String(text ?? '');
        return d.innerHTML;
    }
})();
</script>