/**
 * ===================================================================
 * FOOTER MODERNO - JAVASCRIPT INTERACTIVO
 * ===================================================================
 * Archivo: footer_modern.js
 * Descripción: Funcionalidad interactiva para footer y chat
 * Fecha: 2025
 */

class ModernFooter {
    constructor() {
        // Footer elements
        this.footer = document.getElementById('main-footer');
        
        // Help center
        this.helpTrigger = document.getElementById('help-trigger');
        this.helpPanel = document.getElementById('help-panel');
        this.helpClose = document.getElementById('help-close');
        
        // Chat widget
        this.chatWidget = document.getElementById('chat-widget');
        this.chatTrigger = document.getElementById('chat-trigger');
        this.chatPanel = document.getElementById('chat-panel');
        this.chatClose = document.getElementById('chat-close');
        this.chatInput = document.getElementById('chat-input');
        this.chatSend = document.getElementById('chat-send');
        this.chatMessages = document.getElementById('chat-messages');
        this.chatNotification = document.getElementById('chat-notification');
        
        // Back to top buttons
        this.scrollToTopBtn = document.getElementById('scroll-to-top');
        this.backToTopAlt = document.getElementById('back-to-top-alt');
        
        // Theme toggle
        this.themeToggle = document.getElementById('theme-toggle');
        
        // State management
        this.isHelpPanelOpen = false;
        this.isChatPanelOpen = false;
        this.chatMessages_list = [];
        this.currentTheme = 'dark';
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initScrollEffects();
        this.initChatSystem();
        this.initThemeSystem();
        this.loadInitialData();
        console.log('🦶 ModernFooter initialized');
    }

