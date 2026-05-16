(() => {
    const tableEl = document.getElementById('productTable');
    if (tableEl && window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
        window.jQuery(tableEl).DataTable({
            pageLength: 10,
            order: [[1, 'asc']],
            language: { search: 'Cari:', lengthMenu: '_MENU_', info: '_START_ - _END_ dari _TOTAL_', paginate: { previous: '‹', next: '›' } }
        });
    }

    const modalEl = document.getElementById('productModal');
    const modal = modalEl ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;
    const form = document.getElementById('productForm');

    const fields = {
        title: document.getElementById('productModalTitle'),
        id: document.getElementById('productId'),
        name: document.getElementById('productName'),
        price: document.getElementById('productPrice'),
        stock: document.getElementById('productStock'),
        image: document.getElementById('productImage'),
        preview: document.getElementById('imagePreview'),
    };

    const setPreview = (src) => {
        if (fields.preview) fields.preview.src = src;
    };

    document.getElementById('btnAddProduct')?.addEventListener('click', () => {
        if (fields.title) fields.title.textContent = 'Tambah Produk';
        if (fields.id) fields.id.value = '';
        if (fields.name) fields.name.value = '';
        if (fields.price) fields.price.value = '';
        if (fields.stock) fields.stock.value = '';
        if (fields.image) fields.image.value = '';
        setPreview(fields.preview?.src || '');
        setTimeout(() => fields.name?.focus(), 120);
    });

    document.querySelectorAll('[data-edit="1"]').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (fields.title) fields.title.textContent = 'Edit Produk';
            if (fields.id) fields.id.value = btn.getAttribute('data-id') || '';
            if (fields.name) fields.name.value = btn.getAttribute('data-name') || '';
            if (fields.price) fields.price.value = btn.getAttribute('data-price') || '';
            if (fields.stock) fields.stock.value = btn.getAttribute('data-stock') || '';
            if (fields.image) fields.image.value = '';
            setPreview(btn.getAttribute('data-image') || fields.preview?.src || '');
            modal?.show();
        });
    });

    fields.image?.addEventListener('change', () => {
        const file = fields.image.files && fields.image.files[0] ? fields.image.files[0] : null;
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => setPreview(String(e.target?.result || ''));
        reader.readAsDataURL(file);
    });

    document.querySelectorAll('[data-delete]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const id = btn.getAttribute('data-delete');
            if (!id) return;
            const ok = await Swal.fire({
                icon: 'warning',
                title: 'Hapus produk?',
                text: 'Produk akan dihapus dari daftar kasir.',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            });
            if (!ok.isConfirmed) return;
            try {
                window.AppLoading?.show();
                const form = new URLSearchParams();
                form.append('_token', window.EB_PRODUCTS?.csrf || '');
                form.append('id', id);
                const res = await fetch(window.EB_PRODUCTS?.deleteUrl || '', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                    body: form.toString()
                });
                const json = await res.json().catch(() => null);
                if (!res.ok || !json || !json.ok) throw new Error((json && json.message) ? json.message : 'Gagal menghapus');
                await Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Produk dihapus.' });
                location.reload();
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: e.message || 'Gagal menghapus' });
            } finally {
                window.AppLoading?.hide();
            }
        });
    });

    form?.addEventListener('submit', () => {
        window.AppLoading?.show();
        setTimeout(() => window.AppLoading?.hide(), 1200);
    });
})();
