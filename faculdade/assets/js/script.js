/**
 * Script principal para o Sistema de Gerenciamento Comercial - Faculdade
 */

// Executar quando o documento estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips do Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Inicializar popovers do Bootstrap
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Configurar confirmação para exclusão
    setupDeleteConfirmation();

    // Configurar máscaras para campos de formulário
    setupInputMasks();

    // Configurar validação de formulários
    setupFormValidation();
    
    // Inicializar funcionalidades específicas de páginas
    initPageSpecificFeatures();
    
    // Configurar formulário de pedidos
    setupOrderForm();
});

/**
 * Configura confirmação para botões de exclusão
 */
function setupDeleteConfirmation() {
    document.querySelectorAll('.btn-delete').forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('Tem certeza que deseja excluir este item? Esta ação não pode ser desfeita.')) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Configura máscaras para campos de formulário
 * Requer a biblioteca jQuery Mask Plugin
 */
function setupInputMasks() {
    // Verificar se jQuery e jQuery Mask Plugin estão disponíveis
    if (typeof $ !== 'undefined' && $.fn.mask) {
        $('.mask-date').mask('00/00/0000');
        $('.mask-time').mask('00:00:00');
        $('.mask-date_time').mask('00/00/0000 00:00:00');
        $('.mask-cep').mask('00000-000');
        $('.mask-phone').mask('(00) 0000-0000');
        $('.mask-phone_with_ddd').mask('(00) 0000-0000');
        $('.mask-phone_us').mask('(000) 000-0000');
        $('.mask-cpf').mask('000.000.000-00', {reverse: true});
        $('.mask-cnpj').mask('00.000.000/0000-00', {reverse: true});
        $('.mask-money').mask('000.000.000.000.000,00', {reverse: true});
        $('.mask-money2').mask("#.##0,00", {reverse: true});
    }
}

/**
 * Configura validação de formulários
 */
function setupFormValidation() {
    // Fetch all forms we want to apply custom validation styles to
    var forms = document.querySelectorAll('.needs-validation');

    // Loop over them and prevent submission
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
}

/**
 * Função para formatar valores monetários
 * @param {number} value - Valor a ser formatado
 * @param {string} currency - Símbolo da moeda (padrão: R$)
 * @returns {string} - Valor formatado
 */
function formatCurrency(value, currency = 'R$') {
    return currency + ' ' + parseFloat(value).toFixed(2).replace('.', ',').replace(/\d(?=(\d{3})+\,)/g, '$&.');
}

/**
 * Função para formatar datas
 * @param {string} dateString - Data no formato ISO (YYYY-MM-DD)
 * @returns {string} - Data formatada (DD/MM/YYYY)
 */
function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
}

/**
 * Função para carregar dados via AJAX
 * @param {string} url - URL para requisição
 * @param {function} callback - Função de callback para processar os dados
 */
function loadData(url, callback) {
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na requisição: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (typeof callback === 'function') {
                callback(data);
            }
        })
        .catch(error => {
            console.error('Erro ao carregar dados:', error);
            alert('Erro ao carregar dados. Por favor, tente novamente.');
        });
}

/**
 * Função para enviar formulário via AJAX
 * @param {HTMLFormElement} form - Elemento do formulário
 * @param {function} successCallback - Função de callback para sucesso
 * @param {function} errorCallback - Função de callback para erro
 */
function submitFormAjax(form, successCallback, errorCallback) {
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: form.method,
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na requisição: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (typeof successCallback === 'function') {
            successCallback(data);
        }
    })
    .catch(error => {
        console.error('Erro ao enviar formulário:', error);
        if (typeof errorCallback === 'function') {
            errorCallback(error);
        } else {
            alert('Erro ao processar requisição. Por favor, tente novamente.');
        }
    });
}

/**
 * Configura o formulário de pedidos
 */
function setupOrderForm() {
    const orderForm = document.getElementById('orderForm');
    
    if (orderForm) {
        // Atualizar totais quando quantidades ou preços mudam
        orderForm.addEventListener('change', function(e) {
            if (e.target.classList.contains('item-quantity') || e.target.classList.contains('item-price')) {
                updateOrderTotals();
            }
        });
        
        // Adicionar novo item ao pedido
        const addItemBtn = document.getElementById('addItemBtn');
        if (addItemBtn) {
            addItemBtn.addEventListener('click', addOrderItem);
        }
        
        // Remover item do pedido
        orderForm.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item')) {
                e.preventDefault();
                removeOrderItem(e.target);
            }
        });
        
        // Atualizar preço do produto quando selecionado
        orderForm.addEventListener('change', function(e) {
            if (e.target.classList.contains('product-select')) {
                updateProductPrice(e.target);
            }
        });
    }
}

