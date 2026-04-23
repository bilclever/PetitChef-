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

const REPORT_STATUS_LABELS = {
    open: 'Ouvert',
    in_review: 'En traitement',
    resolved: 'Résolu',
    rejected: 'Rejeté',
};

const ACCOUNT_STATUS_LABELS = {
    active: 'Actif',
    suspended: 'Suspendu',
    banned: 'Banni',
};

// ─── Utilitaires ──────────────────────────────────────────────────────────────

function normalize(status) {
    return String(status ?? '').trim().toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/\s+/g, '_');
}

function toLabel(status) {
    return STATUS_LABELS[normalize(status)] ?? status;
}

function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

// ─── Toast notifications ──────────────────────────────────────────────────────

export function showToast(title, message, type = 'info', durationMs = 5000) {
    const stack = document.getElementById('pc-toast-stack');
    if (!stack) return;

    const borderColor = type === 'success' ? '#6B8C6E'
        : type === 'error' ? '#c0392b'
        : type === 'warning' ? '#c47a20'
        : '#C2623F';

    const toast = document.createElement('div');
    toast.style.cssText = `
        background:#FDFAF5;border:1px solid #DDD8CE;
        border-left:4px solid ${borderColor};border-radius:12px;
        padding:12px 14px;box-shadow:0 4px 16px rgba(44,44,42,.12);
        min-width:260px;max-width:320px;animation:pcSlideIn .25s ease;
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

    setTimeout(() => {
        toast.style.transition = 'opacity .4s, transform .4s';
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(20px)';
        setTimeout(() => toast.remove(), 400);
    }, durationMs);
}

// Exposer globalement pour le layout
window.pcShowToast = showToast;

// ─── Mise à jour DOM : statut commande ────────────────────────────────────────

function updateOrderBadges(payload) {
    const key = normalize(payload.status);
    const label = toLabel(payload.status);
    const style = STATUS_STYLES[key] ?? STATUS_STYLES.recue;

    document.querySelectorAll(`[data-order-status="${payload.order_id}"]`).forEach(el => {
        el.textContent = label;
        el.style.background = style.bg;
        el.style.color = style.color;
    });

    // Bouton d'action cuisinier
    const cell = document.querySelector(`[data-order-action="${payload.order_id}"]`);
    if (cell) {
        const url = cell.dataset.advanceUrl ?? '';
        cell.innerHTML = buildActionBtn(key, url);
    }
}

function buildActionBtn(status, url) {
    const btn = (label, cls, extra = '') =>
        `<button class="pc-btn ${cls}" style="padding:5px 10px;font-size:12px;${extra}"
            onclick="advanceOrder(this,'${url}')">${label}</button>`;

    if (status === 'recue')          return btn('Préparer', 'pc-btn-primary');
    if (status === 'en_preparation') return btn('Prête', '', 'border-color:#6B8C6E;color:#6B8C6E');
    if (status === 'prete')          return btn('Livrée', '');
    return '';
}

// ─── Mise à jour DOM : nouvelle commande (cuisinier) ─────────────────────────

function handleNewOrder(payload) {
    showToast(
        '🆕 Nouvelle commande !',
        `Commande #${payload.order_id} de ${payload.client_name} — ${Number(payload.total_price).toLocaleString('fr')} FCFA`,
        'info',
        8000
    );

    // Ajouter une ligne dans le tableau des commandes si on est sur le dashboard cuisinier
    const tbody = document.querySelector('[data-cook-orders-body]');
    if (tbody) {
        const tr = document.createElement('tr');
        tr.setAttribute('data-order-row', payload.order_id);
        tr.innerHTML = `
            <td><a href="${payload.order_url}" style="color:var(--terracotta);text-decoration:none;font-weight:600">#${payload.order_id}</a></td>
            <td>${payload.client_name}</td>
            <td style="color:var(--mid-gray)">—</td>
            <td>${Number(payload.total_price).toLocaleString('fr')} F</td>
            <td><span class="pc-status pc-status-pending" data-order-status="${payload.order_id}">Reçue</span></td>
            <td data-order-action="${payload.order_id}" data-advance-url="${payload.advance_url}">
                ${buildActionBtn('recue', payload.advance_url)}
            </td>
        `;
        tbody.insertBefore(tr, tbody.firstChild);

        // Retirer le message "aucune commande" si présent
        const empty = tbody.querySelector('[data-empty-row]');
        if (empty) empty.remove();
    }
}

// ─── Mise à jour DOM : modification plat (menu) ───────────────────────────────

