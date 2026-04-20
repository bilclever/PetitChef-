const statusLabelMap = {
    recue: 'Reçue',
    en_preparation: 'En préparation',
    prete: 'Prête',
    livree: 'Livrée',
    annulee: 'Annulée',
};

function normalizeStatus(status) {
    return String(status ?? '')
        .trim()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/\s+/g, '_');
}

function statusToLabel(status) {
    const normalized = normalizeStatus(status);

    return statusLabelMap[normalized] ?? status;
}

function showInAppNotification(payload) {
    window.dispatchEvent(new CustomEvent('pc:notify', {
        detail: {
            title: 'Commande mise à jour',
            message: `Commande #${payload.order_id}: ${statusToLabel(payload.status)}`,
            type: 'info',
        },
    }));
}

function updateOrderRow(payload) {
    const statusNode = document.querySelector(`[data-order-status="${payload.order_id}"]`);

    if (!statusNode) {
        return;
    }

    statusNode.textContent = statusToLabel(payload.status);
}

function listenOrderChannel(orderId) {
    window.Echo.private(`order.${orderId}`)
        .listen('OrderStatusUpdated', (payload) => {
            updateOrderRow(payload);
            showInAppNotification(payload);
        });
}

function listenKitchenChannel(cookId) {
    window.Echo.private(`kitchen.${cookId}`)
        .listen('OrderStatusUpdated', (payload) => {
            updateOrderRow(payload);
            showInAppNotification(payload);
        });
}

export function initRealtimeOrderUpdates() {
    if (!window.Echo) {
        return;
    }

    const root = document.documentElement;
    const orderIds = (root.dataset.orderIds ?? '')
        .split(',')
        .map((id) => id.trim())
        .filter(Boolean);

    const kitchenId = root.dataset.kitchenChannel;

    orderIds.forEach((orderId) => listenOrderChannel(orderId));

    if (kitchenId) {
        listenKitchenChannel(kitchenId);
    }
}
