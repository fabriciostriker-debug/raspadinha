/**
 * Header Moderno - Funcionalidades Interativas
 * Sistema de Raspadinha - 2025
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeHeader();
});

function initializeHeader() {
    // Scroll effects
    setupScrollEffects();
    
    // Mobile menu toggle
    setupMobileMenu();
    
    // Dropdown interactions
    setupDropdowns();
    
    // Smooth scrolling for navigation links
    setupSmoothScrolling();
    
    // Active navigation highlighting
    setupActiveNavigation();
    
    // Performance optimizations
    setupPerformanceOptimizations();
}

/**
 * Configura efeitos de scroll no header
 */
function setupScrollEffects() {
    const header = document.querySelector('.desktop-nav');
    let lastScrollTop = 0;
    
    window.addEventListener('scroll', throttle(() => {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Adiciona classe 'scrolled' para efeitos visuais
        if (scrollTop > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        // Efeito de hide/show baseado na direção do scroll
        if (scrollTop > lastScrollTop && scrollTop > 100) {
            // Scrolling down - hide header
            header.style.transform = 'translateY(-100%)';
        } else {
            // Scrolling up - show header
            header.style.transform = 'translateY(0)';
        }
        
        lastScrollTop = scrollTop;
    }, 16)); // 60fps
}

/**
 * Configura o menu mobile
 */
function setupMobileMenu() {
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const mobileNav = document.querySelector('.mobile-nav');
    const hamburgerLines = document.querySelectorAll('.hamburger-line');
    
    if (!mobileToggle || !mobileNav) return;
    
    mobileToggle.addEventListener('click', function() {
        const isOpen = mobileNav.classList.contains('active');
        
        if (isOpen) {
            closeMobileMenu();
        } else {
            openMobileMenu();
        }
    });
    
    // Fecha o menu ao clicar em links
    const mobileLinks = mobileNav.querySelectorAll('.mobile-nav-link');
    mobileLinks.forEach(link => {
        link.addEventListener('click', () => {
            closeMobileMenu();
        });
    });
    
    // Fecha o menu ao clicar fora
    document.addEventListener('click', function(event) {
        if (!mobileToggle.contains(event.target) && !mobileNav.contains(event.target)) {
            closeMobileMenu();
        }
    });
}

function openMobileMenu() {
    const mobileNav = document.querySelector('.mobile-nav');
    const hamburgerLines = document.querySelectorAll('.hamburger-line');
    
    mobileNav.classList.add('active');
    mobileNav.style.display = 'block';
    
    // Anima as linhas do hamburger
    hamburgerLines[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
    hamburgerLines[1].style.opacity = '0';
    hamburgerLines[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
    
    // Anima o menu
    setTimeout(() => {
        mobileNav.style.opacity = '1';
        mobileNav.style.transform = 'translateY(0)';
    }, 10);
}

function closeMobileMenu() {
    const mobileNav = document.querySelector('.mobile-nav');
    const hamburgerLines = document.querySelectorAll('.hamburger-line');
    
    mobileNav.style.opacity = '0';
    mobileNav.style.transform = 'translateY(-10px)';
    
    // Reseta as linhas do hamburger
    hamburgerLines[0].style.transform = 'none';
    hamburgerLines[1].style.opacity = '1';
    hamburgerLines[2].style.transform = 'none';
    
    setTimeout(() => {
        mobileNav.classList.remove('active');
        mobileNav.style.display = 'none';
    }, 300);
}

/**
 * Configura dropdowns interativos
 */
function setupDropdowns() {
    const dropdowns = document.querySelectorAll('.user-dropdown');
    
    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('.user-menu-trigger');
        const menu = dropdown.querySelector('.user-dropdown-menu');
        
        if (!trigger || !menu) return;
        
        // Toggle dropdown on click (mobile friendly)
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const isOpen = menu.classList.contains('active');
            
            // Fecha outros dropdowns
            dropdowns.forEach(other => {
                if (other !== dropdown) {
                    other.querySelector('.user-dropdown-menu').classList.remove('active');
                }
            });
            
            // Toggle current dropdown
            if (isOpen) {
                menu.classList.remove('active');
            } else {
                menu.classList.add('active');
            }
        });
        
        // Fecha dropdown ao clicar fora
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target)) {
                menu.classList.remove('active');
            }
        });
    });
}