function handleDishUpdated(payload) {
    // Trouver la carte du plat sur la page menu
    const card = document.querySelector(`[data-dish-id="${payload.dish_id}"]`);
    if (!card) return;

    // Mettre à jour l'image
    if (payload.photo_url) {
        const img = card.querySelector('[data-dish-img]');
        if (img) {
            img.src = payload.photo_url + '?t=' + Date.now(); // cache-bust
        }
    }

    // Mettre à jour le nom
    const nameEl = card.querySelector('[data-dish-name]');
    if (nameEl) nameEl.textContent = payload.name;

    // Mettre à jour le prix
    const priceEl = card.querySelector('[data-dish-price]');
    if (priceEl) priceEl.textContent = Number(payload.price).toLocaleString('fr') + ' FCFA';

    // Mettre à jour le stock
    const stockEl = card.querySelector('[data-dish-stock]');
    if (stockEl) {
        stockEl.textContent = payload.quantity + ' restants';
        stockEl.style.color = payload.quantity <= 3 ? 'var(--terracotta)' : '';
        stockEl.style.borderColor = payload.quantity <= 3 ? 'var(--terracotta)' : '';
    }

    // Badge plat du jour
    const ofDayBadge = card.querySelector('[data-dish-ofday]');
    if (ofDayBadge) ofDayBadge.style.display = payload.is_of_day ? 'block' : 'none';

    // Bouton ajouter
    const addBtn = card.querySelector('[data-add-btn]');
    const addInput = card.querySelector('[data-add-input]');
    const unavailable = !payload.is_active || payload.quantity < 1;
    if (addBtn) {
        addBtn.disabled = unavailable;
        if (payload.quantity < 1) addBtn.textContent = 'Épuisé';
        else if (!payload.is_active) addBtn.textContent = 'Indisponible';
        else addBtn.textContent = 'Ajouter';
    }
    if (addInput) addInput.disabled = unavailable;
}

// ─── Mise à jour DOM : statut boutique (menu) ─────────────────────────────────

function handleShopStatusChanged(payload) {
    const isOpen = payload.is_open;

    // Mettre à jour tous les éléments liés à ce cuisinier sur la page menu
    document.querySelectorAll(`[data-cook-id="${payload.cook_id}"]`).forEach(el => {
        const dot = el.querySelector('[data-shop-dot]');
        const label = el.querySelector('[data-shop-label]');
        const overlay = el.querySelector('[data-shop-overlay]');
        const addBtn = el.querySelector('[data-add-btn]');
        const addInput = el.querySelector('[data-add-input]');

        if (dot) {
            dot.style.background = isOpen ? '#2ecc71' : '#c0392b';
        }
        if (label) {
            label.textContent = payload.cook_name;
        }
        if (overlay) {
            overlay.style.display = isOpen ? 'none' : 'flex';
        }
        if (addBtn) {
            addBtn.disabled = !isOpen;
            addBtn.textContent = isOpen ? 'Ajouter' : 'Fermé';
        }
        if (addInput) {
            addInput.disabled = !isOpen;
        }

        // Opacité de la carte
        el.style.opacity = isOpen ? '1' : '0.55';
    });

    showToast(
        isOpen ? '🟢 Boutique ouverte' : '🔴 Boutique fermée',
        `${payload.cook_name} ${isOpen ? 'accepte maintenant les commandes' : 'a clôturé ses commandes'}`,
        isOpen ? 'success' : 'warning'
    );
}

// ─── Mise à jour DOM : paiement ───────────────────────────────────────────────

function handlePaymentUpdate(payload) {
    // Mettre à jour le badge de paiement sur la page détail commande
    const payBadge = document.querySelector(`[data-payment-status="${payload.order_id}"]`);
    if (payBadge && payload.is_paid) {
        payBadge.innerHTML = '✅ <strong style="color:#6B8C6E">Commande payée</strong>';
        // Cacher le bouton payer
        const payBtn = document.querySelector(`[data-pay-btn="${payload.order_id}"]`);
        if (payBtn) payBtn.closest('form')?.remove();
    }
}

function reportStatusClass(status) {
    const key = normalize(status);
    if (key === 'resolved') return 'pc-status-approved';
    if (key === 'rejected') return 'pc-status-rejected';

    return 'pc-status-pending';
}

