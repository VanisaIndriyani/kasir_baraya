(() => {
    const fmtIDR = (n) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(n || 0));

    const tableEl = document.getElementById('salesTable');
    if (tableEl && window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
        window.jQuery(tableEl).DataTable({
            pageLength: 10,
            order: [[1, 'desc']],
            language: { search: 'Cari:', lengthMenu: '_MENU_', info: '_START_ - _END_ dari _TOTAL_', paginate: { previous: '‹', next: '›' } }
        });
    }

    const modalEl = document.getElementById('detailModal');
    const modal = modalEl ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;
    const metaEl = document.getElementById('detailMeta');
    const itemsEl = document.getElementById('detailItems');

    const loadDetail = async (id) => {
        const url = (window.EB_SALES?.detailUrl || '') + '?id=' + encodeURIComponent(id);
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const json = await res.json().catch(() => null);
        if (!res.ok || !json || !json.ok) throw new Error((json && json.message) ? json.message : 'Gagal memuat detail');
        return json.data;
    };

    document.querySelectorAll('[data-detail]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const id = btn.getAttribute('data-detail');
            if (!id) return;
            try {
                window.AppLoading?.show();
                const data = await loadDetail(id);
                const t = data.transaction;

                metaEl.innerHTML = `
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <div class="text-muted small">Invoice</div>
                            <div class="fw-bold">${t.invoice}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="text-muted small">Tanggal</div>
                            <div class="fw-semibold">${t.created_at}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="text-muted small">Order</div>
                            <div class="fw-semibold text-uppercase">${t.order_type}${t.platform ? ' • ' + t.platform : ''}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="text-muted small">Payment</div>
                            <div class="fw-semibold text-uppercase">${t.payment_method}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="text-muted small">Total</div>
                            <div class="fw-bold text-danger">${fmtIDR(t.total)}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="text-muted small">Kembalian</div>
                            <div class="fw-semibold">${fmtIDR(t.change_amount)}</div>
                        </div>
                    </div>
                `;

                itemsEl.innerHTML = '';
                for (const it of (data.items || [])) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="fw-semibold">${it.product_name}</td>
                        <td class="text-end">${it.qty}</td>
                        <td class="text-end">${fmtIDR(it.price)}</td>
                        <td class="text-end fw-semibold">${fmtIDR(it.subtotal)}</td>
                    `;
                    itemsEl.appendChild(tr);
                }

                modal?.show();
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: e.message || 'Gagal memuat detail.' });
            } finally {
                window.AppLoading?.hide();
            }
        });
    });
})();

