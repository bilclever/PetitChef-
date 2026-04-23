/**
 * Gestion centralisée des actions fetch pour tout le projet
 * Remplace les forms POST/PATCH/DELETE par des appels fetch
 */

// ─── Action générique ──────────────────────────────────────────────────────

async function performAction(url, method = 'POST', data = {}) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    
    const isFormData = data instanceof FormData;
    const headers = {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
    };
    
    if (!isFormData) {
        headers['Content-Type'] = 'application/json';
    }
    
    const options = {
        method,
        headers,
    };
    
    if (method !== 'GET' && method !== 'HEAD') {
        options.body = isFormData ? data : JSON.stringify(data);
    }
    
    try {
        const response = await fetch(url, options);
        const json = await response.json();
        
        if (json.success) {
            showToast('✅ Succès', json.message);
            return json;
        } else {
            showToast('❌ Erreur', json.message || 'Une erreur est survenue.');
            return null;
        }
    } catch (err) {
        console.error('[PetitChef] Action error:', err);
        showToast('❌ Erreur', 'Erreur réseau ou serveur.');
        return null;
    }
}

// ─── Actions cart ──────────────────────────────────────────────────────────

async function cartAdd(dishId, quantity = 1) {
    const result = await performAction(`/panier/plats/${dishId}`, 'POST', { quantity });
    if (result) {
        // Optionnel: mettre à jour le badge du panier
        updateCartBadge(result.totals);
    }
    return result;
}

async function cartUpdate(dishId, quantity) {
    const result = await performAction(`/panier/plats/${dishId}`, 'PATCH', { quantity });
    if (result) {
        updateCartBadge(result.totals);
    }
    return result;
}

async function cartRemove(dishId) {
    const result = await performAction(`/panier/plats/${dishId}`, 'DELETE', {});
    if (result) {
        updateCartBadge(result.totals);
        // Optionnel: supprimer la ligne du DOM
        const row = document.querySelector(`[data-cart-item="${dishId}"]`);
        if (row) row.remove();
    }
    return result;
}

async function cartClear() {
    const result = await performAction('/panier', 'DELETE', {});
    if (result) {
        updateCartBadge(result.totals);
        // Optionnel: vider le tableau
        const tbody = document.querySelector('[data-cart-body]');
        if (tbody) tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--mid-gray);padding:24px">Panier vide</td></tr>';
    }
    return result;
}

function updateCartBadge(totals) {
    const badge = document.querySelector('[data-cart-count]');
    if (badge && totals) {
        badge.textContent = totals.items_count;
    }
}

// ─── Actions commandes client ──────────────────────────────────────────────

async function clientOrderCreate(pickupTime, fulfillmentType, paymentMethod) {
    const result = await performAction('/client/orders', 'POST', {
        pickup_time: pickupTime,
        fulfillment_type: fulfillmentType,
        payment_method: paymentMethod,
    });
    if (result && result.redirect_to) {
        setTimeout(() => window.location.href = result.redirect_to, 1000);
    }
    return result;
}

async function clientOrderPay(orderId) {
    const result = await performAction(`/client/orders/${orderId}/pay`, 'PATCH', {});
    if (result) {
        // Mettre à jour le badge de paiement
        const badge = document.querySelector(`[data-order-payment="${orderId}"]`);
        if (badge) {
            badge.textContent = 'Payée';
            badge.style.color = 'var(--sage)';
            badge.style.background = '#EFF5F0';
        }
        // Masquer le bouton payer
        const btn = document.querySelector(`[data-order-pay-btn="${orderId}"]`);
        if (btn) btn.style.display = 'none';
    }
    return result;
}

// ─── Actions plats cuisinier ───────────────────────────────────────────────

async function cookDishCreate(formData) {
    const result = await performAction('/cuisinier/plats', 'POST', formData);
    if (result && result.redirect_to) {
        setTimeout(() => window.location.href = result.redirect_to, 1000);
    }
    return result;
}

