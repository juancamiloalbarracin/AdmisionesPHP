/**
 * ===================================================================
 * UNIMINUTO SIGNUP MODERN - JAVASCRIPT FUNCIONALIDAD
 * ===================================================================
 * Archivo: signup_modern.js
 * Descripción: Funcionalidad del formulario de registro multi-paso
 * Fecha: 2025
 */

// ===================================================================
// CONFIGURACIÓN GLOBAL Y VARIABLES
// ===================================================================
const SIGNUP_CONFIG = {
    API_BASE_URL: 'http://localhost:8000/api',
    STEPS_TOTAL: 4,
    PASSWORD_MIN_LENGTH: 8,
    PHONE_PATTERN: /^[0-9]{10}$/,
    EMAIL_PATTERN: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    DOCUMENT_PATTERNS: {
        CC: /^[0-9]{7,10}$/,
        TI: /^[0-9]{7,11}$/,
        CE: /^[0-9]{6,10}$/,
        PA: /^[A-Z]{2}[0-9]{6,8}$/
    }
};

let currentStep = 1;
let formData = {};
let validationState = {
    step1: false,
    step2: false,
    step3: false,
    step4: false
};

// ===================================================================
// INICIALIZACIÓN AL CARGAR EL DOM
// ===================================================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando sistema de registro Uniminuto...');
    
    initializeEventListeners();
    initializeFormValidation();
    initializePasswordStrength();
    updateProgressBar();
    
    console.log('✅ Sistema inicializado correctamente');
});

// ===================================================================
// CONFIGURACIÓN DE EVENT LISTENERS
// ===================================================================
function initializeEventListeners() {
    // Botones de navegación
    document.querySelectorAll('.btn-next').forEach(btn => {
        btn.addEventListener('click', handleNextStep);
    });
    
    document.querySelectorAll('.btn-prev').forEach(btn => {
        btn.addEventListener('click', handlePrevStep);
    });
    
    // Formulario principal
    const form = document.getElementById('signupForm');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }
    
    // Campos de contraseña
    document.querySelectorAll('.password-toggle').forEach(btn => {
        btn.addEventListener('click', handlePasswordToggle);
    });
    
    // Validación en tiempo real
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', handleInputChange);
    });
    
    // Select personalizado
    document.querySelectorAll('select').forEach(select => {
        select.addEventListener('change', handleSelectChange);
    });
    
    console.log('📡 Event listeners configurados');
}

// ===================================================================
// NAVEGACIÓN ENTRE PASOS
// ===================================================================
function handleNextStep(e) {
    e.preventDefault();
    
    if (validateCurrentStep()) {
        saveCurrentStepData();
        
        if (currentStep < SIGNUP_CONFIG.STEPS_TOTAL) {
            goToStep(currentStep + 1);
        }
    } else {
        showStepError('Por favor, completa todos los campos requeridos correctamente.');
    }
}

function handlePrevStep(e) {
    e.preventDefault();
    
    if (currentStep > 1) {
        goToStep(currentStep - 1);
    }
}

function goToStep(stepNumber) {
    const currentStepEl = document.querySelector(`.form-step[data-step="${currentStep}"]`);
    const nextStepEl = document.querySelector(`.form-step[data-step="${stepNumber}"]`);
    
    if (!currentStepEl || !nextStepEl) return;
    
    // Animación de salida
    const slideDirection = stepNumber > currentStep ? 'slide-out-left' : 'slide-out-right';
    currentStepEl.classList.add(slideDirection);
    
    setTimeout(() => {
        // Ocultar paso actual
        currentStepEl.classList.remove('active', slideDirection);
        
        // Mostrar nuevo paso
        nextStepEl.classList.add('active');
        
        // Actualizar estado
        currentStep = stepNumber;
        updateProgressBar();
        
        // Si es el paso de confirmación, llenar el resumen
        if (currentStep === 4) {
            fillConfirmationSummary();
        }
        
        // Scroll al top
        document.querySelector('.signup-main').scrollTop = 0;
        
    }, 300);
}

