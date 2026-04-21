// ─── Labels & styles par statut ───────────────────────────────────────────────

const STATUS_LABELS = {
    recue:          'Reçue',
    en_preparation: 'En préparation',
    prete:          'Prête',
    livree:         'Livrée',
    annulee:        'Annulée',
};

const STATUS_STYLES = {
    recue:          { bg: '#FEF0EA', color: '#C2623F' },
    en_preparation: { bg: '#FEF0EA', color: '#C2623F' },
    prete:          { bg: '#EAF0FE', color: '#3B6FD4' },
    livree:         { bg: '#EFF5F0', color: '#6B8C6E' },
    annulee:        { bg: '#FFE4E1', color: '#c0392b' },
};

const REPORT_LABELS = {
    open: 'Ouvert',
    in_review: 'En traitement',
    resolved: 'Résolu',
    rejected: 'Rejeté',
};

const REPORT_STYLES = {
    open: { bg: '#FEF0EA', color: '#C2623F' },
    in_review: { bg: '#EAF0FE', color: '#3B6FD4' },
    resolved: { bg: '#EFF5F0', color: '#6B8C6E' },
    rejected: { bg: '#FFE4E1', color: '#c0392b' },
};

let CURRENT_ROLE = '';

// ─── Utilitaires ──────────────────────────────────────────────────────────────

function normalize(status) {
    return String(status ?? '').trim().toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/\s+/g, '_');
}

function toLabel(status) {
    return STATUS_LABELS[normalize(status)] ?? status;
}

// ─── Notifications toast ──────────────────────────────────────────────────────

function showToast(title, message, durationMs = 5000) {
    const stack = document.getElementById('pc-toast-stack');
    if (!stack) return;

    const toast = document.createElement('div');
    toast.style.cssText = `
        background: #FDFAF5;
        border: 1px solid #DDD8CE;
        border-left: 4px solid #C2623F;
        border-radius: 12px;
        padding: 12px 14px;
        box-shadow: 0 4px 16px rgba(44,44,42,.12);
        min-width: 260px;
        max-width: 320px;
        animation: pcSlideIn .25s ease;
    `;

    const t = document.createElement('strong');
    t.style.cssText = 'display:block;font-size:13px;color:#2C2C2A;margin-bottom:3px;';
    t.textContent = title;

    const m = document.createElement('span');
    m.style.cssText = 'font-size:12px;color:#7A7A76;';
    m.textContent = message;

    toast.appendChild(t);
    toast.appendChild(m);
    stack.prepend(toast);

    // Disparaître avec animation
    setTimeout(() => {
        toast.style.transition = 'opacity .4s, transform .4s';
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(20px)';
        setTimeout(() => toast.remove(), 400);
    }, durationMs);
}

// ─── Mise à jour DOM ──────────────────────────────────────────────────────────

function updateOrderBadges(payload) {
    const key = normalize(payload.status);
    const label = toLabel(payload.status);
    const style = STATUS_STYLES[key] ?? STATUS_STYLES.recue;

    // Tous les badges [data-order-status="<id>"]
    document.querySelectorAll(`[data-order-status="${payload.order_id}"]`).forEach(el => {
        el.textContent = label;
        el.style.background = style.bg;
        el.style.color = style.color;
    });

    // Bouton d'action cuisinier [data-order-action="<id>"]
    const cell = document.querySelector(`[data-order-action="${payload.order_id}"]`);
    if (cell) {
        const url = cell.dataset.advanceUrl ?? '';
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
        cell.innerHTML = buildActionBtn(key, url, csrf);
    }
}

function buildActionBtn(status, url, _csrf) {
    // onclick+fetch lit le CSRF frais depuis le meta tag — évite le 419
    const btn = (label, cls, extra = '') =>
        `<button class="pc-btn ${cls}" style="padding:5px 10px;font-size:12px;${extra}"
            onclick="advanceOrder(this,'${url}')">${label}</button>`;

    if (status === 'recue')          return btn('Préparer', 'pc-btn-primary');
    if (status === 'en_preparation') return btn('Prête', '', 'border-color:#6B8C6E;color:#6B8C6E');
    if (status === 'prete')          return btn('Livrée', '');
    return '';
}

// ─── Handlers d'événements ────────────────────────────────────────────────────

function onOrderStatusUpdated(payload) {
    console.log('[PetitChef] OrderStatusUpdated reçu:', payload);
    updateOrderBadges(payload);

    const notify = () => showToast(
        '🔔 Commande mise à jour',
        `Commande #${payload.order_id} → ${toLabel(payload.status)}`
    );

    // Côté client, on laisse 2s avant d'afficher l'alerte.
    if (CURRENT_ROLE === 'client') {
        setTimeout(notify, 2000);
        return;
    }

    notify();
}

