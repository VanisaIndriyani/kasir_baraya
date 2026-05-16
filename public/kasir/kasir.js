(() => {
    const fmtIDR = (n) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(n || 0));

    const els = {
        grid: document.getElementById('productGrid'),
        empty: document.getElementById('productEmpty'),
        search: document.getElementById('productSearch'),
        productCount: document.getElementById('productCount'),
        refresh: document.getElementById('btnRefresh'),
        cartItems: document.getElementById('cartItems'),
        cartTotal: document.getElementById('cartTotal'),
        clearCart: document.getElementById('btnClearCart'),
        payCash: document.getElementById('payCash'),
        payQris: document.getElementById('payQris'),
        cashWrap: document.getElementById('cashWrap'),
        paid: document.getElementById('paidAmount'),
        change: document.getElementById('changeAmount'),
        checkout: document.getElementById('btnCheckout'),
        navCartCount: document.getElementById('navCartCount'),
        cartOpenNav: document.getElementById('btnCartOpenNav'),
        cartDrawer: document.getElementById('cartDrawer'),
        cartOverlay: document.getElementById('cartOverlay'),
        cartOpen: document.getElementById('btnCartOpen'),
        cartClose: document.getElementById('btnCartClose'),
        mobileBar: document.getElementById('mobileCheckoutBar'),
        mobileTotal: document.getElementById('mobileTotal'),
        mobileCount: document.getElementById('mobileCartCount'),
        mobileCountBadge: document.getElementById('mobileCartCountBadge'),
        mobileCheckout: document.getElementById('btnMobileCheckout'),
    };

    let state = {
        products: [],
        cart: { items: [], total: 0, count: 0 },
        lastTransactionId: null,
    };

    let checkoutLocked = false;

    const parseMoney = (value) => {
        const digits = String(value || '').replace(/[^\d]/g, '');
        return digits === '' ? 0 : Number(digits);
    };

    const formatMoneyDots = (n) => {
        const num = Math.max(0, Number(n || 0));
        return num.toLocaleString('id-ID');
    };

    const setPaid = (amount) => {
        const n = Math.max(0, Number(amount || 0));
        els.paid.value = n > 0 ? formatMoneyDots(n) : '';
        updateCashChange();
    };

    const apiUrl = (path) => {
        const base = (window.EB && window.EB.baseUrl) ? window.EB.baseUrl : '';
        return base.replace(/\/$/, '') + '/' + path.replace(/^\//, '');
    };

    const postForm = async (url, data) => {
        const form = new URLSearchParams();
        Object.entries(data || {}).forEach(([k, v]) => form.append(k, v));
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
            body: form.toString(),
        });
        const json = await res.json().catch(() => null);
        if (!res.ok || !json) {
            throw new Error((json && json.message) ? json.message : 'Terjadi kesalahan');
        }
        return json;
    };

    const fetchProducts = async () => {
        const q = (els.search.value || '').trim();
        const res = await fetch(apiUrl('kasir/api/products.php?q=' + encodeURIComponent(q)));
        const json = await res.json().catch(() => null);
        if (!json || !json.ok) throw new Error('Gagal memuat produk');
        state.products = json.data || [];
        if (els.productCount) els.productCount.textContent = String(state.products.length);
        renderProducts();
    };

    const fetchCart = async () => {
        const res = await fetch(apiUrl('kasir/api/cart.php?action=get'), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const json = await res.json().catch(() => null);
        if (!json || !json.ok) throw new Error('Gagal memuat keranjang');
        state.cart = json.data;
        renderCart();
        updateCashChange();
    };

    const isCompactMobileLayout = () => window.matchMedia && window.matchMedia('(max-width: 575.98px)').matches;

    const renderProducts = () => {
        els.grid.innerHTML = '';
        if (!state.products.length) {
            els.empty.classList.remove('d-none');
            return;
        }
        els.empty.classList.add('d-none');

        const compactMobile = isCompactMobileLayout();

        for (const p of state.products) {
            const col = document.createElement('div');
            col.className = compactMobile ? 'col-4' : 'col-6 col-md-4 col-xl-3';

            const disabled = Number(p.stock) <= 0;
            col.innerHTML = compactMobile
                ? `
                    <div class="product-mini app-hover ${disabled ? 'is-disabled' : ''}">
                        <div class="product-mini-media">
                            <img class="product-mini-img" src="${p.image_url}" alt="${p.name}">
                            <span class="badge product-mini-stock ${disabled ? 'text-bg-secondary' : 'badge-soft'}">${disabled ? 'Habis' : 'Stok ' + p.stock}</span>
                        </div>
                        <div class="product-mini-body">
                            <div class="product-mini-name" title="${p.name}">${p.name}</div>
                            <div class="product-mini-footer">
                                <div class="product-mini-price">${fmtIDR(p.price)}</div>
                                <button class="btn btn-danger product-mini-add ${disabled ? 'disabled' : ''}" data-add="${p.id}" type="button" aria-label="Tambah">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `
                : `
                    <div class="product-card app-hover h-100 bg-white">
                        <img class="product-img" src="${p.image_url}" alt="${p.name}">
                        <div class="p-3 product-body">
                            <div class="fw-semibold product-title" title="${p.name}">${p.name}</div>
                            <div class="d-flex align-items-center justify-content-between mt-2">
                                <div class="product-price">${fmtIDR(p.price)}</div>
                                <span class="badge ${disabled ? 'text-bg-secondary' : 'badge-soft'}">${disabled ? 'Habis' : 'Stok ' + p.stock}</span>
                            </div>
                            <div class="d-grid mt-3 product-actions">
                                <button class="btn btn-danger ${disabled ? 'disabled' : ''}" data-add="${p.id}" type="button">
                                    <i class="bi bi-plus-lg me-1"></i>Tambah
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            els.grid.appendChild(col);
        }

        els.grid.querySelectorAll('[data-add]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-add');
                if (!id) return;
                try {
                    btn.setAttribute('disabled', 'disabled');
                    await postForm(apiUrl('kasir/api/cart.php'), { action: 'add', product_id: id, qty: 1, _token: window.EB.csrf });
                    await fetchCart();
                    window.AppToast?.('Ditambahkan ke keranjang');
                } catch (e) {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: e.message || 'Gagal menambah' });
                } finally {
                    btn.removeAttribute('disabled');
                }
            });
        });
    };

    const renderCart = () => {
        els.cartItems.innerHTML = '';
        const items = state.cart.items || [];
        els.cartTotal.textContent = fmtIDR(state.cart.total || 0);
        if (els.mobileTotal) els.mobileTotal.textContent = fmtIDR(state.cart.total || 0);
        if (els.mobileCount) els.mobileCount.textContent = String(state.cart.count || 0);
        if (els.mobileCountBadge) els.mobileCountBadge.textContent = String(state.cart.count || 0);
        if (els.navCartCount) els.navCartCount.textContent = String(state.cart.count || 0);

        if (!items.length) {
            els.cartItems.innerHTML = `
                <div class="text-center text-muted py-4">
                    <div class="mb-2"><i class="bi bi-basket fs-2"></i></div>
                    <div class="fw-semibold">Keranjang kosong</div>
                    <div class="small">Tambahkan produk untuk memulai.</div>
                </div>
            `;
            return;
        }

        for (const item of items) {
            const row = document.createElement('div');
            row.className = 'cart-item';
            row.innerHTML = `
                <img class="cart-item-img" src="${item.image_url}" alt="${item.name}">
                <div class="cart-item-info">
                    <div class="fw-semibold cart-item-title" title="${item.name}">${item.name}</div>
                    <div class="text-muted small">${fmtIDR(item.price)} • Subtotal <span class="fw-semibold">${fmtIDR(item.subtotal)}</span></div>
                </div>
                <div class="cart-item-actions">
                    <div class="qty-control">
                        <button class="btn btn-sm btn-outline-secondary qty-btn" data-dec="${item.product_id}" type="button"><i class="bi bi-dash"></i></button>
                        <div class="fw-semibold" style="min-width:18px;text-align:center;">${item.qty}</div>
                        <button class="btn btn-sm btn-outline-secondary qty-btn" data-inc="${item.product_id}" type="button"><i class="bi bi-plus"></i></button>
                    </div>
                    <button class="btn btn-sm btn-link text-danger p-0 cart-item-remove" data-rm="${item.product_id}" type="button"><i class="bi bi-x-circle me-1"></i>Hapus</button>
                </div>
            `;
            els.cartItems.appendChild(row);
        }

        els.cartItems.querySelectorAll('[data-inc]').forEach((btn) => {
            btn.addEventListener('click', () => adjustQty(btn.getAttribute('data-inc'), 1));
        });
        els.cartItems.querySelectorAll('[data-dec]').forEach((btn) => {
            btn.addEventListener('click', () => adjustQty(btn.getAttribute('data-dec'), -1));
        });
        els.cartItems.querySelectorAll('[data-rm]').forEach((btn) => {
            btn.addEventListener('click', () => removeItem(btn.getAttribute('data-rm')));
        });
    };

    const setDrawerOpen = (open) => {
        if (!els.cartDrawer || !els.cartOverlay) return;
        const shouldOpen = Boolean(open);
        els.cartDrawer.classList.toggle('is-open', shouldOpen);
        els.cartOverlay.classList.toggle('d-none', !shouldOpen);
        els.cartOverlay.setAttribute('aria-hidden', shouldOpen ? 'false' : 'true');
        els.mobileBar?.classList.toggle('d-none', shouldOpen);
        document.body.classList.toggle('no-scroll', shouldOpen);
    };

    const adjustQty = async (productId, delta) => {
        if (!productId) return;
        try {
            await postForm(apiUrl('kasir/api/cart.php'), { action: 'adjust', product_id: productId, delta: delta, _token: window.EB.csrf });
            await fetchCart();
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: e.message || 'Gagal mengubah qty' });
        }
    };

    const removeItem = async (productId) => {
        if (!productId) return;
        try {
            await postForm(apiUrl('kasir/api/cart.php'), { action: 'remove', product_id: productId, _token: window.EB.csrf });
            await fetchCart();
            window.AppToast?.('Item dihapus');
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: e.message || 'Gagal menghapus' });
        }
    };

    const clearCart = async () => {
        try {
            const ok = await Swal.fire({
                icon: 'warning',
                title: 'Kosongkan keranjang?',
                showCancelButton: true,
                confirmButtonText: 'Ya, kosongkan',
                cancelButtonText: 'Batal'
            });
            if (!ok.isConfirmed) return;
            await postForm(apiUrl('kasir/api/cart.php'), { action: 'clear', _token: window.EB.csrf });
            await fetchCart();
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: e.message || 'Gagal mengosongkan' });
        }
    };

    const getOrderPayload = () => {
        const paymentMethod = els.payQris.checked ? 'qris' : 'cash';
        const paidAmount = paymentMethod === 'cash' ? parseMoney(els.paid.value) : 0;
        return { paymentMethod, paidAmount };
    };

    const updateCashChange = () => {
        const paymentMethod = els.payQris.checked ? 'qris' : 'cash';
        const total = Number(state.cart.total || 0);
        if (paymentMethod === 'qris') {
            els.cashWrap.classList.add('d-none');
            els.change.textContent = fmtIDR(0);
            return;
        }
        els.cashWrap.classList.remove('d-none');
        const paid = parseMoney(els.paid.value);
        if (els.paid.value !== '' && paid > 0) {
            const formatted = formatMoneyDots(paid);
            if (els.paid.value !== formatted) {
                els.paid.value = formatted;
            }
        }
        const change = Math.max(0, paid - total);
        els.change.textContent = fmtIDR(change);
    };

    const checkout = async () => {
        if (checkoutLocked) return;
        const items = state.cart.items || [];
        if (!items.length) {
            Swal.fire({ icon: 'info', title: 'Keranjang kosong', text: 'Tambahkan produk dulu.' });
            return;
        }
        const payload = getOrderPayload();
        try {
            checkoutLocked = true;
            els.checkout.disabled = true;
            window.AppLoading?.show();
            const res = await postForm(apiUrl('kasir/api/checkout.php'), {
                _token: window.EB.csrf,
                payment_method: payload.paymentMethod,
                paid_amount: payload.paidAmount
            });
            state.lastTransactionId = res.data.transaction_id;
            els.paid.value = '';
            updateCashChange();
            await fetchCart();
            setDrawerOpen(false);
            window.AppLoading?.hide();

            const confirm = await Swal.fire({
                icon: 'success',
                title: 'Transaksi berhasil',
                html: `<div class="text-muted">Invoice</div><div class="fw-bold fs-5">${res.data.invoice}</div>`,
                showCancelButton: true,
                confirmButtonText: 'Cetak Resi',
                cancelButtonText: 'Tutup'
            });
            if (confirm.isConfirmed) {
                const mode = (window.EB && window.EB.printMode) ? window.EB.printMode : 'browser';
                if (mode === 'server') {
                    try {
                        window.AppLoading?.show();
                        await postForm(apiUrl('kasir/api/print_server.php'), { _token: window.EB.csrf, transaction_id: state.lastTransactionId });
                        Swal.fire({ icon: 'success', title: 'Dikirim ke printer', text: 'Resi sedang diproses printer.' });
                    } catch (e) {
                        Swal.fire({ icon: 'error', title: 'Gagal Print', text: e.message || 'Tidak bisa print via server.' });
                    } finally {
                        window.AppLoading?.hide();
                    }
                } else {
                    window.open(apiUrl('kasir/receipt.php?id=' + encodeURIComponent(state.lastTransactionId)), '_blank', 'noopener,noreferrer');
                }
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Gagal Checkout', text: e.message || 'Periksa input dan stok.' });
        } finally {
            window.AppLoading?.hide();
            checkoutLocked = false;
            els.checkout.disabled = false;
        }
    };

    const initEvents = () => {
        let timer = null;
        els.search.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(() => fetchProducts().catch(() => {}), 180);
        });
        els.refresh.addEventListener('click', () => fetchProducts().catch(() => {}));
        els.clearCart.addEventListener('click', () => clearCart());
        els.payCash.addEventListener('change', updateCashChange);
        els.payQris.addEventListener('change', updateCashChange);
        els.paid.addEventListener('input', updateCashChange);
        document.querySelectorAll('[data-paid-set]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const v = Number(btn.getAttribute('data-paid-set') || 0);
                setPaid(v);
            });
        });
        document.querySelectorAll('[data-paid-exact]').forEach((btn) => {
            btn.addEventListener('click', () => setPaid(Number(state.cart.total || 0)));
        });
        els.checkout.addEventListener('click', checkout);
        els.cartOpenNav?.addEventListener('click', () => setDrawerOpen(true));
        els.cartOpen?.addEventListener('click', () => setDrawerOpen(true));
        els.cartClose?.addEventListener('click', () => setDrawerOpen(false));
        els.cartOverlay?.addEventListener('click', () => setDrawerOpen(false));
        els.mobileCheckout?.addEventListener('click', () => {
            setDrawerOpen(true);
            if (els.payCash?.checked) {
                setTimeout(() => els.paid?.focus(), 120);
            }
        });
        window.addEventListener('resize', () => renderProducts());
    };

    const boot = async () => {
        initEvents();
        setDrawerOpen(false);
        try {
            window.AppLoading?.show();
            await Promise.all([fetchProducts(), fetchCart()]);
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: e.message || 'Gagal memuat data.' });
        } finally {
            window.AppLoading?.hide();
        }
    };

    boot();
})();