function renderReportCard(payload) {
    const reportId = Number(payload.report_id || payload.id || 0);
    const status = String(payload.status || 'open');
    const statusLabel = REPORT_STATUS_LABELS[status] ?? status;
    const typeLabel = String(payload.type || '').replaceAll('_', ' ');
    const csrf = getCsrf();
    const action = `/admin/reports/${reportId}/status`;
    const options = Object.entries(REPORT_STATUS_LABELS)
        .map(([key, label]) => `<option value="${key}" ${key === status ? 'selected' : ''}>${label}</option>`)
        .join('');

    return `
        <form class="pc-card" data-report-card="${reportId}" method="POST" action="${action}" style="padding:12px;border-radius:12px;display:grid;gap:10px;">
            <input type="hidden" name="_token" value="${escapeHtml(csrf)}">
            <input type="hidden" name="_method" value="PATCH">
            <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:flex-start;">
                <div>
                    <div style="font-weight:600;" data-report-type="${reportId}">${escapeHtml(typeLabel ? typeLabel.charAt(0).toUpperCase() + typeLabel.slice(1) : 'Signalement')}</div>
                    <div style="font-size:12px;color:var(--mid-gray);" data-report-meta="${reportId}">Client: ${escapeHtml(payload.client_name || '-')} · Cuisinier: ${escapeHtml(payload.cook_name || '-')}</div>
                    <div style="font-size:12px;color:var(--mid-gray);" data-report-meta2="${reportId}">Commande: #${escapeHtml(payload.order_id ?? '-')} · Plat: ${escapeHtml(payload.dish_name || '-')}</div>
                </div>
                <span class="pc-status ${reportStatusClass(status)}" data-report-status="${reportId}">${escapeHtml(statusLabel)}</span>
            </div>
            <div style="font-size:13px;line-height:1.5;" data-report-description="${reportId}">${escapeHtml(payload.description || '')}</div>
            <textarea name="admin_note" class="pc-textarea" data-report-admin-note-input="${reportId}" placeholder="Note admin...">${escapeHtml(payload.admin_note || '')}</textarea>
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <select name="status" class="pc-select" data-report-status-select="${reportId}" style="max-width:180px;">${options}</select>
                <button class="pc-btn pc-btn-primary" type="submit">Mettre à jour</button>
            </div>
        </form>
    `;
}

function handleReportChanged(payload) {
    const reportId = Number(payload.report_id || payload.id || 0);
    if (!reportId) return;

    const card = document.querySelector(`[data-report-card="${reportId}"]`);
    const status = String(payload.status || 'open');
    const statusLabel = REPORT_STATUS_LABELS[status] ?? status;

    if (card) {
        const statusEl = card.querySelector(`[data-report-status="${reportId}"]`);
        if (statusEl) {
            statusEl.textContent = statusLabel;
            statusEl.classList.remove('pc-status-pending', 'pc-status-approved', 'pc-status-rejected');
            statusEl.classList.add(reportStatusClass(status));
        }

        const statusSelect = card.querySelector(`[data-report-status-select="${reportId}"]`);
        if (statusSelect) statusSelect.value = status;

        const noteInput = card.querySelector(`[data-report-admin-note-input="${reportId}"]`);
        if (noteInput && payload.admin_note !== undefined) noteInput.value = payload.admin_note ?? '';

        const meta = card.querySelector(`[data-report-meta="${reportId}"]`);
        if (meta) meta.textContent = `Client: ${payload.client_name || '-'} · Cuisinier: ${payload.cook_name || '-'}`;

        const meta2 = card.querySelector(`[data-report-meta2="${reportId}"]`);
        if (meta2) meta2.textContent = `Commande: #${payload.order_id ?? '-'} · Plat: ${payload.dish_name || '-'}`;

        const desc = card.querySelector(`[data-report-description="${reportId}"]`);
        if (desc && payload.description !== undefined) desc.textContent = payload.description || '';
    } else if (payload.action === 'created') {
        const list = document.getElementById('admin-reports-list');
        if (list) {
            const empty = document.getElementById('admin-reports-empty');
            if (empty) empty.remove();

            list.insertAdjacentHTML('afterbegin', renderReportCard(payload));
        }
    }

    showToast(
        payload.action === 'created' ? '🆕 Nouveau signalement' : 'Signalement mis à jour',
        `#${reportId} · ${statusLabel}`,
        payload.action === 'created' ? 'warning' : 'info',
        8000
    );
}

