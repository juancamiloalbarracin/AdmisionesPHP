/**
 * ===================================================================
 * NAVEGACIÓN MODERNA - JAVASCRIPT INTERACTIVO
 * ===================================================================
 * Archivo: navigation_modern.js
 * Descripción: Funcionalidad interactiva para navegación sticky
 * Fecha: 2025
 */

class ModernNavigation {
    constructor() {
        this.header = document.getElementById('main-header');
        this.scrollProgress = document.getElementById('scroll-progress-bar');
        this.pageLoader = document.getElementById('page-loading-overlay');
        this.mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        this.mobileNavOverlay = document.getElementById('mobile-nav-overlay');
        this.mobileNavClose = document.getElementById('mobile-nav-close');
        
        // Search functionality
        this.searchTrigger = document.getElementById('global-search-trigger');
        this.searchOverlay = document.getElementById('search-overlay');
        this.searchClose = document.getElementById('search-close');
        this.searchInput = document.getElementById('global-search-input');
        
        // Notifications
        this.notificationsTrigger = document.getElementById('notifications-trigger');
        this.notificationsPanel = document.getElementById('notifications-panel');
        
        // User profile
        this.userProfileTrigger = document.getElementById('user-profile-trigger');
        this.userProfilePanel = document.getElementById('user-profile-panel');
        
        this.lastScrollTop = 0;
        this.scrollThreshold = 50;
        this.activeDropdown = null;
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.handlePageLoad();
        this.initScrollEffects();
        this.initDropdowns();
        this.initSearch();
        this.initKeyboardNavigation();
        console.log('🚀 ModernNavigation initialized');
    }

    setupEventListeners() {
        // Scroll events
        window.addEventListener('scroll', this.handleScroll.bind(this), { passive: true });
        window.addEventListener('resize', this.handleResize.bind(this));
        
        // Mobile menu
        if (this.mobileMenuToggle) {
            this.mobileMenuToggle.addEventListener('click', this.toggleMobileMenu.bind(this));
        }
        
        if (this.mobileNavClose) {
            this.mobileNavClose.addEventListener('click', this.closeMobileMenu.bind(this));
        }
        
        if (this.mobileNavOverlay) {
            this.mobileNavOverlay.addEventListener('click', (e) => {
                if (e.target === this.mobileNavOverlay) {
                    this.closeMobileMenu();
                }
            });
        }
        
        // Search
        if (this.searchTrigger) {
            this.searchTrigger.addEventListener('click', this.openSearch.bind(this));
        }
        
        if (this.searchClose) {
            this.searchClose.addEventListener('click', this.closeSearch.bind(this));
        }
        
        if (this.searchOverlay) {
            this.searchOverlay.addEventListener('click', (e) => {
                if (e.target === this.searchOverlay) {
                    this.closeSearch();
                }
            });
        }
        
        // Notifications
        if (this.notificationsTrigger) {
            this.notificationsTrigger.addEventListener('click', this.toggleNotifications.bind(this));
        }
        
        // User profile
        if (this.userProfileTrigger) {
            this.userProfileTrigger.addEventListener('click', this.toggleUserProfile.bind(this));
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', this.handleOutsideClick.bind(this));
        
        // Keyboard shortcuts
        document.addEventListener('keydown', this.handleKeydown.bind(this));
        
        // Navigation items hover effects
        this.initNavigationEffects();
    }

    handlePageLoad() {
        // Hide page loader after content loads
        window.addEventListener('load', () => {
            setTimeout(() => {
                if (this.pageLoader) {
                    this.pageLoader.classList.add('hidden');
                    document.body.classList.remove('loading');
                    setTimeout(() => {
                        this.pageLoader.style.display = 'none';
                    }, 500);
                }
            }, 1000);
        });
        
        // Show loader initially
        if (this.pageLoader) {
            document.body.classList.add('loading');
        }
    }

    handleScroll() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const windowHeight = window.innerHeight;
        const documentHeight = document.documentElement.scrollHeight;
        
        // Update scroll progress
        this.updateScrollProgress(scrollTop, documentHeight, windowHeight);
        
        // Update header appearance
        this.updateHeaderAppearance(scrollTop);
        
        // Auto-hide header on mobile when scrolling down
        this.handleHeaderAutoHide(scrollTop);
        
        this.lastScrollTop = scrollTop;
    }