// ===================================================================
// VALIDACIÓN DE PASOS
// ===================================================================
function validateCurrentStep() {
    const stepElement = document.querySelector(`.form-step[data-step="${currentStep}"]`);
    const requiredFields = stepElement.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!validateField({ target: field })) {
            isValid = false;
        }
    });
    
    // Validaciones específicas por paso
    switch (currentStep) {
        case 1:
            isValid = validateStep1() && isValid;
            break;
        case 2:
            isValid = validateStep2() && isValid;
            break;
        case 3:
            isValid = validateStep3() && isValid;
            break;
        case 4:
            isValid = validateStep4() && isValid;
            break;
    }
    
    validationState[`step${currentStep}`] = isValid;
    return isValid;
}

function validateStep1() {
    const nombres = document.getElementById('nombres').value.trim();
    const apellidos = document.getElementById('apellidos').value.trim();
    const email = document.getElementById('email').value.trim();
    const telefono = document.getElementById('telefono').value.trim();
    
    let isValid = true;
    
    // Validar nombres
    if (nombres.length < 2) {
        showFieldError('nombres', 'Los nombres deben tener al menos 2 caracteres');
        isValid = false;
    } else {
        hideFieldError('nombres');
    }
    
    // Validar apellidos
    if (apellidos.length < 2) {
        showFieldError('apellidos', 'Los apellidos deben tener al menos 2 caracteres');
        isValid = false;
    } else {
        hideFieldError('apellidos');
    }
    
    // Validar email
    if (!SIGNUP_CONFIG.EMAIL_PATTERN.test(email)) {
        showFieldError('email', 'Ingresa un email válido');
        isValid = false;
    } else {
        hideFieldError('email');
    }
    
    // Validar teléfono
    const phoneDigits = telefono.replace(/\D/g, '');
    if (phoneDigits.length !== 10) {
        showFieldError('telefono', 'El teléfono debe tener 10 dígitos');
        isValid = false;
    } else {
        hideFieldError('telefono');
    }
    
    return isValid;
}

function validateStep2() {
    const tipoDocumento = document.getElementById('tipoDocumento').value;
    const numeroDocumento = document.getElementById('numeroDocumento').value.trim();
    const direccion = document.getElementById('direccion').value.trim();
    
    let isValid = true;
    
    // Validar tipo de documento
    if (!tipoDocumento) {
        showFieldError('tipoDocumento', 'Selecciona un tipo de documento');
        isValid = false;
    } else {
        hideFieldError('tipoDocumento');
    }
    
    // Validar número de documento
    if (tipoDocumento && numeroDocumento) {
        const pattern = SIGNUP_CONFIG.DOCUMENT_PATTERNS[tipoDocumento];
        if (pattern && !pattern.test(numeroDocumento)) {
            showFieldError('numeroDocumento', 'Formato de documento inválido');
            isValid = false;
        } else {
            hideFieldError('numeroDocumento');
        }
    }
    
    // Validar dirección
    if (direccion.length < 10) {
        showFieldError('direccion', 'La dirección debe ser más específica');
        isValid = false;
    } else {
        hideFieldError('direccion');
    }
    
    return isValid;
}

function validateStep3() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    let isValid = true;
    
    // Validar fortaleza de contraseña
    const passwordStrength = checkPasswordStrength(password);
    if (passwordStrength.score < 3) {
        showFieldError('password', 'La contraseña no cumple con los requisitos de seguridad');
        isValid = false;
    } else {
        hideFieldError('password');
    }
    
    // Validar confirmación
    if (password !== confirmPassword) {
        showFieldError('confirmPassword', 'Las contraseñas no coinciden');
        isValid = false;
    } else {
        hideFieldError('confirmPassword');
    }
    
    return isValid;
}

function validateStep4() {
    const acceptTerms = document.getElementById('acceptTerms').checked;
    
    if (!acceptTerms) {
        showStepError('Debes aceptar los términos y condiciones para continuar');
        return false;
    }
    
    return true;
}

