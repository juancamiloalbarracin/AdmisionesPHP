/**
 * =========================================================================
 * SISTEMA DE ADMISIONES UDC - FRONTEND PHP
 * =========================================================================
 * Archivo: app.js
 * Descripción: JavaScript principal para el frontend PHP
 * Funcionalidades: API calls, validaciones, UI interactions
 * Fecha: 2025-08-16
 * =========================================================================
 */

class AdmisionesApp {
    constructor() {
    // If running frontend on :3000, target backend on :8000
    const onDevPort = window.location.port === '3000';
    this.apiUrl = (onDevPort ? (window.location.protocol + '//' + window.location.hostname + ':8000') : '') + '/api';
        this.token = localStorage.getItem('jwt_token');
        this.currentUser = null;
        
        this.init();
    }

    init() {
        // Configurar axios interceptors si está disponible
        if (typeof axios !== 'undefined') {
            this.setupAxiosInterceptors();
        }
        
        // Verificar autenticación
        this.checkAuth();
        
        // Configurar event listeners
        this.setupEventListeners();
        
        // Mostrar notificaciones si existen
        this.showFlashMessages();
    }

    setupAxiosInterceptors() {
        // Request interceptor
        axios.interceptors.request.use(
            (config) => {
                if (this.token) {
                    config.headers.Authorization = `Bearer ${this.token}`;
                }
                return config;
            },
            (error) => Promise.reject(error)
        );

        // Response interceptor
        axios.interceptors.response.use(
            (response) => response,
            (error) => {
                if (error.response?.status === 401) {
                    this.logout();
                    this.showAlert('Sesión expirada. Por favor, inicie sesión nuevamente.', 'danger');
                }
                return Promise.reject(error);
            }
        );
    }

    setupEventListeners() {
        // Login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm && !loginForm.dataset.serverLogin) {
            loginForm.addEventListener('submit', (e) => this.handleLogin(e));
        }