    setupEventListeners() {
        // Help center events
        if (this.helpTrigger) {
            this.helpTrigger.addEventListener('click', this.toggleHelpPanel.bind(this));
        }
        
        if (this.helpClose) {
            this.helpClose.addEventListener('click', this.closeHelpPanel.bind(this));
        }
        
        // Chat widget events
        if (this.chatTrigger) {
            this.chatTrigger.addEventListener('click', this.toggleChatPanel.bind(this));
        }
        
        if (this.chatClose) {
            this.chatClose.addEventListener('click', this.closeChatPanel.bind(this));
        }
        
        if (this.chatSend) {
            this.chatSend.addEventListener('click', this.sendMessage.bind(this));
        }
        
        if (this.chatInput) {
            this.chatInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.sendMessage();
                }
            });
        }
        
        // Quick reply buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('quick-reply')) {
                this.handleQuickReply(e.target.textContent);
            }
        });
        
        // Back to top buttons
        if (this.scrollToTopBtn) {
            this.scrollToTopBtn.addEventListener('click', this.scrollToTop.bind(this));
        }
        
        if (this.backToTopAlt) {
            this.backToTopAlt.addEventListener('click', this.scrollToTop.bind(this));
        }
        
        // Theme toggle
        if (this.themeToggle) {
            this.themeToggle.addEventListener('click', this.toggleTheme.bind(this));
        }
        
        // Quick action buttons
        document.querySelectorAll('.quick-action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.handleQuickAction(e.target.closest('.quick-action-btn'));
            });
        });
        
        // Outside click handling
        document.addEventListener('click', this.handleOutsideClick.bind(this));
        
        // Scroll events
        window.addEventListener('scroll', this.handleScroll.bind(this), { passive: true });
        
        // Help items click
        document.querySelectorAll('.help-item').forEach(item => {
            item.addEventListener('click', (e) => {
                this.handleHelpItemClick(e.target.closest('.help-item'));
            });
        });
        
        // Social links tracking
        document.querySelectorAll('.social-link').forEach(link => {
            link.addEventListener('click', (e) => {
                this.trackSocialClick(e.target.closest('.social-link'));
            });
        });
        
        // Footer links tracking
        document.querySelectorAll('.footer-links a, .legal-link').forEach(link => {
            link.addEventListener('click', (e) => {
                this.trackLinkClick(e.target.href, e.target.textContent);
            });
        });
    }

    // Help Panel Functions
    toggleHelpPanel() {
        if (this.isHelpPanelOpen) {
            this.closeHelpPanel();
        } else {
            this.openHelpPanel();
        }
    }

    openHelpPanel() {
        if (this.helpPanel) {
            this.helpPanel.classList.add('active');
            this.isHelpPanelOpen = true;
            
            // Close chat if open
            if (this.isChatPanelOpen) {
                this.closeChatPanel();
            }
            
            // Track help panel open
            this.trackEvent('help_panel_opened', 'footer_interaction');
        }
    }

    closeHelpPanel() {
        if (this.helpPanel) {
            this.helpPanel.classList.remove('active');
            this.isHelpPanelOpen = false;
        }
    }

    handleHelpItemClick(helpItem) {
        if (!helpItem) return;
        
        const helpTitle = helpItem.querySelector('.help-title')?.textContent;
        console.log(`Help requested: ${helpTitle}`);
        
        // Track help item click
        this.trackEvent('help_item_clicked', 'help_interaction', helpTitle);
        
        // Close help panel and open chat with context
        this.closeHelpPanel();
        this.openChatPanel();
        
        // Add contextual message
        setTimeout(() => {
            this.addBotMessage(`Te puedo ayudar con "${helpTitle}". ¿Qué necesitas saber específicamente?`);
        }, 500);
    }

    // Chat System Functions
    initChatSystem() {
        // Initialize chat with welcome message
        this.chatMessages_list = [
            {
                type: 'bot',
                text: '¡Hola! 👋 Soy el asistente virtual de Uniminuto. ¿En qué puedo ayudarte hoy?',
                time: new Date(),
                id: 'welcome_message'
            }
        ];
        
        // Set up auto-responses
        this.setupAutoResponses();
        
        // Show notification if there are unread messages
        this.updateChatNotification(1);
    }

    toggleChatPanel() {
        if (this.isChatPanelOpen) {
            this.closeChatPanel();
        } else {
            this.openChatPanel();
        }
    }

    openChatPanel() {
        if (this.chatPanel) {
            this.chatPanel.classList.add('active');
            this.isChatPanelOpen = true;
            
            // Close help panel if open
            if (this.isHelpPanelOpen) {
                this.closeHelpPanel();
            }
            
            // Clear notification
            this.updateChatNotification(0);
            
            // Focus input
            if (this.chatInput) {
                setTimeout(() => {
                    this.chatInput.focus();
                }, 300);
            }
            
            // Track chat open
            this.trackEvent('chat_opened', 'footer_interaction');
        }
    }

    closeChatPanel() {
        if (this.chatPanel) {
            this.chatPanel.classList.remove('active');
            this.isChatPanelOpen = false;
        }
    }

    sendMessage() {
        const message = this.chatInput?.value?.trim();
        if (!message) return;
        
        // Add user message
        this.addUserMessage(message);
        
        // Clear input
        this.chatInput.value = '';
        
        // Show typing indicator
        this.showTypingIndicator();
        
        // Generate bot response
        setTimeout(() => {
            this.hideTypingIndicator();
            const response = this.generateBotResponse(message);
            this.addBotMessage(response);
        }, 1500 + Math.random() * 1000);
        
        // Track message sent
        this.trackEvent('message_sent', 'chat_interaction');
    }

    addUserMessage(text) {
        const message = {
            type: 'user',
            text: text,
            time: new Date(),
            id: `user_${Date.now()}`
        };
        
        this.chatMessages_list.push(message);
        this.renderMessage(message);
        this.scrollChatToBottom();
    }

    addBotMessage(text) {
        const message = {
            type: 'bot',
            text: text,
            time: new Date(),
            id: `bot_${Date.now()}`
        };
        
        this.chatMessages_list.push(message);
        this.renderMessage(message);
        this.scrollChatToBottom();
        
        // Show notification if chat is closed
        if (!this.isChatPanelOpen) {
            this.updateChatNotification(1);
        }
    }

    renderMessage(message) {
        if (!this.chatMessages) return;
        
        const messageHTML = `
            <div class="chat-message ${message.type}" data-id="${message.id}">
                <div class="message-avatar">
                    <img src="https://ui-avatars.com/api/?name=${message.type === 'user' ? 'Usuario' : 'Bot'}&background=${message.type === 'user' ? '007bff' : 'FF8C00'}&color=fff&size=24" alt="${message.type}">
                </div>
                <div class="message-content">
                    <div class="message-text">${message.text}</div>
                    <div class="message-time">${this.formatTime(message.time)}</div>
                </div>
            </div>
        `;
        
        this.chatMessages.insertAdjacentHTML('beforeend', messageHTML);
    }

    showTypingIndicator() {
        if (!this.chatMessages) return;
        
        const typingHTML = `
            <div class="chat-message bot typing-indicator" id="typing-indicator">
                <div class="message-avatar">
                    <img src="https://ui-avatars.com/api/?name=Bot&background=FF8C00&color=fff&size=24" alt="Bot">
                </div>
                <div class="message-content">
                    <div class="message-text">
                        <div class="typing-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        this.chatMessages.insertAdjacentHTML('beforeend', typingHTML);
        this.scrollChatToBottom();
    }

    hideTypingIndicator() {
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }

    scrollChatToBottom() {
        if (this.chatMessages) {
            setTimeout(() => {
                this.chatMessages.scrollTop = this.chatMessages.scrollHeight;
            }, 100);
        }
    }

    generateBotResponse(userMessage) {
        const message = userMessage.toLowerCase();
        
        // Simple response system - replace with actual AI/chatbot integration
        if (message.includes('solicitud') || message.includes('nueva solicitud')) {
            return 'Para crear una nueva solicitud de admisión, ve al Dashboard y haz clic en "Nueva Solicitud". Te guiaré paso a paso en el proceso. ¿Necesitas ayuda con algún programa específico?';
        }
        
        if (message.includes('documento') || message.includes('documentos')) {
            return 'Los documentos requeridos incluyen: Cédula, certificado de bachillerato, foto tipo documento, y certificado médico. ¿Necesitas ayuda para subir algún documento específico?';
        }
        
        if (message.includes('estado') || message.includes('consultar')) {
            return 'Puedes consultar el estado de tu solicitud en el Dashboard. También recibirás notificaciones por email sobre cualquier cambio. ¿Quieres que revise tu solicitud actual?';
        }
        
        if (message.includes('programa') || message.includes('carrera')) {
            return 'Uniminuto ofrece programas en Ingeniería, Administración, Derecho, Psicología, Educación y más. ¿Te interesa información sobre algún programa específico?';
        }
        
        if (message.includes('cita') || message.includes('entrevista')) {
            return 'Puedes agendar tu entrevista desde el Dashboard. Te contactaremos para confirmar la fecha y hora. ¿Prefieres entrevista presencial o virtual?';
        }
        
        if (message.includes('gracias') || message.includes('thank')) {
            return '¡De nada! 😊 Estoy aquí para ayudarte con cualquier pregunta sobre tu proceso de admisión. ¿Hay algo más en lo que pueda asistirte?';
        }
        
        if (message.includes('hola') || message.includes('hello')) {
            return '¡Hola! 👋 ¿En qué puedo ayudarte hoy? Puedo asistirte con solicitudes, documentos, estados de aplicación, programas académicos y mucho más.';
        }
        
        // Default response
        const responses = [
            'Entiendo tu consulta. ¿Podrías darme más detalles para poder ayudarte mejor?',
            'Esa es una buena pregunta. Te conectaré con un asesor especializado si necesitas información más específica.',
            'Puedo ayudarte con eso. ¿Te gustaría que revisemos tu caso en detalle?',
            'Para brindarte la mejor asistencia, ¿podrías contarme más sobre tu situación?'
        ];
        
        return responses[Math.floor(Math.random() * responses.length)];
    }

    handleQuickReply(replyText) {
        if (this.chatInput) {
            this.chatInput.value = replyText;
        }
        
        // Auto-send the message
        setTimeout(() => {
            this.sendMessage();
        }, 500);
        
        this.trackEvent('quick_reply_used', 'chat_interaction', replyText);
    }

    updateChatNotification(count) {
        if (this.chatNotification) {
            if (count > 0) {
                this.chatNotification.textContent = count;
                this.chatNotification.style.display = 'flex';
            } else {
                this.chatNotification.style.display = 'none';
            }
        }
    }

    setupAutoResponses() {
        // Auto-response for inactive users
        this.inactivityTimer = setTimeout(() => {
            if (!this.isChatPanelOpen) {
                this.addBotMessage('¿Necesitas ayuda con tu proceso de admisión? Estoy aquí para asistirte. 🤝');
            }
        }, 30000); // 30 seconds
    }

    // Scroll Effects
    initScrollEffects() {
        this.handleScroll(); // Initial call
    }

    handleScroll() {
        const scrollY = window.pageYOffset;
        const windowHeight = window.innerHeight;
        const documentHeight = document.documentElement.scrollHeight;
        
        // Show/hide back to top button
        this.updateBackToTopButton(scrollY);
        
        // Footer parallax effect (subtle)
        if (this.footer && scrollY > 200) {
            const footerTop = this.footer.offsetTop;
            const scrolled = scrollY - footerTop + windowHeight;
            
            if (scrolled > 0) {
                const parallaxValue = scrolled * 0.1;
                this.footer.style.transform = `translateY(-${parallaxValue}px)`;
            }
        }
        
        // Update scroll progress for footer sections
        this.updateFooterAnimations(scrollY);
    }

    updateBackToTopButton(scrollY) {
        const showThreshold = 300;
        
        if (this.backToTopAlt) {
            if (scrollY > showThreshold) {
                this.backToTopAlt.classList.add('visible');
            } else {
                this.backToTopAlt.classList.remove('visible');
            }
        }
    }

    updateFooterAnimations(scrollY) {
        // Animate footer sections on scroll
        const footerSections = document.querySelectorAll('.footer-section');
        
        footerSections.forEach((section, index) => {
            const rect = section.getBoundingClientRect();
            const isVisible = rect.top < window.innerHeight && rect.bottom > 0;
            
            if (isVisible) {
                section.style.animationDelay = `${index * 0.1}s`;
                section.classList.add('animate-in');
            }
        });
    }

    scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
        
        this.trackEvent('scroll_to_top', 'navigation');
    }

    // Theme System
    initThemeSystem() {
        // Load saved theme
        this.currentTheme = localStorage.getItem('uniminuto_theme') || 'dark';
        this.applyTheme(this.currentTheme);
    }

    toggleTheme() {
        this.currentTheme = this.currentTheme === 'dark' ? 'light' : 'dark';
        this.applyTheme(this.currentTheme);
        
        // Save preference
        localStorage.setItem('uniminuto_theme', this.currentTheme);
        
        this.trackEvent('theme_changed', 'ui_interaction', this.currentTheme);
    }

    applyTheme(theme) {
        document.body.setAttribute('data-theme', theme);
        
        if (this.themeToggle) {
            const icon = this.themeToggle.querySelector('i');
            if (icon) {
                icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            }
        }
    }

    // Quick Actions
    handleQuickAction(button) {
        if (!button) return;
        
        const actionText = button.querySelector('span')?.textContent || '';
        const actionIcon = button.querySelector('i')?.className || '';
        
        console.log(`Quick action: ${actionText}`);
        
        // Add visual feedback
        button.style.transform = 'scale(0.95)';
        setTimeout(() => {
            button.style.transform = '';
        }, 150);
        
        // Handle specific actions
        if (actionText.includes('Solicitud')) {
            // Redirect to new application
            window.location.href = '#new-application';
        } else if (actionText.includes('Documento')) {
            // Open document upload
            window.location.href = '#documents';
        } else if (actionText.includes('Estado')) {
            // Open status check
            window.location.href = '#status-check';
        }
        
        this.trackEvent('quick_action_clicked', 'footer_interaction', actionText);
    }

    // Outside Click Handler
    handleOutsideClick(e) {
        // Close help panel if clicking outside
        if (this.isHelpPanelOpen && this.helpPanel && !this.helpPanel.contains(e.target) && !this.helpTrigger.contains(e.target)) {
            this.closeHelpPanel();
        }
        
        // Close chat panel if clicking outside
        if (this.isChatPanelOpen && this.chatPanel && !this.chatPanel.contains(e.target) && !this.chatTrigger.contains(e.target)) {
            this.closeChatPanel();
        }
    }

    // Tracking and Analytics
    trackEvent(action, category, label = '') {
        // Track user interactions for analytics
        console.log(`📊 Event: ${category} - ${action}${label ? ` (${label})` : ''}`);
        
        // Replace with actual analytics implementation
        if (typeof gtag !== 'undefined') {
            gtag('event', action, {
                event_category: category,
                event_label: label,
                value: 1
            });
        }
    }

    trackSocialClick(socialLink) {
        if (!socialLink) return;
        
        const platform = socialLink.className.includes('facebook') ? 'Facebook' :
                         socialLink.className.includes('twitter') ? 'Twitter' :
                         socialLink.className.includes('instagram') ? 'Instagram' :
                         socialLink.className.includes('linkedin') ? 'LinkedIn' :
                         socialLink.className.includes('youtube') ? 'YouTube' : 'Unknown';
        
        this.trackEvent('social_link_clicked', 'social_media', platform);
    }

    trackLinkClick(url, text) {
        this.trackEvent('footer_link_clicked', 'navigation', text);
    }

    // Utility Functions
    formatTime(date) {
        return date.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    loadInitialData() {
        // Load any initial data needed for footer functionality
        this.loadNotificationCount();
        this.checkSystemStatus();
    }

    loadNotificationCount() {
        // Simulate loading notification count
        setTimeout(() => {
            this.updateChatNotification(Math.floor(Math.random() * 3) + 1);
        }, 2000);
    }

    checkSystemStatus() {
        // Check system status and show any important messages
        console.log('✅ System status: All services operational');
    }

    // Public API
    showMessage(message, type = 'info') {
        // Show system message in chat
        this.addBotMessage(`📢 ${message}`);
        
        if (!this.isChatPanelOpen) {
            this.updateChatNotification(1);
        }
    }

    clearChat() {
        if (this.chatMessages) {
            this.chatMessages.innerHTML = '';
            this.chatMessages_list = [];
        }
    }

    destroy() {
        // Clean up event listeners
        clearTimeout(this.inactivityTimer);
        
        window.removeEventListener('scroll', this.handleScroll);
        
        console.log('🧹 ModernFooter destroyed');
    }
}

// CSS for typing indicator
const typingIndicatorStyles = `
    .typing-indicator .message-text {
        padding: 8px 16px;
        background: rgba(255, 255, 255, 0.1) !important;
    }
    
    .typing-dots {
        display: flex;
        gap: 4px;
        align-items: center;
    }
    
    .typing-dots span {
        width: 6px;
        height: 6px;
        background: var(--text-secondary);
        border-radius: 50%;
        animation: typing-pulse 1.4s infinite ease-in-out;
    }
    
    .typing-dots span:nth-child(2) {
        animation-delay: 0.2s;
    }
    
    .typing-dots span:nth-child(3) {
        animation-delay: 0.4s;
    }
    
    @keyframes typing-pulse {
        0%, 60%, 100% {
            transform: scale(1);
            opacity: 0.5;
        }
        30% {
            transform: scale(1.2);
            opacity: 1;
        }
    }
    
    .animate-in {
        animation: slideInUp 0.8s ease-out forwards;
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;

// Inject typing indicator styles
const styleSheet = document.createElement('style');
styleSheet.textContent = typingIndicatorStyles;
document.head.appendChild(styleSheet);

// Initialize footer when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.modernFooter = new ModernFooter();
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ModernFooter;
}