function onReportChanged(payload) {
    const action = payload.action === 'created' ? 'nouveau signalement' : 'signalement mis à jour';

    showToast(
        '📣 Signalement',
        `#${payload.report_id} · ${action}`
    );

    patchAdminReport(payload);
    patchClientReport(payload);
}

function formatReportType(type) {
    return String(type ?? '')
        .replaceAll('_', ' ')
        .replace(/\b\w/g, c => c.toUpperCase());
}

function getReportBadge(status) {
    const key = normalize(status);
    const style = REPORT_STYLES[key] ?? REPORT_STYLES.open;
    const label = REPORT_LABELS[key] ?? status;
    return { key, style, label };
}

function upsertAdminCard(payload) {
    const list = document.getElementById('admin-reports-list');
    if (!list) return;

    const existing = list.querySelector(`[data-report-card="${payload.report_id}"]`);
    if (existing) return existing;

    const empty = document.getElementById('admin-reports-empty');
    if (empty) empty.remove();

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    const badge = getReportBadge(payload.status);
    const wrapper = document.createElement('div');
    wrapper.innerHTML = `
        <form class="pc-card" data-report-card="${payload.report_id}" method="POST" action="/admin/reports/${payload.report_id}/status" style="padding:12px;border-radius:12px;display:grid;gap:10px;">
            <input type="hidden" name="_token" value="${csrf}">
            <input type="hidden" name="_method" value="PATCH">
            <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:flex-start;">
                <div>
                    <div style="font-weight:600;" data-report-type="${payload.report_id}">${formatReportType(payload.type)}</div>
                    <div style="font-size:12px;color:var(--mid-gray);" data-report-meta="${payload.report_id}">Client: ${payload.client_name ?? '-'} · Cuisinier: ${payload.cook_name ?? '-'}</div>
                    <div style="font-size:12px;color:var(--mid-gray);" data-report-meta2="${payload.report_id}">Commande: #${payload.order_id ?? '-'} · Plat: ${payload.dish_name ?? '-'}</div>
                </div>
                <span class="pc-status" data-report-status="${payload.report_id}" style="background:${badge.style.bg};color:${badge.style.color}">${badge.label}</span>
            </div>
            <div style="font-size:13px;line-height:1.5;" data-report-description="${payload.report_id}">${payload.description ?? ''}</div>
            <textarea name="admin_note" class="pc-textarea" data-report-admin-note-input="${payload.report_id}" placeholder="Note admin...">${payload.admin_note ?? ''}</textarea>
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <select name="status" class="pc-select" data-report-status-select="${payload.report_id}" style="max-width:180px;">
                    <option value="open">Ouvert</option>
                    <option value="in_review">En traitement</option>
                    <option value="resolved">Résolu</option>
                    <option value="rejected">Rejeté</option>
                </select>
                <button class="pc-btn pc-btn-primary" type="submit">Mettre à jour</button>
            </div>
        </form>
    `;

    const form = wrapper.firstElementChild;
    list.prepend(form);
    return form;
}

function patchAdminReport(payload) {
    const card = upsertAdminCard(payload);
    if (!card) return;

    const badge = getReportBadge(payload.status);
    const statusEl = card.querySelector(`[data-report-status="${payload.report_id}"]`);
    if (statusEl) {
        statusEl.textContent = badge.label;
        statusEl.style.background = badge.style.bg;
        statusEl.style.color = badge.style.color;
    }

    const selectEl = card.querySelector(`[data-report-status-select="${payload.report_id}"]`);
    if (selectEl) selectEl.value = badge.key;

    const noteInput = card.querySelector(`[data-report-admin-note-input="${payload.report_id}"]`);
    if (noteInput && document.activeElement !== noteInput) {
        noteInput.value = payload.admin_note ?? '';
    }

    const descEl = card.querySelector(`[data-report-description="${payload.report_id}"]`);
    if (descEl && payload.description) descEl.textContent = payload.description;
}