/**
 * Atualiza os totais do pedido
 */
function updateOrderTotals() {
    let total = 0;
    const rows = document.querySelectorAll('.order-item-row');
    
    rows.forEach(row => {
        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const subtotal = quantity * price;
        
        row.querySelector('.item-subtotal').textContent = subtotal.toFixed(2);
        total += subtotal;
    });
    
    // Atualizar total do pedido
    const totalElement = document.getElementById('orderTotal');
    if (totalElement) {
        totalElement.textContent = total.toFixed(2);
    }
}

/**
 * Adiciona um novo item ao pedido
 */
function addOrderItem() {
    const itemsContainer = document.getElementById('orderItems');
    const itemTemplate = document.getElementById('itemRowTemplate');
    
    if (itemsContainer && itemTemplate) {
        const newRow = itemTemplate.content.cloneNode(true);
        const rowCount = itemsContainer.querySelectorAll('.order-item-row').length;
        
        // Atualizar índices dos campos
        const inputs = newRow.querySelectorAll('[name]');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            input.setAttribute('name', name.replace('index', rowCount));
        });
        
        itemsContainer.appendChild(newRow);
        updateOrderTotals();
    }
}

/**
 * Remove um item do pedido
 */
function removeOrderItem(button) {
    const row = button.closest('.order-item-row');
    
    if (row) {
        row.remove();
        updateOrderTotals();
    }
}

/**
 * Atualiza o preço do produto quando um produto é selecionado
 */
function updateProductPrice(productSelect) {
    const row = productSelect.closest('.order-item-row');
    const priceInput = row.querySelector('.item-price');
    const selectedOption = productSelect.options[productSelect.selectedIndex];
    
    if (selectedOption && priceInput) {
        const price = selectedOption.getAttribute('data-price') || '0.00';
        priceInput.value = price;
        updateOrderTotals();
    }
}

/**
 * Inicializa funcionalidades específicas de páginas
 */
function initPageSpecificFeatures() {
    // Inicializar gráficos na página de relatórios
    initReportCharts();
    
    // Inicializar funcionalidades da página de produtos
    initProductsPage();
    
    // Inicializar funcionalidades da página de clientes
    initClientsPage();
}

/**
 * Inicializa gráficos na página de relatórios
 */
function initReportCharts() {
    // Verificar se estamos na página de relatórios
    if (!document.getElementById('salesChart')) {
        return;
    }
    
    // Gráfico de vendas por dia
    const salesChartCtx = document.getElementById('salesChart').getContext('2d');
    const salesData = JSON.parse(document.getElementById('salesChartData')?.textContent || '{}');
    
    if (salesChartCtx) {
        new Chart(salesChartCtx, {
            type: 'line',
            data: {
                labels: salesData.labels || [],
                datasets: [{
                    label: 'Vendas por Dia',
                    data: salesData.values || [],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Gráfico de vendas por categoria
    const categoryChartCtx = document.getElementById('categoryChart')?.getContext('2d');
    const categoryData = JSON.parse(document.getElementById('categoryChartData')?.textContent || '{}');
    
    if (categoryChartCtx) {
        new Chart(categoryChartCtx, {
            type: 'doughnut',
            data: {
                labels: categoryData.labels || [],
                datasets: [{
                    data: categoryData.values || [],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(199, 199, 199, 0.7)',
                        'rgba(83, 102, 255, 0.7)',
                        'rgba(40, 159, 64, 0.7)',
                        'rgba(210, 199, 199, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    }
}

/**
 * Inicializa funcionalidades da página de produtos
 */
function initProductsPage() {
    // Verificar se estamos na página de produtos
    if (!document.querySelector('.product-filter')) {
        return;
    }
    
    // Filtro de produtos
    const filterForm = document.getElementById('productFilterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            applyProductFilter();
        });
        
        // Limpar filtros
        const clearFilterBtn = document.getElementById('clearFilterBtn');
        if (clearFilterBtn) {
            clearFilterBtn.addEventListener('click', function() {
                filterForm.reset();
                applyProductFilter();
            });
        }
    }
}

/**
 * Aplica filtros na página de produtos
 */
function applyProductFilter() {
    const form = document.getElementById('productFilterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    window.location.href = 'produtos.php?' + params.toString();
}

/**
 * Inicializa funcionalidades da página de clientes
 */
function initClientsPage() {
    // Verificar se estamos na página de clientes
    if (!document.querySelector('.client-filter')) {
        return;
    }
    
    // Implementar funcionalidades específicas da página de clientes
    console.log('Client page features ready to be implemented');
}