// ===================================================================
// VALIDACIÓN DE CAMPOS INDIVIDUALES
// ===================================================================
function validateField(e) {
    const field = e.target;
    const value = field.value.trim();
    let isValid = true;
    
    // Validación básica de campos requeridos
    if (field.hasAttribute('required') && !value) {
        showFieldError(field.id, 'Este campo es requerido');
        return false;
    }
    
    // Validaciones específicas por tipo
    switch (field.type) {
        case 'email':
            if (value && !SIGNUP_CONFIG.EMAIL_PATTERN.test(value)) {
                showFieldError(field.id, 'Ingresa un email válido');
                isValid = false;
            }
            break;
            
        case 'tel':
            const phoneDigits = value.replace(/\D/g, '');
            if (value && phoneDigits.length !== 10) {
                showFieldError(field.id, 'El teléfono debe tener 10 dígitos');
                isValid = false;
            }
            break;
            
        case 'password':
            if (field.id === 'password' && value) {
                const strength = checkPasswordStrength(value);
                updatePasswordStrength(strength);
                if (strength.score < 3) {
                    isValid = false;
                }
            } else if (field.id === 'confirmPassword' && value) {
                const password = document.getElementById('password').value;
                if (value !== password) {
                    showFieldError(field.id, 'Las contraseñas no coinciden');
                    isValid = false;
                }
            }
            break;
    }
    
    if (isValid) {
        hideFieldError(field.id);
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
    }
    
    return isValid;
}

// ===================================================================
// MANEJO DE CONTRASEÑAS
// ===================================================================
function initializePasswordStrength() {
    const passwordField = document.getElementById('password');
    if (passwordField) {
        passwordField.addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            updatePasswordStrength(strength);
            updatePasswordRequirements(this.value);
        });
    }
}

function checkPasswordStrength(password) {
    let score = 0;
    let feedback = [];
    
    // Longitud
    if (password.length >= 8) score++;
    else feedback.push('Mínimo 8 caracteres');
    
    // Mayúscula
    if (/[A-Z]/.test(password)) score++;
    else feedback.push('Una letra mayúscula');
    
    // Minúscula
    if (/[a-z]/.test(password)) score++;
    else feedback.push('Una letra minúscula');
    
    // Número
    if (/[0-9]/.test(password)) score++;
    else feedback.push('Un número');
    
    // Carácter especial
    if (/[^A-Za-z0-9]/.test(password)) score++;
    else feedback.push('Un carácter especial');
    
    const strength = ['muy débil', 'débil', 'aceptable', 'buena', 'muy fuerte'][score];
    
    return { score, strength, feedback };
}

function updatePasswordStrength(strengthData) {
    const strengthFill = document.querySelector('.strength-fill');
    const strengthText = document.querySelector('.strength-text');
    
    if (!strengthFill || !strengthText) return;
    
    // Actualizar barra visual
    strengthFill.className = 'strength-fill';
    
    switch (strengthData.score) {
        case 0:
        case 1:
            strengthFill.classList.add('weak');
            break;
        case 2:
            strengthFill.classList.add('fair');
            break;
        case 3:
            strengthFill.classList.add('good');
            break;
        case 4:
        case 5:
            strengthFill.classList.add('strong');
            break;
    }
    
    // Actualizar texto
    strengthText.textContent = `Contraseña ${strengthData.strength}`;
}

function updatePasswordRequirements(password) {
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password)
    };
    
    Object.keys(requirements).forEach(req => {
        const element = document.querySelector(`[data-requirement="${req}"]`);
        if (element) {
            if (requirements[req]) {
                element.classList.add('valid');
                element.querySelector('i').className = 'fas fa-check';
            } else {
                element.classList.remove('valid');
                element.querySelector('i').className = 'fas fa-times';
            }
        }
    });
}