    updateScrollProgress(scrollTop, documentHeight, windowHeight) {
        if (this.scrollProgress) {
            const scrollPercent = (scrollTop / (documentHeight - windowHeight)) * 100;
            this.scrollProgress.style.width = Math.min(scrollPercent, 100) + '%';
        }
    }

    updateHeaderAppearance(scrollTop) {
        if (this.header) {
            if (scrollTop > this.scrollThreshold) {
                this.header.classList.add('scrolled');
            } else {
                this.header.classList.remove('scrolled');
            }
        }
    }

    handleHeaderAutoHide(scrollTop) {
        if (window.innerWidth <= 768) {
            const scrollDelta = scrollTop - this.lastScrollTop;
            
            if (scrollDelta > 10 && scrollTop > 200) {
                // Scrolling down - hide header
                this.header.style.transform = 'translateY(-100%)';
            } else if (scrollDelta < -10 || scrollTop < 100) {
                // Scrolling up - show header
                this.header.style.transform = 'translateY(0)';
            }
        } else {
            // Desktop - always show header
            this.header.style.transform = 'translateY(0)';
        }
    }

    handleResize() {
        // Close mobile menu on resize to desktop
        if (window.innerWidth > 992) {
            this.closeMobileMenu();
        }
        
        // Close all dropdowns on resize
        this.closeAllDropdowns();
    }

