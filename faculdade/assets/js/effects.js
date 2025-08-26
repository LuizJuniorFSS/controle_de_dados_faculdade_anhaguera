/**
 * Arquivo de efeitos e interações JavaScript para melhorar a experiência do usuário
 */

document.addEventListener('DOMContentLoaded', function() {
    // Aplicar efeito de hover-lift em cards
    applyHoverLiftEffect();
    
    // Inicializar contadores animados
    initCounters();
    
    // Adicionar efeito de fade-in aos elementos
    applyFadeInEffect();
    
    // Adicionar efeito de ripple aos botões
    applyRippleEffect();
    
    // Inicializar tooltips personalizados
    initTooltips();
    
    // Adicionar efeito de pulse para elementos importantes
    applyPulseEffect();
});

/**
 * Aplica efeito de elevação ao passar o mouse sobre cards
 */
function applyHoverLiftEffect() {
    const cards = document.querySelectorAll('.card:not(.dashboard-card)');
    cards.forEach(card => {
        card.classList.add('hover-lift');
    });
}

/**
 * Inicializa contadores animados para números estatísticos
 */
function initCounters() {
    const counters = document.querySelectorAll('.dashboard-card p');
    
    counters.forEach(counter => {
        const target = parseInt(counter.innerText, 10);
        counter.classList.add('counter-animation');
        
        if (target > 0) {
            animateCounter(counter, 0, target, 1500);
        }
    });
}

/**
 * Anima um contador de 0 até o valor alvo
 */
function animateCounter(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = Math.floor(progress * (end - start) + start);
        element.innerText = value;
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

/**
 * Aplica efeito de fade-in aos elementos da página
 */
function applyFadeInEffect() {
    const elements = [
        '.page-header',
        '.card',
        '.alert',
        '.table'
    ];
    
    elements.forEach((selector, index) => {
        document.querySelectorAll(selector).forEach((el, i) => {
            el.classList.add('fade-in-element');
            el.style.animationDelay = `${(index * 0.1) + (i * 0.1)}s`;
        });
    });
}

/**
 * Adiciona efeito de ondas (ripple) aos botões
 */
function applyRippleEffect() {
    const buttons = document.querySelectorAll('.btn');
    
    buttons.forEach(button => {
        button.classList.add('btn-ripple');
        
        button.addEventListener('click', function(e) {
            const rect = button.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const ripple = document.createElement('span');
            ripple.classList.add('ripple');
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
}

/**
 * Inicializa tooltips personalizados
 */
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.classList.add('custom-tooltip');
        
        const tooltipText = document.createElement('span');
        tooltipText.classList.add('tooltip-text');
        tooltipText.innerText = element.getAttribute('data-tooltip');
        
        element.appendChild(tooltipText);
    });
}

/**
 * Aplica efeito de pulse para elementos que precisam de destaque
 */
function applyPulseEffect() {
    const elements = document.querySelectorAll('.highlight-pulse');
    elements.forEach(element => {
        element.classList.add('pulse');
    });
    
    // Adicionar efeito de pulse para produtos com estoque crítico
    const lowStockBadges = document.querySelectorAll('.badge.bg-danger');
    lowStockBadges.forEach(badge => {
        badge.classList.add('pulse');
    });
}

/**
 * Adiciona classe 'active' ao link de navegação atual
 */
function highlightCurrentNavLink() {
    const currentLocation = location.pathname;
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    
    navLinks.forEach(link => {
        const linkPath = link.getAttribute('href');
        if (linkPath && currentLocation.includes(linkPath) && !linkPath.endsWith('index.php')) {
            link.classList.add('active');
        } else if (currentLocation.endsWith('/') || currentLocation.endsWith('index.php')) {
            if (linkPath && linkPath.endsWith('index.php')) {
                link.classList.add('active');
            }
        }
    });
}

/**
 * Adiciona funcionalidade de busca em tabelas
 */
function initTableSearch() {
    const searchInputs = document.querySelectorAll('.table-search');
    
    searchInputs.forEach(input => {
        const tableId = input.getAttribute('data-table');
        const table = document.getElementById(tableId);
        
        if (table) {
            input.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }
    });
}

/**
 * Adiciona confirmação antes de ações destrutivas
 */
function initDeleteConfirmations() {
    const deleteButtons = document.querySelectorAll('.btn-delete, [data-confirm]');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 'Tem certeza que deseja excluir este item?';
            
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Aplica máscara para campos de formulário
 */
function initInputMasks() {
    // Implementar quando necessário com biblioteca como IMask ou similar
    console.log('Input masks ready to be implemented');
}

// Inicializar funções adicionais
document.addEventListener('DOMContentLoaded', function() {
    highlightCurrentNavLink();
    initTableSearch();
    initDeleteConfirmations();
    initInputMasks();
});