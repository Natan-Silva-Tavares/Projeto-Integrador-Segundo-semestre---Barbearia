// JavaScript opcional para melhorar a UX (validacoes simples).
// O backend em PHP continua sendo responsavel pela validacao real.

document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;

    // Exemplo: se houver campo com required, o navegador ja bloqueia.
    // Aqui so mantemos um gancho para avisos futuros.
});

