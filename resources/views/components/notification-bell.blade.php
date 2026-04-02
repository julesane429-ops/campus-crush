{{-- components/notification-bell.blade.php --}}

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
             style="background: rgba(26,17,69,0.97); z-index: 1;">
            <h3 class="font-semibold text-sm">Notifications</h3>
            {{-- onclick appelle directement la fonction globale, pas de addEventListener --}}
            <button type="button"
                    id="notif-mark-all"
                    onclick="notifMarkAllRead()"
                    class="hidden text-[11px] text-[#ff5e6c] hover:text-[#ff8a5c] transition font-medium px-2 py-1">
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
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    let notifBusy = false;

    // ── Helpers DOM ───────────────────────────────────────────────
    function el(id) { return document.getElementById(id); }
    function escH(v) { const d = document.createElement('div'); d.textContent = String(v ?? ''); return d.innerHTML; }

    // ── Badge ─────────────────────────────────────────────────────
    function updateBadge(count) {
        const badge = el('notif-badge');
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    // ── Ouverture / fermeture ─────────────────────────────────────
    window.toggleNotifications = function () {
        const dd = el('notif-dropdown');
        if (!dd) return;
        const opening = dd.classList.toggle('hidden') === false;
        // classList.toggle renvoie true si la classe est présente après l'appel
        // donc si elle vient d'être enlevée (dropdown visible), opening = true
        if (!dd.classList.contains('hidden')) {
            loadNotifications();
        }
    };

    document.addEventListener('click', function (e) {
        const wrapper = el('notif-wrapper');
        const dd      = el('notif-dropdown');
        if (wrapper && dd && !wrapper.contains(e.target)) {
            dd.classList.add('hidden');
        }
    });

    // ── Chargement ────────────────────────────────────────────────
    async function loadNotifications() {
        const list = el('notif-list');
        if (list) list.innerHTML = '<div class="px-4 py-8 text-center text-white/25 text-sm">Chargement…</div>';
        try {
            const res = await fetch('/notifications', { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            renderNotifications(await res.json());
        } catch (err) {
            console.error('[Notif] load error:', err);
            const list = el('notif-list');
            if (list) list.innerHTML = '<div class="px-4 py-8 text-center text-white/25 text-sm">Erreur de chargement</div>';
        }
    }

    // ── Rendu ─────────────────────────────────────────────────────
    function renderNotifications(data) {
        const notifications = data.notifications || [];
        const count         = data.unread_count  ?? notifications.length;

        updateBadge(count);

        const btn = el('notif-mark-all');
        if (btn) {
            if (notifications.length > 0) btn.classList.remove('hidden');
            else                          btn.classList.add('hidden');
        }

        const list = el('notif-list');
        if (!list) return;

        if (notifications.length === 0) {
            list.innerHTML = '<div class="px-4 py-8 text-center text-white/25 text-sm">Aucune notification</div>';
            return;
        }

        list.innerHTML = notifications.map(n => {
            const d      = n.data  || {};
            const href   = d.match_id ? `/messages/${d.match_id}` : '#';
            const avatar = d.user_photo || d.sender_photo;
            const icon   = d.type === 'new_match' ? '💕' : '💬';

            return `<a href="${href}"
                       onclick="notifMarkRead('${escH(n.id)}', event)"
                       class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition cursor-pointer">
                        <div class="flex-shrink-0">
                            ${avatar
                                ? `<img src="${escH(avatar)}" class="w-10 h-10 rounded-full object-cover" alt="">`
                                : `<div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center">${icon}</div>`
                            }
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-white/70 truncate">${escH(d.message || '')}</p>
                            <p class="text-[10px] text-white/25 mt-0.5">${escH(n.time || '')}</p>
                        </div>
                        <span id="dot-${escH(n.id)}" class="w-2 h-2 rounded-full flex-shrink-0" style="background:#ff5e6c;"></span>
                    </a>`;
        }).join('');
    }

    // ── Tout marquer comme lu — fonction GLOBALE appelée via onclick ──
    window.notifMarkAllRead = async function () {
        if (notifBusy) return;
        notifBusy = true;

        const btn = el('notif-mark-all');
        if (btn) { btn.textContent = '…'; btn.classList.add('opacity-50'); }

        try {
            const res = await fetch('/notifications/read-all', {
                method : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept'      : 'application/json',
                },
                body: JSON.stringify({}),
            });

            if (!res.ok) throw new Error('HTTP ' + res.status);

            // Succès → vider l'UI
            updateBadge(0);
            if (btn) btn.classList.add('hidden');
            const list = el('notif-list');
            if (list) list.innerHTML = '<div class="px-4 py-8 text-center text-white/25 text-sm">Aucune notification</div>';

        } catch (err) {
            console.error('[Notif] mark-all error:', err);
            // Échec → recharger l'état réel depuis le serveur
            await loadNotifications();
        } finally {
            if (btn) { btn.textContent = 'Tout marquer comme lu'; btn.classList.remove('opacity-50'); }
            notifBusy = false;
        }
    };

    // ── Marquer une notif individuelle ────────────────────────────
    window.notifMarkRead = function (id, _e) {
        const dot = el('dot-' + id);
        if (dot) dot.style.display = 'none';

        fetch('/notifications/' + id + '/read', {
            method   : 'POST',
            keepalive: true,
            headers  : { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
        }).catch(() => {});
    };

    // ── Polling du badge ──────────────────────────────────────────
    async function refreshBadge() {
        if (notifBusy) return;
        try {
            const res = await fetch('/nav-counts', { headers: { Accept: 'application/json' } });
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
            window.Echo.private('user.' + userId).listen('.NewMatch', function (data) {
                refreshBadge();
                const t = document.createElement('div');
                t.className = 'fixed top-6 left-1/2 -translate-x-1/2 z-[100] px-6 py-3 rounded-2xl text-sm font-medium text-white';
                t.style.cssText = 'background:linear-gradient(135deg,rgba(255,94,108,0.9),rgba(255,138,92,0.9));border:1px solid rgba(255,255,255,0.1);backdrop-filter:blur(20px);';
                t.textContent = '💕 Match avec ' + (data.otherUserName || '') + ' !';
                document.body.appendChild(t);
                setTimeout(() => t.remove(), 4000);
            });
        }
    }
})();
</script>