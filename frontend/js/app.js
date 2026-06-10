// UX no front: modo noturno e validações simples de formulário.

(function initTheme() {
    const root = document.documentElement;
    const toggle = document.querySelector('.theme-toggle');
    const icon = document.querySelector('.theme-toggle__icon');

    function applyTheme(theme) {
        if (theme === 'dark') {
            root.setAttribute('data-theme', 'dark');
            if (icon) icon.textContent = '☀️';
        } else {
            root.removeAttribute('data-theme');
            if (icon) icon.textContent = '🌙';
        }
    }

    applyTheme(localStorage.getItem('theme') === 'dark' ? 'dark' : 'light');

    if (toggle) {
        toggle.addEventListener('click', () => {
            const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            localStorage.setItem('theme', next);
            applyTheme(next);
        });
    }
})();

document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;
});