/**
 * Configura scroll suave para links de navegação
 */
function setupSmoothScrolling() {
    const navLinks = document.querySelectorAll('.nav-link[href^="#"]');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            if (href === '#') return;
            
            e.preventDefault();
            const targetElement = document.querySelector(href);
            
            if (targetElement) {
                const headerHeight = document.querySelector('.desktop-nav').offsetHeight;
                const targetPosition = targetElement.offsetTop - headerHeight - 20;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * Configura navegação ativa baseada na posição do scroll
 */
function setupActiveNavigation() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link[href^="#"]');
    
    if (sections.length === 0) return;
    
    window.addEventListener('scroll', throttle(() => {
        let current = '';
        const scrollPosition = window.pageYOffset + 100;
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            
            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                current = section.getAttribute('id');
            }
        });
        
        // Atualiza links ativos
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    }, 100));
}

/**
 * Configura otimizações de performance
 */
function setupPerformanceOptimizations() {
    // Lazy loading para imagens
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
    
    // Debounce para eventos de resize
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            handleResize();
        }, 250);
    });
}

/**
 * Função utilitária para throttle
 */
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    }
}

/**
 * Função utilitária para debounce
 */
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction() {
        const context = this;
        const args = arguments;
        const later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

/**
 * Manipula redimensionamento da janela
 */
function handleResize() {
    const header = document.querySelector('.desktop-nav');
    const mobileNav = document.querySelector('.mobile-nav');
    
    // Fecha menu mobile em telas grandes
    if (window.innerWidth > 768 && mobileNav.classList.contains('active')) {
        closeMobileMenu();
    }
    
    // Ajusta altura do header baseado no conteúdo
    const headerHeight = header.offsetHeight;
    document.documentElement.style.setProperty('--header-height', `${headerHeight}px`);
}

/**
 * Adiciona efeitos de hover avançados
 */
function setupAdvancedHoverEffects() {
    const interactiveElements = document.querySelectorAll('.nav-link, .btn-deposit, .btn-login, .btn-register');
    
    interactiveElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.02)';
        });
        
        element.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
}

/**
 * Configura notificações toast para ações do usuário
 */
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    // Estilos do toast
    Object.assign(toast.style, {
        position: 'fixed',
        top: '100px',
        right: '20px',
        background: type === 'success' ? '#22c55e' : type === 'error' ? '#ef4444' : '#3b82f6',
        color: 'white',
        padding: '12px 20px',
        borderRadius: '8px',
        boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)',
        zIndex: '10000',
        transform: 'translateX(100%)',
        transition: 'transform 0.3s ease',
        maxWidth: '300px',
        wordWrap: 'break-word'
    });
    
    document.body.appendChild(toast);
    
    // Anima entrada
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 10);
    
    // Remove após 3 segundos
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

/**
 * Configura analytics para interações do header
 */
function trackHeaderInteractions() {
    const trackEvent = (action, label) => {
        if (typeof gtag !== 'undefined') {
            gtag('event', action, {
                event_category: 'Header',
                event_label: label
            });
        }
    };
    
    // Track navigation clicks
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', () => {
            trackEvent('navigation_click', link.textContent.trim());
        });
    });
    
    // Track button clicks
    document.querySelectorAll('.btn-deposit, .btn-login, .btn-register').forEach(btn => {
        btn.addEventListener('click', () => {
            trackEvent('button_click', btn.textContent.trim());
        });
    });
}

// Inicializa funcionalidades adicionais quando necessário
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setupAdvancedHoverEffects();
        trackHeaderInteractions();
    });
} else {
    setupAdvancedHoverEffects();
    trackHeaderInteractions();
}

// Exporta funções para uso global se necessário
window.HeaderModern = {
    showToast,
    openMobileMenu,
    closeMobileMenu
};