function addPendingCookCards(payload) {
    const userId = Number(payload.user_id || 0);
    if (!userId) return;

    const csrf = getCsrf();
    const action = `/admin/cooks/${userId}/status`;
    const name = escapeHtml(payload.name || `Cuisinier #${userId}`);
    const email = escapeHtml(payload.email || '-');
    const phone = escapeHtml(payload.phone || '-');

    const quick = document.getElementById('admin-pending-cooks-quick');
    if (quick && !quick.querySelector(`[data-pending-cook-quick="${userId}"]`)) {
        const empty = document.getElementById('admin-pending-cooks-quick-empty');
        if (empty) empty.remove();

        quick.insertAdjacentHTML('afterbegin', `
            <div class="pc-card" data-pending-cook-quick="${userId}" style="padding:10px;border-radius:12px;">
                <div style="font-weight:600;">${name}</div>
                <div style="font-size:12px;color:var(--mid-gray);">${email}</div>
                <div style="display:flex;gap:8px;margin-top:8px;">
                    <form method="POST" action="${action}">
                        <input type="hidden" name="_token" value="${escapeHtml(csrf)}">
                        <input type="hidden" name="_method" value="PATCH">
                        <input type="hidden" name="decision" value="approve">
                        <button class="pc-btn pc-btn-primary" type="submit" style="padding:6px 10px;">Valider</button>
                    </form>
                    <form method="POST" action="${action}">
                        <input type="hidden" name="_token" value="${escapeHtml(csrf)}">
                        <input type="hidden" name="_method" value="PATCH">
                        <input type="hidden" name="decision" value="reject">
                        <button class="pc-btn" type="submit" style="padding:6px 10px;">Rejeter</button>
                    </form>
                </div>
            </div>
        `);
    }

    const detailed = document.getElementById('admin-pending-cooks-detailed');
    if (detailed && !detailed.querySelector(`[data-pending-cook-detailed="${userId}"]`)) {
        const empty = document.getElementById('admin-pending-cooks-detailed-empty');
        if (empty) empty.remove();

        detailed.insertAdjacentHTML('afterbegin', `
            <form class="pc-card" data-pending-cook-detailed="${userId}" method="POST" action="${action}" style="padding:12px;border-radius:12px;display:grid;gap:10px;">
                <input type="hidden" name="_token" value="${escapeHtml(csrf)}">
                <input type="hidden" name="_method" value="PATCH">
                <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:flex-start;">
                    <div>
                        <div style="font-weight:600;">${name}</div>
                        <div style="font-size:12px;color:var(--mid-gray);">${email} · ${phone}</div>
                    </div>
                    <span class="pc-status pc-status-pending">En attente</span>
                </div>
                <textarea name="comment" class="pc-textarea" placeholder="Commentaire obligatoire si rejet..."></textarea>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <button class="pc-btn pc-btn-primary" type="submit" name="decision" value="approve">Valider</button>
                    <button class="pc-btn" type="submit" name="decision" value="reject" style="border-color:#e6b2ac;color:#c0392b">Rejeter</button>
                </div>
            </form>
        `);
    }
}

function removePendingCookCards(userId) {
    document.querySelectorAll(`[data-pending-cook-quick="${userId}"]`).forEach(el => el.remove());
    document.querySelectorAll(`[data-pending-cook-detailed="${userId}"]`).forEach(el => el.remove());

    const quick = document.getElementById('admin-pending-cooks-quick');
    if (quick && !quick.querySelector('[data-pending-cook-quick]') && !document.getElementById('admin-pending-cooks-quick-empty')) {
        quick.insertAdjacentHTML('beforeend', '<div id="admin-pending-cooks-quick-empty" style="color:var(--mid-gray);font-size:13px;">Aucun profil en attente.</div>');
    }

    const detailed = document.getElementById('admin-pending-cooks-detailed');
    if (detailed && !detailed.querySelector('[data-pending-cook-detailed]') && !document.getElementById('admin-pending-cooks-detailed-empty')) {
        detailed.insertAdjacentHTML('beforeend', '<div id="admin-pending-cooks-detailed-empty" style="color:var(--mid-gray);font-size:13px;">Aucun profil en attente.</div>');
    }
}

