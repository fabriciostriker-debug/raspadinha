/* ========================================
   HEADER ORIGINAL MODERNO - JAVASCRIPT
   Sistema de Raspadinha - 2025
   ======================================== */

(function() {
    'use strict';

    // Configurações
    const config = {
        scrollThreshold: 50,
        animationDuration: 300,
        hoverDelay: 200
    };

    // Elementos do DOM
    let header, navLinks, userDropdowns, buttons;

    // Inicialização
    function init() {
        setupElements();
        setupEventListeners();
        setupAnimations();
        setupPerformanceOptimizations();
        
        console.log('🚀 Header Original Moderno inicializado com sucesso!');
    }

    // Configurar elementos do DOM
    function setupElements() {
        header = document.querySelector('.desktop-nav');
        navLinks = document.querySelectorAll('.desktop-nav .nav-link');
        userDropdowns = document.querySelectorAll('.desktop-nav .group');
        buttons = document.querySelectorAll('.desktop-nav button');
    }

    // Configurar event listeners
    function setupEventListeners() {
        setupScrollEffects();
        setupHoverEffects();
        setupDropdownInteractions();
        setupSmoothScrolling();
        setupActiveNavigation();
    }

    // Efeitos de scroll
    function setupScrollEffects() {
        let lastScrollTop = 0;
        let ticking = false;

        function updateHeader() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > config.scrollThreshold) {
                header.classList.add('scrolled');
                
                // Fazer o header desaparecer completamente ao rolar para baixo
                if (scrollTop > lastScrollTop && scrollTop > 100) {
                    header.style.transform = 'translateY(-100%)';
                } else {
                    // Fazer o header reaparecer ao rolar para cima
                    header.style.transform = 'translateY(0)';
                }
            } else {
                header.classList.remove('scrolled');
                header.style.transform = 'translateY(0)';
            }
            
            lastScrollTop = scrollTop;
            ticking = false;
        }

        function requestTick() {
            if (!ticking) {
                requestAnimationFrame(updateHeader);
                ticking = true;
            }
        }

        window.addEventListener('scroll', requestTick, { passive: true });
    }

    // Efeitos de hover
    function setupHoverEffects() {
        // Efeito de brilho nos botões
        buttons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px) scale(1.02)';
                this.style.boxShadow = '0 8px 25px rgba(34, 197, 94, 0.4)';
            });

            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
                this.style.boxShadow = '';
            });
        });

        // Efeito de destaque nos links de navegação
        navLinks.forEach(link => {
            link.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 20px rgba(34, 197, 94, 0.2)';
            });

            link.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '';
            });
        });
    }

    // Interações com dropdowns
    function setupDropdownInteractions() {
        userDropdowns.forEach(dropdown => {
            const trigger = dropdown.querySelector('button');
            const menu = dropdown.querySelector('.absolute');

            if (trigger && menu) {
                // Fechar outros dropdowns ao abrir um novo
                trigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    
                    // Fechar outros dropdowns
                    userDropdowns.forEach(other => {
                        if (other !== dropdown) {
                            const otherMenu = other.querySelector('.absolute');
                            if (otherMenu) {
                                otherMenu.style.display = 'none';
                            }
                        }
                    });

                    // Alternar dropdown atual
                    const isVisible = menu.style.display === 'block';
                    menu.style.display = isVisible ? 'none' : 'block';
                    
                    if (!isVisible) {
                        // Animar entrada
                        menu.style.opacity = '0';
                        menu.style.transform = 'translateY(-10px)';
                        
                        requestAnimationFrame(() => {
                            menu.style.transition = 'all 0.3s ease';
                            menu.style.opacity = '1';
                            menu.style.transform = 'translateY(0)';
                        });
                    }
                });
            }
        });

        // Fechar dropdowns ao clicar fora
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.group')) {
                userDropdowns.forEach(dropdown => {
                    const menu = dropdown.querySelector('.absolute');
                    if (menu) {
                        menu.style.display = 'none';
                    }
                });
            }
        });
    }

    // Scroll suave
    function setupSmoothScrolling() {
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                if (href.startsWith('#')) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });
    }

    // Navegação ativa
    function setupActiveNavigation() {
        const sections = document.querySelectorAll('section[id]');
        
        function updateActiveNav() {
            const scrollPos = window.scrollY + 100;
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.offsetHeight;
                const sectionId = section.getAttribute('id');
                
                if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                    navLinks.forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('href') === `#${sectionId}`) {
                            link.classList.add('active');
                        }
                    });
                }
            });
        }

        window.addEventListener('scroll', throttle(updateActiveNav, 100), { passive: true });
    }

    // Animações
    function setupAnimations() {
        // Animar elementos na entrada
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observar elementos do header
        const headerElements = header.querySelectorAll('*');
        headerElements.forEach(el => {
            if (el !== header) {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'all 0.6s ease';
                observer.observe(el);
            }
        });
    }

    // Otimizações de performance
    function setupPerformanceOptimizations() {
        // Lazy loading para imagens
        const images = header.querySelectorAll('img');
        images.forEach(img => {
            if ('loading' in HTMLImageElement.prototype) {
                img.loading = 'lazy';
            }
        });

        // Debounce para resize
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                handleResize();
            }, 250);
        }, { passive: true });
    }

    // Manipular resize
    function handleResize() {
        const width = window.innerWidth;
        
        if (width <= 768) {
            header.style.padding = '0.75rem 0';
        } else {
            header.style.padding = '1rem 0';
        }
    }

    // Utilitários
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
        };
    }

    // Toast notifications
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        
        // Estilos do toast
        Object.assign(toast.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            padding: '1rem 1.5rem',
            borderRadius: '0.5rem',
            color: 'white',
            fontWeight: '500',
            zIndex: '10000',
            transform: 'translateX(100%)',
            transition: 'transform 0.3s ease',
            maxWidth: '300px',
            wordWrap: 'break-word'
        });

        // Cores baseadas no tipo
        const colors = {
            success: '#22c55e',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };

        toast.style.background = colors[type] || colors.info;
        toast.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.2)';

        document.body.appendChild(toast);

        // Animar entrada
        requestAnimationFrame(() => {
            toast.style.transform = 'translateX(0)';
        });

        // Remover após 3 segundos
        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }

    // Analytics tracking
    function trackHeaderInteractions() {
        // Tracking de cliques nos links de navegação
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                const href = this.getAttribute('href');
                const text = this.textContent.trim();
                
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'header_nav_click', {
                        'event_category': 'Header',
                        'event_label': text,
                        'link_url': href
                    });
                }
            });
        });

        // Tracking de cliques nos botões
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                const text = this.textContent.trim();
                
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'header_button_click', {
                        'event_category': 'Header',
                        'event_label': text
                    });
                }
            });
        });
    }

    // Verificar se o DOM está pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expor funções públicas
    window.HeaderOriginalModern = {
        showToast,
        init,
        trackHeaderInteractions
    };

    // Auto-inicializar tracking se disponível
    if (typeof gtag !== 'undefined') {
        trackHeaderInteractions();
    }

})();