function handlePasswordToggle(e) {
    const button = e.target.closest('.password-toggle');
    const targetId = button.getAttribute('data-target');
    const passwordField = document.getElementById(targetId);
    const icon = button.querySelector('i');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        passwordField.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// ===================================================================
// ACTUALIZACIÓN DE PROGRESS BAR
// ===================================================================
function updateProgressBar() {
    document.querySelectorAll('.progress-step').forEach((step, index) => {
        const stepNumber = index + 1;
        step.classList.remove('active', 'completed');
        
        if (stepNumber === currentStep) {
            step.classList.add('active');
        } else if (stepNumber < currentStep) {
            step.classList.add('completed');
        }
    });
}

// ===================================================================
// MANEJO DE DATOS DEL FORMULARIO
// ===================================================================
function saveCurrentStepData() {
    const stepElement = document.querySelector(`.form-step[data-step="${currentStep}"]`);
    const fields = stepElement.querySelectorAll('input, select, textarea');
    
    fields.forEach(field => {
        if (field.type === 'checkbox' || field.type === 'radio') {
            formData[field.name] = field.checked;
        } else {
            formData[field.name] = field.value.trim();
        }
    });
    
    console.log('💾 Datos del paso guardados:', formData);
}

function fillConfirmationSummary() {
    // Llenar resumen de datos personales
    document.getElementById('summaryNombre').textContent = 
        `${formData.nombres} ${formData.apellidos}`;
    document.getElementById('summaryEmail').textContent = formData.email;
    document.getElementById('summaryTelefono').textContent = formData.telefono;
    
    // Llenar resumen de documentación
    const tipoDocTexto = {
        'CC': 'Cédula de Ciudadanía',
        'TI': 'Tarjeta de Identidad',
        'CE': 'Cédula de Extranjería',
        'PA': 'Pasaporte'
    };
    
    document.getElementById('summaryDocumento').textContent = 
        `${tipoDocTexto[formData.tipo_documento] || formData.tipo_documento} - ${formData.numero_documento}`;
    document.getElementById('summaryDireccion').textContent = formData.direccion;
}

// ===================================================================
// ENVÍO DEL FORMULARIO
// ===================================================================
async function handleFormSubmit(e) {
    e.preventDefault();
    
    if (!validateCurrentStep()) {
        showStepError('Por favor, revisa y corrige los datos antes de continuar.');
        return;
    }
    
    saveCurrentStepData();
    
    const submitButton = document.querySelector('.btn-submit');
    const originalText = submitButton.innerHTML;
    
    try {
        // Mostrar loading
        submitButton.classList.add('loading');
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creando cuenta...';
        submitButton.disabled = true;
        
        // Preparar datos para envío
        const registrationData = {
            nombres: formData.nombres,
            apellidos: formData.apellidos,
            email: formData.email,
            telefono: formData.telefono,
            tipo_documento: formData.tipo_documento,
            numero_documento: formData.numero_documento,
            direccion: formData.direccion,
            password: formData.password
        };
        
        console.log('📤 Enviando datos de registro:', registrationData);
        
        // Enviar a la API
        const response = await fetch(`${SIGNUP_CONFIG.API_BASE_URL}/register`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(registrationData)
        });
        
        // Leer como texto, quitar BOM y parsear
        const rawText = await response.text();
        const cleaned = rawText.replace(/^\uFEFF/, '').trim();
        let result;
        try {
            result = cleaned ? JSON.parse(cleaned) : {};
        } catch (e2) {
            console.error('❌ Error parseando JSON:', e2, 'RAW:', cleaned.substring(0, 300));
            throw new Error('Respuesta no válida del servidor.');
        }
        
        if (response.ok && result.success) {
            showSuccessMessage('¡Cuenta creada exitosamente! Redirigiendo...');
            
            setTimeout(() => {
                window.location.href = 'login.php?message=registro_exitoso';
            }, 2000);
            
        } else {
            throw new Error(result.message || 'Error al crear la cuenta');
        }
        
    } catch (error) {
        console.error('❌ Error en el registro:', error);
        showStepError(error.message || 'Error al crear la cuenta. Por favor, intenta de nuevo.');
        
    } finally {
        // Restaurar botón
        submitButton.classList.remove('loading');
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    }
}