    // Mobile Menu Functions
    toggleMobileMenu() {
        if (this.mobileNavOverlay) {
            this.mobileNavOverlay.classList.toggle('active');
            this.mobileMenuToggle.classList.toggle('active');
            
            // Prevent body scroll
            if (this.mobileNavOverlay.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }
    }

    closeMobileMenu() {
        if (this.mobileNavOverlay) {
            this.mobileNavOverlay.classList.remove('active');
            this.mobileMenuToggle.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    // Search Functions
    openSearch() {
        if (this.searchOverlay) {
            this.searchOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Focus search input after animation
            setTimeout(() => {
                if (this.searchInput) {
                    this.searchInput.focus();
                }
            }, 300);
        }
    }

    closeSearch() {
        if (this.searchOverlay) {
            this.searchOverlay.classList.remove('active');
            document.body.style.overflow = '';
            
            // Clear search
            if (this.searchInput) {
                this.searchInput.value = '';
                this.clearSearchResults();
            }
        }
    }

    initSearch() {
        if (this.searchInput) {
            let searchTimeout;
            
            this.searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                const query = e.target.value.trim();
                
                if (query.length >= 2) {
                    searchTimeout = setTimeout(() => {
                        this.performSearch(query);
                    }, 300);
                } else {
                    this.clearSearchResults();
                }
            });
            
            this.searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const query = e.target.value.trim();
                    if (query) {
                        this.performSearch(query, true);
                    }
                } else if (e.key === 'Escape') {
                    this.closeSearch();
                }
            });
        }
    }

    performSearch(query, isExactSearch = false) {
        const resultsContainer = document.getElementById('search-results');
        if (!resultsContainer) return;
        
        // Show loading
        resultsContainer.innerHTML = `
            <div class="search-loading">
                <div class="loading-spinner">
                    <div class="spinner-ring"></div>
                </div>
                <p>Buscando "${query}"...</p>
            </div>
        `;
        
        // Simulate API call (replace with actual API call)
        setTimeout(() => {
            const mockResults = this.getMockSearchResults(query);
            this.displaySearchResults(mockResults, query);
        }, 800);
    }

    getMockSearchResults(query) {
        const mockData = [
            {
                type: 'application',
                title: 'Solicitud de Ingeniería de Sistemas',
                subtitle: 'Estado: En revisión',
                url: '#',
                icon: 'fas fa-file-alt',
                date: '2025-01-15'
            },
            {
                type: 'document',
                title: 'Certificado Académico',
                subtitle: 'Documento subido correctamente',
                url: '#',
                icon: 'fas fa-certificate',
                date: '2025-01-14'
            },
            {
                type: 'profile',
                title: 'Actualizar perfil académico',
                subtitle: 'Configuración de usuario',
                url: '#',
                icon: 'fas fa-user',
                date: '2025-01-13'
            }
        ];
        
        return mockData.filter(item => 
            item.title.toLowerCase().includes(query.toLowerCase()) ||
            item.subtitle.toLowerCase().includes(query.toLowerCase())
        );
    }

    displaySearchResults(results, query) {
        const resultsContainer = document.getElementById('search-results');
        if (!resultsContainer) return;
        
        if (results.length === 0) {
            resultsContainer.innerHTML = `
                <div class="search-no-results">
                    <div class="no-results-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h6>No se encontraron resultados</h6>
                    <p>No hay resultados para "${query}". Intenta con otros términos.</p>
                </div>
            `;
            return;
        }
        
        const resultsHTML = `
            <div class="search-results-header">
                <h6>Resultados para "${query}"</h6>
                <span class="results-count">${results.length} encontrado${results.length !== 1 ? 's' : ''}</span>
            </div>
            <div class="search-results-list">
                ${results.map(result => `
                    <a href="${result.url}" class="search-result-item">
                        <div class="result-icon">
                            <i class="${result.icon}"></i>
                        </div>
                        <div class="result-content">
                            <div class="result-title">${this.highlightQuery(result.title, query)}</div>
                            <div class="result-subtitle">${result.subtitle}</div>
                            <div class="result-date">${this.formatDate(result.date)}</div>
                        </div>
                        <div class="result-type">${result.type}</div>
                    </a>
                `).join('')}
            </div>
        `;
        
        resultsContainer.innerHTML = resultsHTML;
    }

    highlightQuery(text, query) {
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    clearSearchResults() {
        const resultsContainer = document.getElementById('search-results');
        if (resultsContainer) {
            resultsContainer.innerHTML = '';
        }
    }

    // Dropdown Functions
    initDropdowns() {
        // Initialize all dropdowns with proper event handling
        this.setupDropdownTriggers();
    }

    setupDropdownTriggers() {
        const dropdownTriggers = [
            { trigger: this.notificationsTrigger, panel: this.notificationsPanel },
            { trigger: this.userProfileTrigger, panel: this.userProfilePanel }
        ];
        
        dropdownTriggers.forEach(({ trigger, panel }) => {
            if (trigger && panel) {
                trigger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.toggleDropdown(panel);
                });
            }
        });
    }

    toggleDropdown(panel) {
        if (panel.classList.contains('active')) {
            this.closeDropdown(panel);
        } else {
            this.closeAllDropdowns();
            this.openDropdown(panel);
        }
    }

    openDropdown(panel) {
        panel.classList.add('active');
        this.activeDropdown = panel;
    }

    closeDropdown(panel) {
        panel.classList.remove('active');
        if (this.activeDropdown === panel) {
            this.activeDropdown = null;
        }
    }

    closeAllDropdowns() {
        const dropdowns = [this.notificationsPanel, this.userProfilePanel];
        dropdowns.forEach(dropdown => {
            if (dropdown) {
                this.closeDropdown(dropdown);
            }
        });
    }

    toggleNotifications(e) {
        e.stopPropagation();
        this.toggleDropdown(this.notificationsPanel);
        
        // Mark notifications as read when opened
        if (this.notificationsPanel.classList.contains('active')) {
            this.markNotificationsAsRead();
        }
    }

    toggleUserProfile(e) {
        e.stopPropagation();
        this.toggleDropdown(this.userProfilePanel);
    }

    markNotificationsAsRead() {
        // Update notification count
        const notificationCount = document.querySelector('.notification-count');
        if (notificationCount) {
            setTimeout(() => {
                notificationCount.textContent = '0';
                notificationCount.style.display = 'none';
            }, 1000);
        }
        
        // Mark individual notifications as read
        const unreadNotifications = document.querySelectorAll('.notification-item.unread');
        unreadNotifications.forEach(notification => {
            setTimeout(() => {
                notification.classList.remove('unread');
            }, 500);
        });
    }

    handleOutsideClick(e) {
        // Close dropdowns when clicking outside
        if (this.activeDropdown && !this.activeDropdown.contains(e.target)) {
            const triggers = [this.notificationsTrigger, this.userProfileTrigger];
            const isClickOnTrigger = triggers.some(trigger => 
                trigger && trigger.contains(e.target)
            );
            
            if (!isClickOnTrigger) {
                this.closeAllDropdowns();
            }
        }
    }

    // Keyboard Navigation
    initKeyboardNavigation() {
        // Add keyboard support for better accessibility
        this.setupKeyboardShortcuts();
    }

    handleKeydown(e) {
        // Global keyboard shortcuts
        if (e.ctrlKey || e.metaKey) {
            switch (e.key) {
                case 'k':
                    e.preventDefault();
                    this.openSearch();
                    break;
                case '/':
                    e.preventDefault();
                    this.openSearch();
                    break;
            }
        }
        
        // Escape key handling
        if (e.key === 'Escape') {
            this.closeSearch();
            this.closeMobileMenu();
            this.closeAllDropdowns();
        }
    }

    setupKeyboardShortcuts() {
        // Add keyboard navigation for dropdown items
        document.querySelectorAll('.nav-link, .profile-menu-item, .mobile-nav-item').forEach(item => {
            item.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    item.click();
                }
            });
        });
    }

    // Navigation Effects
    initNavigationEffects() {
        // Add hover effects and animations to navigation items
        const navItems = document.querySelectorAll('.nav-item');
        
        navItems.forEach(item => {
            const link = item.querySelector('.nav-link');
            if (link) {
                link.addEventListener('mouseenter', () => {
                    this.animateNavItem(item, 'enter');
                });
                
                link.addEventListener('mouseleave', () => {
                    this.animateNavItem(item, 'leave');
                });
            }
        });
        
        // Add click ripple effect
        this.initRippleEffect();
    }

    animateNavItem(item, action) {
        const indicator = item.querySelector('.nav-indicator');
        const icon = item.querySelector('.nav-icon');
        
        if (action === 'enter') {
            if (indicator) {
                indicator.style.transform = 'translateX(-50%) scaleX(1.1)';
            }
            if (icon) {
                icon.style.transform = 'translateY(-1px) scale(1.1)';
            }
        } else {
            if (indicator) {
                indicator.style.transform = 'translateX(-50%) scaleX(1)';
            }
            if (icon) {
                icon.style.transform = 'translateY(0) scale(1)';
            }
        }
    }

    initRippleEffect() {
        document.querySelectorAll('.nav-link, .btn-quick-action, .btn-favorite').forEach(element => {
            element.addEventListener('click', (e) => {
                this.createRipple(e, element);
            });
        });
    }

    createRipple(event, element) {
        const ripple = document.createElement('span');
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;
        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('ripple-effect');
        
        element.style.position = 'relative';
        element.style.overflow = 'hidden';
        element.appendChild(ripple);
        
        setTimeout(() => {
            ripple.remove();
        }, 600);
    }

    // Utility Functions
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Public API for external interaction
    scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    updateNotificationCount(count) {
        const notificationCount = document.querySelector('.notification-count');
        if (notificationCount) {
            notificationCount.textContent = count;
            notificationCount.style.display = count > 0 ? 'flex' : 'none';
            notificationCount.setAttribute('data-count', count);
        }
    }

    addNotification(notification) {
        // Add new notification to the panel
        const notificationsContent = document.querySelector('.notifications-content');
        if (notificationsContent) {
            const notificationHTML = `
                <div class="notification-item unread">
                    <div class="notification-avatar">
                        <i class="${notification.icon}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">${notification.title}</div>
                        <div class="notification-text">${notification.text}</div>
                        <div class="notification-time">ahora mismo</div>
                    </div>
                    <div class="notification-actions">
                        <button class="btn-view" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            `;
            
            notificationsContent.insertAdjacentHTML('afterbegin', notificationHTML);
            
            // Update counter
            const currentCount = parseInt(document.querySelector('.notification-count')?.textContent || '0');
            this.updateNotificationCount(currentCount + 1);
        }
    }

    destroy() {
        // Clean up event listeners when component is destroyed
        window.removeEventListener('scroll', this.handleScroll);
        window.removeEventListener('resize', this.handleResize);
        document.removeEventListener('click', this.handleOutsideClick);
        document.removeEventListener('keydown', this.handleKeydown);
        console.log('🧹 ModernNavigation destroyed');
    }
}