        // Register form
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', (e) => this.handleRegister(e));
        }

        // Info personal form
        const infoPersonalForm = document.getElementById('infoPersonalForm');
        if (infoPersonalForm) {
            infoPersonalForm.addEventListener('submit', (e) => this.handleInfoPersonal(e));
        }

        // Info academica form
        const infoAcademicaForm = document.getElementById('infoAcademicaForm');
        if (infoAcademicaForm) {
            infoAcademicaForm.addEventListener('submit', (e) => this.handleInfoAcademica(e));
        }

        // Solicitud form
        const solicitudForm = document.getElementById('solicitudForm');
        if (solicitudForm) {
            solicitudForm.addEventListener('submit', (e) => this.handleSolicitud(e));
        }

        // Logout links
        const logoutLinks = document.querySelectorAll('.logout-link');
        logoutLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.logout();
            });
        });

        // Modal close buttons
        const modalCloseButtons = document.querySelectorAll('.modal-close');
        modalCloseButtons.forEach(button => {
            button.addEventListener('click', () => this.closeModal());
        });

        // Alert close buttons
        const alertCloseButtons = document.querySelectorAll('.alert-close');
        alertCloseButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.target.closest('.alert').style.display = 'none';
            });
        });
    }

    async checkAuth() {
        if (this.token) {
            try {
                const user = await this.getCurrentUser();
                if (user) {
                    this.currentUser = user;
                    this.updateUserInfo();
                } else {
                    this.logout();
                }
            } catch (error) {
                console.error('Error verificando autenticación:', error);
                this.logout();
            }
        }
    }

    async handleLogin(event) {
        event.preventDefault();
        
        const form = event.target;
        const submitButton = form.querySelector('button[type="submit"]');
        const email = form.email.value.trim();
        const password = form.password.value;

        if (!email || !password) {
            this.showAlert('Por favor, complete todos los campos', 'danger');
            return;
        }

        this.setLoading(submitButton, true);
        this.clearAlerts();

        try {
            const response = await this.makeRequest('/login', 'POST', {
                email: email,
                password: password
            });

            if (response.success) {
                this.token = response.token;
                localStorage.setItem('jwt_token', this.token);
                this.currentUser = response.user;
                
                this.showAlert('¡Inicio de sesión exitoso!', 'success');
                
                // Redirigir después de un pequeño delay
                setTimeout(() => {
                    window.location.href = 'main_modern.php';
                }, 800);
            } else {
                this.showAlert(response.message || 'Error en el inicio de sesión', 'danger');
            }
        } catch (error) {
            console.error('Error en login:', error);
            this.showAlert('Error de conexión. Intente nuevamente.', 'danger');
        } finally {
            this.setLoading(submitButton, false);
        }
    }

    async handleRegister(event) {
        event.preventDefault();
        
        const form = event.target;
        const submitButton = form.querySelector('button[type="submit"]');
        
        // Obtener datos del formulario
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Validaciones básicas
        if (!data.nombre || !data.apellido || !data.email || !data.password) {
            this.showAlert('Por favor, complete todos los campos obligatorios', 'danger');
            return;
        }

        if (data.password !== data.confirmPassword) {
            this.showAlert('Las contraseñas no coinciden', 'danger');
            return;
        }

        if (data.password.length < 6) {
            this.showAlert('La contraseña debe tener al menos 6 caracteres', 'danger');
            return;
        }

        this.setLoading(submitButton, true);
        this.clearAlerts();

        try {
            const response = await this.makeRequest('/auth/register', 'POST', {
                nombre: data.nombre,
                apellido: data.apellido,
                email: data.email,
                password: data.password
            });

            if (response.success) {
                this.showAlert('¡Registro exitoso! Ya puede iniciar sesión.', 'success');
                
                // Limpiar formulario
                form.reset();
                
                // Redirigir al login después de un delay
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
            } else {
                this.showAlert(response.message || 'Error en el registro', 'danger');
            }
        } catch (error) {
            console.error('Error en register:', error);
            this.showAlert('Error de conexión. Intente nuevamente.', 'danger');
        } finally {
            this.setLoading(submitButton, false);
        }
    }

    async handleInfoPersonal(event) {
        event.preventDefault();
        
        const form = event.target;
        const submitButton = form.querySelector('button[type="submit"]');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        this.setLoading(submitButton, true);
        this.clearAlerts();

        try {
            const response = await this.makeRequest('/info-personal', 'POST', data);

            if (response.success) {
                this.showAlert('Información personal guardada exitosamente', 'success');
                
                // Actualizar indicador de completitud si existe
                this.updateCompletionStatus();
            } else {
                this.showAlert(response.message || 'Error al guardar la información', 'danger');
            }
        } catch (error) {
            console.error('Error guardando info personal:', error);
            this.showAlert('Error de conexión. Intente nuevamente.', 'danger');
        } finally {
            this.setLoading(submitButton, false);
        }
    }

    async handleInfoAcademica(event) {
        event.preventDefault();
        
        const form = event.target;
        const submitButton = form.querySelector('button[type="submit"]');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        this.setLoading(submitButton, true);
        this.clearAlerts();

        try {
            const response = await this.makeRequest('/info-academica', 'POST', data);

            if (response.success) {
                this.showAlert('Información académica guardada exitosamente', 'success');
                
                // Actualizar indicador de completitud si existe
                this.updateCompletionStatus();
            } else {
                this.showAlert(response.message || 'Error al guardar la información', 'danger');
            }
        } catch (error) {
            console.error('Error guardando info académica:', error);
            this.showAlert('Error de conexión. Intente nuevamente.', 'danger');
        } finally {
            this.setLoading(submitButton, false);
        }
    }

    async handleSolicitud(event) {
        event.preventDefault();
        
        const form = event.target;
        const submitButton = form.querySelector('button[type="submit"]');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        this.setLoading(submitButton, true);
        this.clearAlerts();

        try {
            const response = await this.makeRequest('/solicitudes', 'POST', data);

            if (response.success) {
                this.showAlert('Solicitud enviada exitosamente', 'success');
                
                // Redirigir a la página principal después de un delay
                setTimeout(() => {
                    window.location.href = 'main.php';
                }, 2000);
            } else {
                this.showAlert(response.message || 'Error al enviar la solicitud', 'danger');
            }
        } catch (error) {
            console.error('Error enviando solicitud:', error);
            this.showAlert('Error de conexión. Intente nuevamente.', 'danger');
        } finally {
            this.setLoading(submitButton, false);
        }
    }

    async makeRequest(endpoint, method = 'GET', data = null) {
        const url = this.apiUrl + endpoint;
        
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            }
        };

        if (this.token) {
            options.headers.Authorization = `Bearer ${this.token}`;
        }

        if (data && (method === 'POST' || method === 'PUT')) {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(url, options);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        // BOM-safe JSON parsing
        const text = await response.text();
        const clean = text.replace(/^\uFEFF/, '').trim();
        try {
            return JSON.parse(clean || '{}');
        } catch (e) {
            console.error('Respuesta no-JSON del servidor:', clean || text);
            throw e;
        }
    }

    async getCurrentUser() {
        try {
            const response = await this.makeRequest('/users/profile');
            return response.success ? response.data : null;
        } catch (error) {
            console.error('Error obteniendo usuario:', error);
            return null;
        }
    }

    async loadCatalogs() {
        try {
            const response = await this.makeRequest('/solicitudes/catalogs');
            if (response.success) {
                return response.data;
            }
        } catch (error) {
            console.error('Error cargando catálogos:', error);
        }
        return null;
    }

    logout() {
        localStorage.removeItem('jwt_token');
        this.token = null;
        this.currentUser = null;
        window.location.href = 'login.php';
    }

    showAlert(message, type = 'info', duration = 5000) {
        // Remover alertas existentes
        this.clearAlerts();

        // Crear nueva alerta
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible`;
        alertDiv.innerHTML = `
            <div class="alert-content">
                <span class="alert-icon">
                    ${this.getAlertIcon(type)}
                </span>
                <span class="alert-message">${message}</span>
            </div>
            <button type="button" class="alert-close" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        `;

        // Agregar al DOM
        const container = document.querySelector('.alert-container') || document.body;
        if (container === document.body) {
            alertDiv.style.position = 'fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.right = '20px';
            alertDiv.style.zIndex = '9999';
            alertDiv.style.maxWidth = '400px';
        }
        
        container.insertBefore(alertDiv, container.firstChild);

        // Event listener para cerrar
        const closeBtn = alertDiv.querySelector('.alert-close');
        closeBtn.addEventListener('click', () => {
            alertDiv.remove();
        });

        // Auto-cerrar después del duration
        if (duration > 0) {
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, duration);
        }
    }

    getAlertIcon(type) {
        const icons = {
            success: '<i class="fas fa-check-circle"></i>',
            danger: '<i class="fas fa-exclamation-triangle"></i>',
            warning: '<i class="fas fa-exclamation-circle"></i>',
            info: '<i class="fas fa-info-circle"></i>'
        };
        return icons[type] || icons.info;
    }

    clearAlerts() {
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());
    }

    setLoading(element, loading) {
        if (loading) {
            element.disabled = true;
            element.innerHTML = '<span class="spinner"></span> Cargando...';
        } else {
            element.disabled = false;
            element.innerHTML = element.getAttribute('data-original-text') || 'Enviar';
        }
    }

    updateUserInfo() {
        if (this.currentUser) {
            const userNameElements = document.querySelectorAll('.user-name');
            userNameElements.forEach(element => {
                element.textContent = `${this.currentUser.nombre} ${this.currentUser.apellido}`;
            });

            const userEmailElements = document.querySelectorAll('.user-email');
            userEmailElements.forEach(element => {
                element.textContent = this.currentUser.email;
            });
        }
    }

    updateCompletionStatus() {
        // Actualizar indicadores de progreso si existen en la página
        const progressBars = document.querySelectorAll('.progress-bar');
        // Implementar lógica de progreso según necesidades específicas
    }

    showFlashMessages() {
        // Mostrar mensajes flash de PHP si existen
        const flashMessages = document.querySelectorAll('.flash-message');
        flashMessages.forEach(message => {
            const type = message.getAttribute('data-type') || 'info';
            const text = message.textContent.trim();
            if (text) {
                this.showAlert(text, type);
                message.remove();
            }
        });
    }

    closeModal() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.classList.remove('show');
        });
    }

    // Utilidades para formularios
    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    validateRequired(value) {
        return value && value.trim().length > 0;
    }

    formatDate(date) {
        if (!(date instanceof Date)) {
            date = new Date(date);
        }
        return date.toLocaleDateString('es-CO');
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP'
        }).format(amount);
    }
}

// Inicializar la aplicación cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    window.app = new AdmisionesApp();
});

// Funciones globales para compatibilidad con código existente
function showAlert(message, type = 'info') {
    if (window.app) {
        window.app.showAlert(message, type);
    }
}

function clearAlerts() {
    if (window.app) {
        window.app.clearAlerts();
    }
}