// ===================================================================
// MANEJO DE EVENTOS DE INPUTS
// ===================================================================
function handleInputChange(e) {
    const field = e.target;
    
    // Limpiar errores en tiempo real
    if (field.value.trim()) {
        hideFieldError(field.id);
    }
    
    // Formatear teléfono
    if (field.type === 'tel') {
        let value = field.value.replace(/\D/g, '');
        if (value.length > 10) {
            value = value.substring(0, 10);
        }
        // Formato: 300 123 4567
        if (value.length >= 6) {
            value = `${value.substring(0, 3)} ${value.substring(3, 6)} ${value.substring(6)}`;
        } else if (value.length >= 3) {
            value = `${value.substring(0, 3)} ${value.substring(3)}`;
        }
        field.value = value;
    }
}

function handleSelectChange(e) {
    const select = e.target;
    
    // Actualizar validación del select
    if (select.value) {
        hideFieldError(select.id);
        select.classList.remove('is-invalid');
        select.classList.add('is-valid');
    }
}

// ===================================================================
// MANEJO DE ERRORES Y MENSAJES
// ===================================================================
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    // Remover error existente
    hideFieldError(fieldId);
    
    // Crear elemento de error
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error text-danger mt-1';
    errorElement.style.fontSize = '0.85rem';
    errorElement.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i>${message}`;
    
    // Insertar después del campo o su contenedor
    const container = field.closest('.input-group') || field.closest('.select-group') || field;
    container.parentNode.insertBefore(errorElement, container.nextSibling);
    
    // Marcar campo como inválido
    field.classList.add('is-invalid');
    field.classList.remove('is-valid');
}

function hideFieldError(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    const container = field.closest('.form-group');
    if (container) {
        const errorElement = container.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
    }
}

function showStepError(message) {
    // Remover alert existente
    const existingAlert = document.querySelector('.step-alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    // Crear nuevo alert
    const alertElement = document.createElement('div');
    alertElement.className = 'alert alert-danger step-alert';
    alertElement.innerHTML = `<i class="fas fa-exclamation-triangle"></i>${message}`;
    
    // Insertar al inicio del paso actual
    const currentStepEl = document.querySelector(`.form-step[data-step="${currentStep}"]`);
    currentStepEl.insertBefore(alertElement, currentStepEl.firstChild);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        if (alertElement.parentNode) {
            alertElement.remove();
        }
    }, 5000);
}

function showSuccessMessage(message) {
    const alertElement = document.createElement('div');
    alertElement.className = 'alert alert-success step-alert';
    alertElement.innerHTML = `<i class="fas fa-check-circle"></i>${message}`;
    
    const currentStepEl = document.querySelector(`.form-step[data-step="${currentStep}"]`);
    currentStepEl.insertBefore(alertElement, currentStepEl.firstChild);
}

// ===================================================================
// UTILIDADES ADICIONALES
// ===================================================================
function formatPhoneNumber(phone) {
    const digits = phone.replace(/\D/g, '');
    if (digits.length === 10) {
        return `${digits.substring(0, 3)} ${digits.substring(3, 6)} ${digits.substring(6)}`;
    }
    return phone;
}

function sanitizeInput(input) {
    return input.trim().replace(/<script[^>]*>.*?<\/script>/gi, '');
}

// ===================================================================
// DEBUGGING Y DESARROLLO
// ===================================================================
if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    window.SIGNUP_DEBUG = {
        formData,
        validationState,
        currentStep,
        goToStep,
        validateCurrentStep,
        CONFIG: SIGNUP_CONFIG
    };
    
    console.log('🔧 Modo desarrollo activado. Variables disponibles en window.SIGNUP_DEBUG');
}

// ===================================================================
// EXPORTAR PARA USO EXTERNO
// ===================================================================
window.SignupManager = {
    goToStep,
    validateCurrentStep,
    getCurrentStep: () => currentStep,
    getFormData: () => ({ ...formData }),
    getValidationState: () => ({ ...validationState })
};