// CSS for ripple effect
const rippleStyles = `
    .ripple-effect {
        position: absolute;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        pointer-events: none;
        transform: scale(0);
        animation: ripple 0.6s ease-out;
    }
    
    @keyframes ripple {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }
    
    .search-loading {
        text-align: center;
        padding: 40px 20px;
        color: var(--text-secondary);
    }
    
    .loading-spinner .spinner-ring {
        width: 30px;
        height: 30px;
        margin: 0 auto 16px;
        border: 2px solid transparent;
        border-top-color: var(--primary-color);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    .search-no-results {
        text-align: center;
        padding: 40px 20px;
    }
    
    .no-results-icon {
        font-size: 48px;
        color: var(--text-muted);
        margin-bottom: 16px;
    }
    
    .search-results-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 16px;
    }
    
    .results-count {
        font-size: 12px;
        color: var(--text-secondary);
        background: rgba(255, 255, 255, 0.1);
        padding: 4px 8px;
        border-radius: 12px;
    }
    
    .search-result-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        color: var(--text-secondary);
        text-decoration: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.2s ease;
    }
    
    .search-result-item:hover {
        color: var(--text-primary);
        text-decoration: none;
        background: rgba(255, 255, 255, 0.03);
        margin: 0 -24px;
        padding: 12px 24px;
        border-radius: 8px;
    }
    
    .result-icon {
        width: 36px;
        height: 36px;
        background: rgba(255, 140, 0, 0.1);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
        font-size: 14px;
        flex-shrink: 0;
    }
    
    .result-content {
        flex: 1;
        min-width: 0;
    }
    
    .result-title {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 4px;
        line-height: 1.3;
    }
    
    .result-title mark {
        background: rgba(255, 140, 0, 0.3);
        color: var(--primary-color);
        padding: 1px 2px;
        border-radius: 2px;
    }
    
    .result-subtitle {
        font-size: 12px;
        color: var(--text-secondary);
        line-height: 1.3;
        margin-bottom: 2px;
    }
    
    .result-date {
        font-size: 11px;
        color: var(--text-muted);
    }
    
    .result-type {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        background: rgba(255, 255, 255, 0.1);
        padding: 2px 6px;
        border-radius: 4px;
        flex-shrink: 0;
    }
`;

// Inject ripple styles
const styleSheet = document.createElement('style');
styleSheet.textContent = rippleStyles;
document.head.appendChild(styleSheet);

// Initialize navigation when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.modernNavigation = new ModernNavigation();
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ModernNavigation;
}