function handleUserChanged(payload) {
    const userId = Number(payload.user_id || 0);
    const eventType = payload.event_type || '';

    if (eventType === 'registered_pending' && payload.role === 'cook') {
        addPendingCookCards(payload);
        showToast('👨‍🍳 Nouveau cuisinier', `${payload.name} est en attente de validation`, 'info', 9000);

        return;
    }

    if (eventType === 'approval_updated') {
        removePendingCookCards(userId);
        const state = payload.approval_status === 'approved' ? 'validé' : 'rejeté';
        showToast('Validation profil', `${payload.name || 'Cuisinier'} ${state}`, 'info');

        return;
    }

    if (eventType === 'account_status_updated') {
        const status = String(payload.account_status || 'active');
        const statusLabel = ACCOUNT_STATUS_LABELS[status] ?? status;

        const statusBadge = document.querySelector(`[data-admin-user-status-label="${userId}"]`);
        if (statusBadge) {
            statusBadge.textContent = statusLabel;
            statusBadge.classList.remove('pc-status-approved', 'pc-status-rejected');
            statusBadge.classList.add(status === 'active' ? 'pc-status-approved' : 'pc-status-rejected');
        }

        const statusSelect = document.querySelector(`[data-admin-user-status-select="${userId}"]`);
        if (statusSelect) statusSelect.value = status;

        const reasonInput = document.querySelector(`[data-admin-user-status-reason="${userId}"]`);
        if (reasonInput && payload.account_status_reason !== undefined) {
            reasonInput.value = payload.account_status_reason ?? '';
        }

        showToast('Statut utilisateur', `${payload.name || 'Utilisateur'} → ${statusLabel}`, status === 'active' ? 'success' : 'warning');
    }
}

// ─── Abonnements aux canaux ───────────────────────────────────────────────────

function subscribeOrder(orderId) {
    window.Echo.private(`order.${orderId}`)
        .listen('.OrderStatusUpdated', payload => {
            updateOrderBadges(payload);
            if (payload.is_paid !== undefined) handlePaymentUpdate(payload);
            showToast('🔔 Commande mise à jour', `Commande #${payload.order_id} → ${toLabel(payload.status)}`);
        })
        .error(err => console.warn(`[PetitChef] Canal order.${orderId}:`, err));
}

function subscribeKitchen(cookId) {
    window.Echo.private(`kitchen.${cookId}`)
        .listen('.OrderStatusUpdated', payload => {
            updateOrderBadges(payload);
            showToast('🔔 Statut mis à jour', `Commande #${payload.order_id} → ${toLabel(payload.status)}`);
        })
        .listen('.NewOrderReceived', payload => {
            handleNewOrder(payload);
        })
        .error(err => console.warn(`[PetitChef] Canal kitchen.${cookId}:`, err));
}

function subscribeMenu() {
    // Canal public — pas besoin d'auth
    window.Echo.channel('menu.updates')
        .listen('.ShopStatusChanged', payload => {
            handleShopStatusChanged(payload);
        })
        .listen('.DishUpdated', payload => {
            handleDishUpdated(payload);
        });
}

function subscribeAdmin() {
    window.Echo.private('admin.stream')
        .listen('.OrderStatusUpdated', payload => {
            updateOrderBadges(payload);
        })
        .listen('.NewOrderReceived', payload => {
            showToast('🆕 Nouvelle commande', `#${payload.order_id} — ${payload.client_name}`, 'info', 8000);
            // Incrémenter le compteur si présent
            const counter = document.querySelector('[data-admin-orders-count]');
            if (counter) counter.textContent = parseInt(counter.textContent || '0') + 1;
        })
        .listen('.ReportChanged', payload => {
            handleReportChanged(payload);
        })
        .listen('.UserChanged', payload => {
            handleUserChanged(payload);
        })
        .error(err => console.warn('[PetitChef] Canal admin:', err));
}

// ─── Init principale ──────────────────────────────────────────────────────────

function init() {
    if (!window.Echo) {
        console.warn('[PetitChef] Echo non disponible');
        return;
    }

    const root = document.documentElement;
    const orderIds = (root.dataset.orderIds ?? '').split(',').map(s => s.trim()).filter(Boolean);
    const kitchenId = (root.dataset.kitchenChannel ?? '').trim();
    const isAdmin = root.dataset.authRole === 'admin';
    const isOnMenu = !!document.querySelector('[data-menu-page]');

    orderIds.forEach(id => subscribeOrder(id));
    if (kitchenId) subscribeKitchen(kitchenId);
    if (isAdmin) subscribeAdmin();
    if (isOnMenu || isAdmin) subscribeMenu();

    if (orderIds.length || kitchenId || isAdmin) {
        console.log(`[PetitChef] Abonné — ${orderIds.length} commande(s)${kitchenId ? ' + cuisine' : ''}${isAdmin ? ' + admin' : ''}`);
    }
}

window.addEventListener('echo:ready', init);
if (window.Echo?.connector?.pusher?.connection?.state === 'connected') init();