function upsertClientCard(payload) {
    let list = document.getElementById('client-reports-list');
    const empty = document.getElementById('client-reports-empty');

    if (!list && empty) {
        list = document.createElement('div');
        list.id = 'client-reports-list';
        list.style.cssText = 'display:flex;flex-direction:column;gap:12px';
        empty.replaceWith(list);
    }

    if (!list) return null;

    const existing = list.querySelector(`[data-report-card="${payload.report_id}"]`);
    if (existing) return existing;

    const badge = getReportBadge(payload.status);
    const card = document.createElement('div');
    card.className = 'pc-card';
    card.setAttribute('data-report-card', String(payload.report_id));
    card.style.padding = '18px';
    card.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:start;gap:12px;flex-wrap:wrap">
            <div style="flex:1">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;flex-wrap:wrap">
                    <strong style="font-size:14px" data-report-type="${payload.report_id}">${formatReportType(payload.type)}</strong>
                    <span data-report-status="${payload.report_id}" style="padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;background:${badge.style.bg};color:${badge.style.color}">${badge.label}</span>
                </div>
                <div data-report-meta="${payload.report_id}" style="font-size:12px;color:var(--mid-gray);margin-bottom:8px">
                    Cuisinier : <strong>${payload.cook_name ?? '—'}</strong>${payload.order_id ? ' · Commande #' + payload.order_id : ''}
                </div>
                <div data-report-description="${payload.report_id}" style="font-size:13px;color:var(--charcoal);line-height:1.6">${payload.description ?? ''}</div>
            </div>
        </div>
    `;
    list.prepend(card);
    return card;
}

function patchClientReport(payload) {
    const card = upsertClientCard(payload);
    if (!card) return;

    const badge = getReportBadge(payload.status);
    const statusEl = card.querySelector(`[data-report-status="${payload.report_id}"]`);
    if (statusEl) {
        statusEl.textContent = badge.label;
        statusEl.style.background = badge.style.bg;
        statusEl.style.color = badge.style.color;
    }

    const meta = card.querySelector(`[data-report-meta="${payload.report_id}"]`);
    if (meta && payload.cook_name) {
        meta.innerHTML = `Cuisinier : <strong>${payload.cook_name}</strong>${payload.order_id ? ' · Commande #' + payload.order_id : ''}`;
    }

    if (payload.description) {
        const desc = card.querySelector(`[data-report-description="${payload.report_id}"]`);
        if (desc) desc.textContent = payload.description;
    }

    let note = card.querySelector(`[data-report-admin-note="${payload.report_id}"]`);
    if (payload.admin_note) {
        if (!note) {
            note = document.createElement('div');
            note.setAttribute('data-report-admin-note', String(payload.report_id));
            note.style.cssText = 'margin-top:10px;padding:10px 14px;background:#EAF0FE;border-radius:8px;font-size:12px;color:#2B50A0;border-left:3px solid #3B6FD4';
            card.querySelector('[data-report-description]')?.insertAdjacentElement('afterend', note);
        }
        note.innerHTML = `<strong>Réponse admin :</strong> ${payload.admin_note}`;
    }
}

// ─── Abonnements aux canaux ───────────────────────────────────────────────────

function subscribeOrder(orderId) {
    window.Echo.private(`order.${orderId}`)
        .listen('.OrderStatusUpdated', onOrderStatusUpdated)
        .error(err => console.warn(`[PetitChef] Canal order.${orderId}:`, err));
}

function subscribeKitchen(cookId) {
    window.Echo.private(`kitchen.${cookId}`)
        .listen('.OrderStatusUpdated', onOrderStatusUpdated)
        .listen('.ReportChanged', onReportChanged)
        .error(err => console.warn(`[PetitChef] Canal kitchen.${cookId}:`, err));
}

function subscribeAdmin() {
    window.Echo.private('admin.stream')
        .listen('.OrderStatusUpdated', onOrderStatusUpdated)
        .listen('.ReportChanged', onReportChanged)
        .error(err => console.warn('[PetitChef] Canal admin.stream:', err));
}

function subscribeClient(clientId) {
    window.Echo.private(`client.${clientId}`)
        .listen('.ReportChanged', onReportChanged)
        .error(err => console.warn(`[PetitChef] Canal client.${clientId}:`, err));
}

// ─── Init principale ──────────────────────────────────────────────────────────

function init() {
    if (!window.Echo) {
        console.warn('[PetitChef] Echo non disponible');
        return;
    }

    const root = document.documentElement;
    const orderIds = (root.dataset.orderIds ?? '')
        .split(',').map(s => s.trim()).filter(Boolean);
    const kitchenId = (root.dataset.kitchenChannel ?? '').trim();
    const authRole = (root.dataset.authRole ?? '').trim();
    const authId = (root.dataset.authId ?? '').trim();

    CURRENT_ROLE = authRole;

    orderIds.forEach(id => subscribeOrder(id));
    if (kitchenId) subscribeKitchen(kitchenId);
    if (authRole === 'admin') subscribeAdmin();
    if (authRole === 'client' && authId) subscribeClient(authId);

    if (orderIds.length || kitchenId || authRole) {
        console.log(`[PetitChef] Abonné à ${orderIds.length} commande(s)${kitchenId ? ' + cuisine #' + kitchenId : ''}${authRole ? ' + role ' + authRole : ''}`);
    }
}

// Attendre que Echo soit connecté avant de s'abonner
window.addEventListener('echo:ready', init);

// Fallback : si echo:ready est déjà passé (navigation SPA)
if (window.Echo?.connector?.pusher?.connection?.state === 'connected') {
    init();
}
