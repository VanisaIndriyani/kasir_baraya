(() => {
    const setTheme = (theme) => {
        const html = document.documentElement;
        html.setAttribute('data-bs-theme', theme);
        localStorage.setItem('eb_theme', theme);
    };

    const initTheme = () => {
        const saved = localStorage.getItem('eb_theme');
        if (saved === 'dark' || saved === 'light') {
            setTheme(saved);
            return;
        }
        setTheme('light');
    };

    const initDarkToggle = () => {
        const btn = document.getElementById('darkModeToggle');
        if (!btn) return;
        btn.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-bs-theme') || 'light';
            setTheme(current === 'dark' ? 'light' : 'dark');
        });
    };

    const loadingEl = () => document.getElementById('app-loading');
    window.AppLoading = {
        show() {
            const el = loadingEl();
            if (el) el.classList.remove('d-none');
        },
        hide() {
            const el = loadingEl();
            if (el) el.classList.add('d-none');
        }
    };

    window.AppToast = (message) => {
        const toastEl = document.getElementById('appToast');
        const bodyEl = document.getElementById('appToastBody');
        if (!toastEl || !bodyEl) return;
        bodyEl.textContent = message;
        const toast = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 2200 });
        toast.show();
    };

    initTheme();
    initDarkToggle();
})();

