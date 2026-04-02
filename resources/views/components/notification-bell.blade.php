{{-- components/notification-bell.blade.php --}}

<div class="relative" id="notif-wrapper">
    <button type="button" id="notif-bell-btn" class="relative p-2 rounded-xl hover:bg-white/5 transition">
        <svg class="w-6 h-6 text-white/50 hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
        </svg>
        <span id="notif-badge" class="hidden absolute -top-0.5 -right-0.5 w-5 h-5 bg-gradient-to-r from-[#ff5e6c] to-[#ff8a5c] text-[10px] font-bold rounded-full flex items-center justify-center shadow-lg shadow-[#ff5e6c]/30">0</span>
    </button>
</div>

{{--
    Le dropdown est injecté directement dans <body> via JS (pattern "portal").
    Cela le sort de tout contexte d'empilement créé par les ancêtres
    (will-change, transform, flex, etc.) et garantit qu'il s'affiche au-dessus de tout.
--}}
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    let unreadCount  = 0;
    let notifBusy    = false;
    let dropdownOpen = false;

    // ── Créer le dropdown et l'injecter dans <body> ───────────────
    // En tant qu'enfant direct de body, il n'est plus soumis aux
    // stacking contexts des ancestors (flex container, will-change…)
    const dropdown = document.createElement('div');
    dropdown.id = 'notif-dropdown';
    dropdown.style.cssText = `
        display: none;
        position: fixed;
        width: 320px;
        max-height: 384px;
        overflow-y: auto;
        border-radius: 16px;
        z-index: 99999;
        background: rgba(26,17,69,0.97);
        border: 1px solid rgba(255,255,255,0.1);
        backdrop-filter: blur(40px);
        -webkit-backdrop-filter: blur(40px);
        box-shadow: 0 20px 60px rgba(0,0,0,0.6);
        font-family: 'Sora', sans-serif;
    `;
    dropdown.innerHTML = `
        <div id="notif-header" style="display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-bottom:1px solid rgba(255,255,255,0.05); position:sticky; top:0; background:rgba(26,17,69,0.97); z-index:1;">
            <span style="font-weight:600; font-size:14px; color:#fff;">Notifications</span>
            <button id="notif-mark-all" style="display:none; font-size:11px; color:#ff5e6c; font-weight:500; padding:4px 8px; background:none; border:none; cursor:pointer; font-family:inherit;">
                Tout marquer comme lu
            </button>
        </div>
        <div id="notif-list">
            <div style="padding:32px 16px; text-align:center; color:rgba(255,255,255,0.25); font-size:14px;">Chargement…</div>
        </div>
    `;
    document.body.appendChild(dropdown);

    // ── Helpers ───────────────────────────────────────────────────
    function el(id) { return document.getElementById(id); }
    function escH(v) {
        const d = document.createElement('div');
        d.textContent = String(v ?? '');
        return d.innerHTML;
    }

    // ── Positionner le dropdown sous la cloche ────────────────────
    function positionDropdown() {
        const btn = el('notif-bell-btn');
        if (!btn) return;
        const rect = btn.getBoundingClientRect();
        let left = rect.right - 320;
        let top  = rect.bottom + 8;
        if (left < 8) left = 8;
        if (left + 320 > window.innerWidth - 8) left = window.innerWidth - 328;
        dropdown.style.left = left + 'px';
        dropdown.style.top  = top  + 'px';
    }

    // ── Badge ─────────────────────────────────────────────────────
    function updateBadge(count) {
        unreadCount = Math.max(0, count);
        const badge = el('notif-badge');
        if (!badge) return;
        if (unreadCount > 0) {
            badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    function decrementBadge() {
        updateBadge(unreadCount - 1);
        if (unreadCount === 0) {
            const btn = el('notif-mark-all');
            if (btn) btn.style.display = 'none';
        }
    }

    // ── Ouverture / fermeture ─────────────────────────────────────
    function openDropdown() {
        positionDropdown();
        dropdown.style.display = 'block';
        dropdownOpen = true;
        loadNotifications();
    }

    function closeDropdown() {
        dropdown.style.display = 'none';
        dropdownOpen = false;
    }

    el('notif-bell-btn').addEventListener('click', function (e) {
        e.stopPropagation();
        dropdownOpen ? closeDropdown() : openDropdown();
    });

    // Fermer si clic en dehors
    document.addEventListener('click', function (e) {
        if (!dropdownOpen) return;
        const btn = el('notif-bell-btn');
        if (!dropdown.contains(e.target) && btn && !btn.contains(e.target)) {
            closeDropdown();
        }
    });

    window.addEventListener('resize', function () {
        if (dropdownOpen) positionDropdown();
    });

    // ── Chargement ────────────────────────────────────────────────
    async function loadNotifications() {
        const list = el('notif-list');
        if (list) list.innerHTML = '<div style="padding:32px 16px; text-align:center; color:rgba(255,255,255,0.25); font-size:14px;">Chargement…</div>';
        try {
            const res = await fetch('/notifications', { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            renderNotifications(await res.json());
        } catch (err) {
            console.error('[Notif] load error:', err);
            const list = el('notif-list');
            if (list) list.innerHTML = '<div style="padding:32px 16px; text-align:center; color:rgba(255,255,255,0.25); font-size:14px;">Erreur de chargement</div>';
        }
    }

    // ── Rendu ─────────────────────────────────────────────────────
    function renderNotifications(data) {
        const notifications = data.notifications ?? [];
        const count         = data.unread_count  ?? notifications.length;

        updateBadge(count);

        const markAllBtn = el('notif-mark-all');
        if (markAllBtn) markAllBtn.style.display = notifications.length > 0 ? 'block' : 'none';

        const list = el('notif-list');
        if (!list) return;

        if (notifications.length === 0) {
            list.innerHTML = '<div style="padding:32px 16px; text-align:center; color:rgba(255,255,255,0.25); font-size:14px;">Aucune notification</div>';
            return;
        }

        list.innerHTML = '';
        notifications.forEach(n => {
            const d      = n.data ?? {};
            const href   = d.match_id ? `/messages/${d.match_id}` : null;
            const avatar = d.user_photo ?? d.sender_photo;
            const icon   = d.type === 'new_match' ? '💕' : '💬';

            const item = document.createElement('div');
            item.id = 'notif-item-' + n.id;
            item.style.cssText = 'display:flex; align-items:center; gap:12px; padding:12px 16px; cursor:pointer; transition:background 0.15s;';
            item.onmouseenter = () => item.style.background = 'rgba(255,255,255,0.05)';
            item.onmouseleave = () => item.style.background = 'transparent';

            const avatarHtml = avatar
                ? `<img src="${escH(avatar)}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;flex-shrink:0;" alt="">`
                : `<div style="width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:18px;">${icon}</div>`;

            item.innerHTML = `
                ${avatarHtml}
                <div style="flex:1;min-width:0;">
                    <p style="font-size:12px;color:rgba(255,255,255,0.7);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escH(d.message ?? '')}</p>
                    <p style="font-size:10px;color:rgba(255,255,255,0.25);margin-top:2px;">${escH(n.time ?? '')}</p>
                </div>
                <span style="width:8px;height:8px;border-radius:50%;background:#ff5e6c;flex-shrink:0;"></span>
            `;

            // stopPropagation pour ne pas déclencher le listener "clic en dehors"
            item.addEventListener('click', function (e) {
                e.stopPropagation();
                notifMarkRead(n.id, href);
            });

            list.appendChild(item);
        });
    }

    // ── Marquer une notif individuelle ────────────────────────────
    window.notifMarkRead = function (id, href) {
        const item = el('notif-item-' + id);
        if (item) item.remove();
        decrementBadge();

        const list = el('notif-list');
        if (list && list.children.length === 0) {
            list.innerHTML = '<div style="padding:32px 16px; text-align:center; color:rgba(255,255,255,0.25); font-size:14px;">Aucune notification</div>';
        }

        fetch('/notifications/' + id + '/read', {
            method   : 'POST',
            keepalive: true,
            headers  : { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
        }).catch(() => {});

        if (href) {
            closeDropdown();
            window.location.href = href;
        }
    };

    // ── Tout marquer comme lu ─────────────────────────────────────
    // stopPropagation CRITIQUE : sans ça, le clic remonte au document
    // listener qui ferme le dropdown avant que le fetch ne termine
    el('notif-mark-all').addEventListener('click', async function (e) {
        e.stopPropagation();
        if (notifBusy) return;
        notifBusy = true;

        const btn = this;
        const originalText = btn.textContent;
        btn.textContent     = '…';
        btn.style.opacity   = '0.5';
        btn.style.pointerEvents = 'none';

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

            if (!res.ok) throw new Error('HTTP ' + res.status);

            // Succès → vider l'UI
            updateBadge(0);
            btn.style.display = 'none';
            const list = el('notif-list');
            if (list) list.innerHTML = '<div style="padding:32px 16px; text-align:center; color:rgba(255,255,255,0.25); font-size:14px;">Aucune notification</div>';

        } catch (err) {
            console.error('[Notif] mark-all error:', err);
            await loadNotifications();
        } finally {
            btn.textContent     = originalText;
            btn.style.opacity   = '1';
            btn.style.pointerEvents = 'auto';
            notifBusy = false;
        }
    });

    // ── Polling badge (toutes les 30s) ────────────────────────────
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
                t.style.cssText = 'position:fixed;top:24px;left:50%;transform:translateX(-50%);z-index:99999;padding:12px 24px;border-radius:16px;font-size:14px;font-weight:500;color:#fff;background:linear-gradient(135deg,rgba(255,94,108,0.9),rgba(255,138,92,0.9));border:1px solid rgba(255,255,255,0.1);backdrop-filter:blur(20px);font-family:Sora,sans-serif;';
                t.textContent = '💕 Match avec ' + (data.otherUserName ?? '') + ' !';
                document.body.appendChild(t);
                setTimeout(() => t.remove(), 4000);
            });
        }
    }
})();
</script>