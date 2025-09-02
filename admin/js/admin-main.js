/**
 * JavaScript Principal do Painel Administrativo V2 RASPA
 * Funcionalidades comuns e utilitários
 */

// Aguarda o DOM estar carregado
document.addEventListener('DOMContentLoaded', function() {
    initializeAdminPanel();
});

/**
 * Inicializa o painel administrativo
 */
function initializeAdminPanel() {
    // Inicializa tooltips do Bootstrap
    initializeTooltips();
    
    // Inicializa animações de entrada
    initializeAnimations();
    
    // Inicializa funcionalidades de tabelas
    initializeTables();
    
    // Inicializa funcionalidades de formulários
    initializeForms();
    
    // Inicializa notificações
    initializeNotifications();
    
    // Inicializa sidebar mobile
    initializeMobileSidebar();
}

/**
 * Inicializa tooltips do Bootstrap
 */
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Inicializa animações de entrada
 */
function initializeAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observa elementos com classes de animação
    document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right').forEach(el => {
        observer.observe(el);
    });
}

/**
 * Inicializa funcionalidades de tabelas
 */
function initializeTables() {
    // Adiciona funcionalidade de ordenação
    document.querySelectorAll('.sortable-table th[data-sort]').forEach(th => {
        th.addEventListener('click', function() {
            const table = this.closest('table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const column = this.dataset.sort;
            const direction = this.dataset.direction === 'asc' ? 'desc' : 'asc';
            
            // Remove classes de ordenação anteriores
            table.querySelectorAll('th').forEach(header => {
                header.classList.remove('sort-asc', 'sort-desc');
            });
            
            // Adiciona classe de ordenação atual
            this.classList.add(direction === 'asc' ? 'sort-asc' : 'sort-desc');
            this.dataset.direction = direction;
            
            // Ordena as linhas
            rows.sort((a, b) => {
                const aValue = a.querySelector(`td[data-${column}]`).textContent;
                const bValue = b.querySelector(`td[data-${column}]`).textContent;
                
                if (direction === 'asc') {
                    return aValue.localeCompare(bValue);
                } else {
                    return bValue.localeCompare(aValue);
                }
            });
            
            // Reorganiza as linhas na tabela
            rows.forEach(row => tbody.appendChild(row));
        });
    });

    // Adiciona funcionalidade de busca
    document.querySelectorAll('.table-search').forEach(searchInput => {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const table = this.closest('.table-container').querySelector('table');
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
}

/**
 * Inicializa funcionalidades de formulários
 */
function initializeForms() {
    // Adiciona validação personalizada
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                showNotification('Por favor, corrija os erros no formulário.', 'error');
            }
        });
    });

    // Adiciona máscaras de input
    initializeInputMasks();
    
    // Adiciona funcionalidade de upload de arquivos
    initializeFileUploads();
}

/**
 * Valida um formulário
 */
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

/**
 * Inicializa máscaras de input
 */
function initializeInputMasks() {
    // Máscara para telefone
    document.querySelectorAll('input[data-mask="phone"]').forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            
            if (value.length > 0) {
                if (value.length <= 2) {
                    value = `(${value}`;
                } else if (value.length <= 6) {
                    value = `(${value.slice(0, 2)}) ${value.slice(2)}`;
                } else if (value.length <= 10) {
                    value = `(${value.slice(0, 2)}) ${value.slice(2, 6)}-${value.slice(6)}`;
                } else {
                    value = `(${value.slice(0, 2)}) ${value.slice(2, 7)}-${value.slice(7)}`;
                }
            }
            
            e.target.value = value;
        });
    });

    // Máscara para CPF
    document.querySelectorAll('input[data-mask="cpf"]').forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            
            if (value.length > 0) {
                if (value.length <= 3) {
                    value = value;
                } else if (value.length <= 6) {
                    value = `${value.slice(0, 3)}.${value.slice(3)}`;
                } else if (value.length <= 9) {
                    value = `${value.slice(0, 3)}.${value.slice(3, 6)}.${value.slice(6)}`;
                } else {
                    value = `${value.slice(0, 3)}.${value.slice(3, 6)}.${value.slice(6, 9)}-${value.slice(9)}`;
                }
            }
            
            e.target.value = value;
        });
    });

    // Máscara para moeda
    document.querySelectorAll('input[data-mask="currency"]').forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = (parseInt(value) / 100).toFixed(2);
            value = value.replace('.', ',');
            value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
            e.target.value = `R$ ${value}`;
        });
    });
}

/**
 * Inicializa uploads de arquivos
 */
function initializeFileUploads() {
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const preview = this.parentElement.querySelector('.file-preview');
                if (preview) {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.innerHTML = `<img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px;">`;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        preview.innerHTML = `<div class="alert alert-info">Arquivo selecionado: ${file.name}</div>`;
                    }
                }
            }
        });
    });
}

/**
 * Inicializa notificações
 */
function initializeNotifications() {
    // Cria container de notificações se não existir
    if (!document.getElementById('notification-container')) {
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        `;
        document.body.appendChild(container);
    }
}

/**
 * Mostra uma notificação
 */
function showNotification(message, type = 'info', duration = 5000) {
    const container = document.getElementById('notification-container');
    const notification = document.createElement('div');
    
    const alertClass = type === 'error' ? 'alert-danger' : 
                      type === 'success' ? 'alert-success' : 
                      type === 'warning' ? 'alert-warning' : 'alert-info';
    
    notification.className = `alert ${alertClass} alert-dismissible fade show`;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    container.appendChild(notification);
    
    // Remove a notificação após o tempo especificado
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, duration);
}

/**
 * Inicializa sidebar mobile
 */
function initializeMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    
    if (mobileMenuBtn && sidebar && sidebarOverlay) {
        mobileMenuBtn.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });
        
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }
}

/**
 * Função para confirmar ações
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * Função para fazer requisições AJAX
 */
function makeRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
        ...options
    };
    
    return fetch(url, defaultOptions)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            showNotification('Erro na requisição. Tente novamente.', 'error');
            throw error;
        });
}

/**
 * Função para atualizar dados em tempo real
 */
function startRealTimeUpdates(interval = 30000) {
    setInterval(() => {
        // Atualiza estatísticas
        updateStats();
        
        // Atualiza notificações
        updateNotifications();
    }, interval);
}

/**
 * Atualiza estatísticas
 */
function updateStats() {
    // Implementar atualização de estatísticas em tempo real
    console.log('Atualizando estatísticas...');
}

/**
 * Atualiza notificações
 */
function updateNotifications() {
    // Implementar atualização de notificações em tempo real
    console.log('Atualizando notificações...');
}

/**
 * Função para exportar dados
 */
function exportData(format = 'csv') {
    const table = document.querySelector('.table-container table');
    if (!table) return;
    
    let data = '';
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td, th');
        const rowData = Array.from(cells).map(cell => cell.textContent.trim());
        data += rowData.join(',') + '\n';
    });
    
    if (format === 'csv') {
        const blob = new Blob([data], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'dados_exportados.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    }
}

/**
 * Função para imprimir página
 */
function printPage() {
    window.print();
}

/**
 * Função para alternar tema escuro/claro
 */
function toggleTheme() {
    document.body.classList.toggle('dark-theme');
    const isDark = document.body.classList.contains('dark-theme');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
}

/**
 * Carrega tema salvo
 */
function loadSavedTheme() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-theme');
    }
}

// Carrega tema ao inicializar
loadSavedTheme();

// Exporta funções para uso global
window.AdminPanel = {
    showNotification,
    confirmAction,
    makeRequest,
    exportData,
    printPage,
    toggleTheme
};
