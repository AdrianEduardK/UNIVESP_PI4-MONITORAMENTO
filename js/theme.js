function initTheme() {
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
    } else if (prefersDark) {
        document.documentElement.setAttribute('data-theme', 'dark');
    } else {
        document.documentElement.setAttribute('data-theme', 'light');
    }
    updateThemeIcon();
}

window.toggleTheme = function(e) {
    if (e) e.preventDefault(); // Evita recarregar a página caso seja um link
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeIcon();
};

function updateThemeIcon() {
    const btns = document.querySelectorAll('.theme-switch-btn');
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    
    btns.forEach(btn => {
        btn.innerHTML = isDark ? '☀️ Modo Claro' : '🌙 Modo Escuro';
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const btns = document.querySelectorAll('.theme-switch-btn');
    btns.forEach(btn => {
        // Remove event listeners antigos clonando o nó se necessário, mas adicionar é seguro.
        btn.removeEventListener('click', window.toggleTheme);
        btn.addEventListener('click', window.toggleTheme);
    });
    // Atualiza ícones após carregar todos os elementos
    updateThemeIcon();
});

// Executa na inicialização apenas a cor
initTheme();
