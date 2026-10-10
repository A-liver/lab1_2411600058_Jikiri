/**
 * Dashboard AJAX polling (module pattern).
 * Fetches stats, low-stock products and recent transactions every 30 seconds
 * and updates the DOM without a page reload.
 */
(function () {
    'use strict';

    const POLL_INTERVAL_MS = 30000;
    const API = window.DASHBOARD_API;

    const $ = (id) => document.getElementById(id);
    const money = (n) => '$' + Number(n).toLocaleString('en-US', {
        minimumFractionDigits: 2, maximumFractionDigits: 2,
    });
    const integer = (n) => String(Math.round(n));
    const escapeHtml = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));

    let timer = null;
    let inFlight = false;

    async function fetchJson(url) {
        const res = await fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });

        if (res.status === 401 || res.status === 419) {
            window.location.reload(); // session expired -> login page
            throw new Error('Session expired');
        }
        if (!res.ok) {
            throw new Error(`HTTP ${res.status} for ${url}`);
        }
        return res.json();
    }

    /** Count the number up/down so changes are easy to notice. */
    function animateNumber(el, to, format) {
        if (!el) return;

        const current = el.dataset.value !== undefined
            ? parseFloat(el.dataset.value)
            : parseFloat(el.textContent.replace(/[^0-9.-]/g, ''));
        const from = Number.isNaN(current) ? 0 : current;

        el.dataset.value = to;
        if (from === to) return;

        el.classList.add('stat-flash');
        setTimeout(() => el.classList.remove('stat-flash'), 900);

        const duration = 600;
        const start = performance.now();

        function frame(now) {
            const t = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - t, 3);
            el.textContent = format(from + (to - from) * eased);
            if (t < 1) {
                requestAnimationFrame(frame);
            } else {
                el.textContent = format(to);
            }
        }
        requestAnimationFrame(frame);
    }

    function renderStats(s) {
        animateNumber($('invTotalProducts'), s.total_products, integer);
        animateNumber($('invTotalValue'), s.inventory_value, money);
        animateNumber($('invLowStock'), s.low_stock, integer);
        animateNumber($('invOutOfStock'), s.out_of_stock, integer);
    }

    function renderLowStock(items) {
        const box = $('lowStockAlert');
        if (!box) return;

        if (!items.length) {
            box.innerHTML = '';
            return;
        }

        const out = items.filter((p) => p.quantity <= 0);
        const low = items.filter((p) => p.quantity > 0);

        const rows = [
            ...out.map((p) => `<li><strong class="text-danger"><a href="${escapeHtml(p.url)}" class="text-danger">${escapeHtml(p.name)}</a></strong> &mdash; <strong>OUT OF STOCK</strong> (reorder at ${p.reorder_level})</li>`),
            ...low.map((p) => `<li><strong><a href="${escapeHtml(p.url)}">${escapeHtml(p.name)}</a></strong> &mdash; ${p.quantity} left (reorder at ${p.reorder_level})</li>`),
        ].join('');

        box.innerHTML = `
            <div class="alert alert-warning alert-dismissible fade show d-flex" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3 flex-shrink-0"></i>
                <div>
                    <strong>${items.length} item(s) need attention</strong>
                    <ul class="mb-0 mt-2 small">${rows}</ul>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
    }

    function renderRecentTransactions(rows) {
        const body = $('recentTransactionsBody');
        if (!body) return;

        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No transactions yet.</td></tr>';
            return;
        }

        body.innerHTML = rows.map((t) => `
            <tr>
                <td>${escapeHtml(t.created_at_display)}</td>
                <td><a href="${escapeHtml(t.product.url)}">${escapeHtml(t.product.name)}</a></td>
                <td><span class="badge bg-${escapeHtml(t.type_color)} ${t.type_color === 'warning' ? 'text-dark' : ''}">${escapeHtml(t.type_label)}</span></td>
                <td class="text-end fw-bold ${t.quantity < 0 ? 'text-danger' : 'text-success'}">${t.quantity > 0 ? '+' : ''}${t.quantity}</td>
                <td>${escapeHtml(t.user)}</td>
            </tr>`).join('');
    }

    function setStatus(state) {
        const badge = $('pollStatus');
        if (!badge) return;

        const map = {
            live: ['bg-success', 'Live'],
            loading: ['bg-secondary', 'Updating...'],
            error: ['bg-danger', 'Offline - retrying'],
        };
        badge.className = 'badge ' + map[state][0];
        badge.textContent = map[state][1];
    }

    async function refresh() {
        if (inFlight) return;
        inFlight = true;
        setStatus('loading');

        try {
            const [stats, low, recent] = await Promise.all([
                fetchJson(API.stats),
                fetchJson(API.lowStock),
                fetchJson(API.recent),
            ]);

            renderStats(stats.data);
            renderLowStock(low.data);
            renderRecentTransactions(recent.data);

            $('lastUpdated').textContent = stats.meta.generated_at_display;
            setStatus('live');
        } catch (err) {
            // Graceful failure: keep the old numbers on screen and try again next tick.
            console.error('Dashboard refresh failed:', err);
            setStatus('error');
        } finally {
            inFlight = false;
        }
    }

    function start() {
        timer = setInterval(() => {
            if (!document.hidden) refresh();   // don't poll a background tab
        }, POLL_INTERVAL_MS);

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) refresh();   // catch up when the tab is shown again
        });

        const btn = $('refreshNow');
        if (btn) btn.addEventListener('click', refresh);
    }

    document.addEventListener('DOMContentLoaded', start);
})();