async function cookDishUpdate(dishId, formData) {
    const result = await performAction(`/cuisinier/plats/${dishId}`, 'PUT', formData);
    if (result && result.redirect_to) {
        setTimeout(() => window.location.href = result.redirect_to, 1000);
    }
    return result;
}

async function cookDishDelete(dishId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce plat ?')) return;
    const result = await performAction(`/cuisinier/plats/${dishId}`, 'DELETE', {});
    if (result && result.redirect_to) {
        setTimeout(() => window.location.href = result.redirect_to, 1000);
    }
    return result;
}

async function cookDishToggleOfDay(dishId) {
    const result = await performAction(`/cuisinier/plats/${dishId}/plat-du-jour`, 'PATCH', {});
    if (result) {
        // Mettre à jour le badge
        const badge = document.querySelector(`[data-dish-of-day="${dishId}"]`);
        if (badge) {
            badge.style.display = result.is_of_day ? 'inline-block' : 'none';
        }
    }
    return result;
}

// ─── Actions shop cuisinier ───────────────────────────────────────────────

async function cookShopToggle() {
    const result = await performAction('/cuisinier/boutique/statut', 'PATCH', {});
    if (result) {
        // Mettre à jour le badge de statut
        const status = document.querySelector('[data-shop-status]');
        if (status) {
            status.innerHTML = result.is_open
                ? '<span style="width:8px;height:8px;border-radius:50%;background:#2ecc71;display:inline-block;margin-right:6px"></span><span style="font-size:12px;font-weight:600;color:var(--sage)">Ouvert</span>'
                : '<span style="width:8px;height:8px;border-radius:50%;background:#c0392b;display:inline-block;margin-right:6px"></span><span style="font-size:12px;font-weight:600;color:#c0392b">Fermé</span>';
        }
        // Mettre à jour le bouton
        const btn = document.querySelector('[data-shop-toggle-btn]');
        if (btn) {
            btn.textContent = result.is_open ? '🔴 Fermer ma boutique' : '🟢 Ouvrir ma boutique';
            btn.className = result.is_open ? 'pc-btn' : 'pc-btn pc-btn-primary';
        }
    }
    return result;
}

async function cookShopSetClosingTime(closingTime) {
    const result = await performAction('/cuisinier/boutique/cloture', 'PATCH', { shop_closes_at: closingTime });
    if (result) {
        const input = document.querySelector('[data-shop-closing-input]');
        if (input) input.value = result.closing_time || '';
    }
    return result;
}

// ─── Actions admin ────────────────────────────────────────────────────────

async function adminApproveCook(userId, decision, comment = '') {
    const result = await performAction(`/admin/cooks/${userId}/status`, 'PATCH', {
        decision,
        comment,
    });
    if (result) {
        // Optionnel: supprimer la ligne du tableau
        const row = document.querySelector(`[data-cook-row="${userId}"]`);
        if (row) row.remove();
    }
    return result;
}

async function adminUpdateReportStatus(reportId, status, adminNote = '') {
    const result = await performAction(`/admin/reports/${reportId}/status`, 'PATCH', {
        status,
        admin_note: adminNote,
    });
    return result;
}

async function adminUpdateUserStatus(userId, accountStatus, reason = '') {
    const result = await performAction(`/admin/users/${userId}/status`, 'PATCH', {
        account_status: accountStatus,
        account_status_reason: reason,
    });
    return result;
}

// ─── Actions signalements client ───────────────────────────────────────────

async function clientReportCreate(orderId, type, description) {
    const result = await performAction('/client/signalements', 'POST', {
        order_id: orderId,
        type,
        description,
    });
    if (result && result.redirect_to) {
        setTimeout(() => window.location.href = result.redirect_to, 1000);
    }
    return result;
}

// ─── Export ─────────────────────────────────────────────────────────────────

// Les fonctions sont globales pour l'accès depuis onclick="..."
// ou peuvent être utilisées dans d'autres modules
