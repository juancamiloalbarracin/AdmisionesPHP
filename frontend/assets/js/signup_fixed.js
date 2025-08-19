// ===================================================================
// SISTEMA DE REGISTRO MODERNO - UNIMINUTO
// ===================================================================

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
    
    initializeStepSystem();
    initializeValidation();
    initializeEventListeners();
    
    console.log('✅ Sistema de registro inicializado correctamente');
});

function initializeStepSystem() {
    updateStepProgress();
    loadStepData();
}

function initializeValidation() {
    // Configurar validación en tiempo real para cada campo
    const inputs = document.querySelectorAll('.form-control, .form-select');
    inputs.forEach(input => {
        input.addEventListener('blur', handleInputChange);
        input.addEventListener('input', handleInputChange);
        
        // Evento específico para contraseña
        if (input.id === 'password') {
            input.addEventListener('keyup', (e) => {
                updatePasswordStrength(e.target.value);
            });
            input.addEventListener('focus', (e) => {
                updatePasswordStrength(e.target.value);
            });
        }
    });
}

function initializeEventListeners() {
    // Botones de navegación
    const nextButtons = document.querySelectorAll('.btn-next');
    nextButtons.forEach(btn => {
        btn.addEventListener('click', handleNextStep);
    });
    
    const prevButtons = document.querySelectorAll('.btn-prev');
    prevButtons.forEach(btn => {
        btn.addEventListener('click', handlePrevStep);
    });
    
    // Submit del formulario
    const form = document.getElementById('signupForm');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }
    
    // Toggle de contraseñas
    const toggleButtons = document.querySelectorAll('.password-toggle');
    toggleButtons.forEach(btn => {
        btn.addEventListener('click', handlePasswordToggle);
    });
}

// ===================================================================
// NAVEGACIÓN ENTRE STEPS
// ===================================================================
function handleNextStep(e) {
    e.preventDefault();
    
    console.log('🚀 Botón Continuar presionado');
    console.log('📍 Current Step:', currentStep);
    
    if (validateCurrentStep()) {
        console.log('✅ Validación exitosa, avanzando step');
        saveCurrentStepData();
        
        if (currentStep < SIGNUP_CONFIG.STEPS_TOTAL) {
            currentStep++;
            showStep(currentStep);
            updateStepProgress();
            console.log('🎯 Cambiado a step:', currentStep);
        }
    } else {
        console.log('❌ Validación falló');
        showStepError('Por favor, completa todos los campos requeridos correctamente.');
    }
}

function handlePrevStep(e) {
    e.preventDefault();
    
    if (currentStep > 1) {
        currentStep--;
        showStep(currentStep);
        updateStepProgress();
    }
}

function showStep(step) {
    // Ocultar todos los steps
    const steps = document.querySelectorAll('.form-step');
    steps.forEach((stepEl) => {
        const stepNumber = parseInt(stepEl.getAttribute('data-step'));
        if (stepNumber === step) {
            stepEl.classList.add('active');
            stepEl.style.display = 'block';
        } else {
            stepEl.classList.remove('active');
            stepEl.style.display = 'none';
        }
    });
    
    // Cargar datos en los campos del step actual
    loadStepData();
    
    // Actualizar título del step
    const stepTitle = document.querySelector('.step-title h3');
    const stepSubtitle = document.querySelector('.step-title p');
    
    const stepTitles = {
        1: { title: 'Información Personal', subtitle: 'Ingresa tus datos básicos' },
        2: { title: 'Información de Contacto', subtitle: 'Datos de comunicación y documentación' },
        3: { title: 'Configuración de Seguridad', subtitle: 'Crea una contraseña segura' },
        4: { title: 'Confirmación', subtitle: 'Revisa y confirma tu información' }
    };
    
    if (stepTitle && stepSubtitle && stepTitles[step]) {
        stepTitle.textContent = stepTitles[step].title;
        stepSubtitle.textContent = stepTitles[step].subtitle;
    }
}

function updateStepProgress() {
    const progressSteps = document.querySelectorAll('.progress-step');
    
    progressSteps.forEach((step) => {
        const stepNumber = parseInt(step.getAttribute('data-step'));
        
        if (stepNumber < currentStep) {
            step.classList.add('completed');
            step.classList.remove('active');
        } else if (stepNumber === currentStep) {
            step.classList.add('active');
            step.classList.remove('completed');
        } else {
            step.classList.remove('active', 'completed');
        }
    });
}

// ===================================================================
// VALIDACIONES
// ===================================================================
function validateCurrentStep() {
    let isValid = true;
    
    console.log('🔍 Validando step:', currentStep);
    
    switch (currentStep) {
        case 1:
            isValid = validatePersonalInfo();
            break;
        case 2:
            isValid = validateContactInfo();
            break;
        case 3:
            isValid = validatePasswordInfo();
            break;
        case 4:
            isValid = validateConfirmationInfo();
            break;
    }
    
    console.log('🎯 Validación completa step', currentStep, ':', isValid);
    validationState[`step${currentStep}`] = isValid;
    return isValid;
}

function validatePersonalInfo() {
    const nombresElement = document.getElementById('nombres');
    const apellidosElement = document.getElementById('apellidos');
    
    console.log('🔍 Elementos encontrados:', { 
        nombresElement: nombresElement ? 'ENCONTRADO' : 'NO ENCONTRADO',
        apellidosElement: apellidosElement ? 'ENCONTRADO' : 'NO ENCONTRADO'
    });
    
    if (!nombresElement || !apellidosElement) {
        console.log('❌ ERROR: No se encontraron los elementos del DOM');
        return false;
    }
    
    const nombres = nombresElement.value.trim();
    const apellidos = apellidosElement.value.trim();
    
    console.log('🔍 Debug validación:', { nombres, apellidos });
    
    let isValid = true;
    
    if (nombres.length < 2) {
        console.log('❌ Nombres inválido:', nombres);
        showFieldError('nombres', 'Los nombres deben tener al menos 2 caracteres');
        isValid = false;
    } else {
        console.log('✅ Nombres válido:', nombres);
        clearFieldError('nombres');
    }
    
    if (apellidos.length < 2) {
        console.log('❌ Apellidos inválido:', apellidos);
        showFieldError('apellidos', 'Los apellidos deben tener al menos 2 caracteres');
        isValid = false;
    } else {
        console.log('✅ Apellidos válido:', apellidos);
        clearFieldError('apellidos');
    }
    
    console.log('🎯 Resultado validación paso 1:', isValid);
    return isValid;
}

function validateContactInfo() {
    const email = document.getElementById('email').value.trim();
    const telefono = document.getElementById('telefono').value.trim();
    const tipoDocumento = document.getElementById('tipoDocumento').value;
    const numeroDocumento = document.getElementById('numeroDocumento').value.trim();
    const direccion = document.getElementById('direccion').value.trim();
    
    let isValid = true;
    
    // Validar email
    if (!SIGNUP_CONFIG.EMAIL_PATTERN.test(email)) {
        showFieldError('email', 'Ingresa un email válido');
        isValid = false;
    } else {
        clearFieldError('email');
    }
    
    // Validar teléfono
    const phoneDigits = telefono.replace(/\D/g, '');
    if (!SIGNUP_CONFIG.PHONE_PATTERN.test(phoneDigits)) {
        showFieldError('telefono', 'El teléfono debe tener 10 dígitos');
        isValid = false;
    } else {
        clearFieldError('telefono');
    }
    
    // Validar documento
    if (!tipoDocumento) {
        showFieldError('tipoDocumento', 'Selecciona un tipo de documento');
        isValid = false;
    } else {
        clearFieldError('tipoDocumento');
    }
    
    if (tipoDocumento && numeroDocumento) {
        const pattern = SIGNUP_CONFIG.DOCUMENT_PATTERNS[tipoDocumento];
        if (!pattern.test(numeroDocumento)) {
            showFieldError('numeroDocumento', 'Formato de documento inválido');
            isValid = false;
        } else {
            clearFieldError('numeroDocumento');
        }
    }
    
    // Validar dirección
    if (direccion.length < 10) {
        showFieldError('direccion', 'La dirección debe ser más específica');
        isValid = false;
    } else {
        clearFieldError('direccion');
    }
    
    return isValid;
}

function validateDocumentInfo() {
    const tipoDocumento = document.getElementById('tipoDocumento').value;
    const numeroDocumento = document.getElementById('numeroDocumento').value.trim();
    const direccion = document.getElementById('direccion').value.trim();
    
    let isValid = true;
    
    if (!tipoDocumento) {
        showFieldError('tipoDocumento', 'Selecciona un tipo de documento');
        isValid = false;
    } else {
        clearFieldError('tipoDocumento');
    }
    
    if (tipoDocumento && numeroDocumento) {
        const pattern = SIGNUP_CONFIG.DOCUMENT_PATTERNS[tipoDocumento];
        if (!pattern.test(numeroDocumento)) {
            showFieldError('numeroDocumento', 'Formato de documento inválido');
            isValid = false;
        } else {
            clearFieldError('numeroDocumento');
        }
    }
    
    if (direccion.length < 10) {
        showFieldError('direccion', 'La dirección debe ser más específica');
        isValid = false;
    } else {
        clearFieldError('direccion');
    }
    
    return isValid;
}

function validatePasswordInfo() {
    const passwordElement = document.getElementById('password');
    const confirmPasswordElement = document.getElementById('confirmPassword');
    
    console.log('🔐 Debug contraseña:', {
        passwordElement: passwordElement ? 'ENCONTRADO' : 'NO ENCONTRADO',
        confirmPasswordElement: confirmPasswordElement ? 'ENCONTRADO' : 'NO ENCONTRADO'
    });
    
    if (!passwordElement || !confirmPasswordElement) {
        console.log('❌ ERROR: Elementos de contraseña no encontrados');
        return false;
    }
    
    const password = passwordElement.value;
    const confirmPassword = confirmPasswordElement.value;
    
    console.log('🔐 Valores contraseña:', {
        password: password ? `${password.length} caracteres` : 'VACÍO',
        confirmPassword: confirmPassword ? `${confirmPassword.length} caracteres` : 'VACÍO',
        coinciden: password === confirmPassword
    });
    
    let isValid = true;
    
    if (!isPasswordStrong(password)) {
        console.log('❌ Contraseña no es fuerte');
        showFieldError('password', 'La contraseña no cumple con los requisitos de seguridad');
        isValid = false;
    } else {
        console.log('✅ Contraseña es fuerte');
        clearFieldError('password');
    }
    
    if (password !== confirmPassword) {
        console.log('❌ Contraseñas no coinciden');
        showFieldError('confirmPassword', 'Las contraseñas no coinciden');
        isValid = false;
    } else {
        console.log('✅ Contraseñas coinciden');
        clearFieldError('confirmPassword');
    }
    
    console.log('🎯 Resultado validación contraseña:', isValid);
    return isValid;
}

function validateConfirmationInfo() {
    const acceptTerms = document.getElementById('acceptTerms');
    
    let isValid = true;
    
    if (!acceptTerms || !acceptTerms.checked) {
        showFieldError('acceptTerms', 'Debes aceptar los términos y condiciones');
        isValid = false;
    } else {
        clearFieldError('acceptTerms');
    }
    
    return isValid;
}

// ===================================================================
// MANEJO DE DATOS
// ===================================================================
function saveCurrentStepData() {
    switch (currentStep) {
        case 1:
            formData.nombres = document.getElementById('nombres').value.trim();
            formData.apellidos = document.getElementById('apellidos').value.trim();
            break;
        case 2:
            formData.email = document.getElementById('email').value.trim();
            formData.telefono = document.getElementById('telefono').value.trim();
            formData.tipo_documento = document.getElementById('tipoDocumento').value;
            formData.numero_documento = document.getElementById('numeroDocumento').value.trim();
            formData.direccion = document.getElementById('direccion').value.trim();
            break;
        case 3:
            formData.password = document.getElementById('password').value;
            break;
        case 4:
            // En el paso de confirmación no hay datos nuevos que guardar
            break;
    }
    
    console.log('💾 Datos guardados:', formData);
}

function loadStepData() {
    // Cargar datos guardados en los campos correspondientes
    console.log('📥 Cargando datos:', formData);
    
    const fields = [
        { id: 'nombres', key: 'nombres' },
        { id: 'apellidos', key: 'apellidos' },
        { id: 'email', key: 'email' },
        { id: 'telefono', key: 'telefono' },
        { id: 'tipoDocumento', key: 'tipo_documento' },
        { id: 'numeroDocumento', key: 'numero_documento' },
        { id: 'direccion', key: 'direccion' }
    ];
    
    fields.forEach(field => {
        const element = document.getElementById(field.id);
        if (element && formData[field.key]) {
            element.value = formData[field.key];
            console.log(`✅ Cargado ${field.id}:`, formData[field.key]);
        }
    });
    
    // Si estamos en el paso 4 (confirmación), actualizar el resumen
    if (currentStep === 4) {
        updateConfirmationSummary();
    }
}

function updateConfirmationSummary() {
    console.log('📋 Actualizando resumen de confirmación');
    
    // Actualizar datos personales
    const summaryNombre = document.getElementById('summaryNombre');
    const summaryEmail = document.getElementById('summaryEmail');
    const summaryTelefono = document.getElementById('summaryTelefono');
    const summaryDocumento = document.getElementById('summaryDocumento');
    const summaryDireccion = document.getElementById('summaryDireccion');
    
    if (summaryNombre && formData.nombres && formData.apellidos) {
        summaryNombre.textContent = `${formData.nombres} ${formData.apellidos}`;
    }
    
    if (summaryEmail && formData.email) {
        summaryEmail.textContent = formData.email;
    }
    
    if (summaryTelefono && formData.telefono) {
        summaryTelefono.textContent = formData.telefono;
    }
    
    if (summaryDocumento && formData.tipo_documento && formData.numero_documento) {
        summaryDocumento.textContent = `${formData.tipo_documento}: ${formData.numero_documento}`;
    }
    
    if (summaryDireccion && formData.direccion) {
        summaryDireccion.textContent = formData.direccion;
    }
    
    console.log('✅ Resumen actualizado');
}

// ===================================================================
// MANEJO DE EVENTOS DE INPUTS
// ===================================================================
function handleInputChange(e) {
    const field = e.target;
    
    // Limpiar errores en tiempo real
    if (field.value.trim()) {
        clearFieldError(field.id);
    }
    
    // Validaciones específicas en tiempo real
    switch (field.id) {
        case 'email':
            if (field.value && !SIGNUP_CONFIG.EMAIL_PATTERN.test(field.value)) {
                showFieldError('email', 'Formato de email inválido');
            }
            break;
        case 'telefono':
            const phoneDigits = field.value.replace(/\D/g, '');
            field.value = formatPhoneNumber(phoneDigits);
            if (phoneDigits && !SIGNUP_CONFIG.PHONE_PATTERN.test(phoneDigits)) {
                showFieldError('telefono', 'Debe tener 10 dígitos');
            }
            break;
        case 'numeroDocumento':
            field.value = field.value.replace(/[^0-9A-Z]/g, '');
            break;
        case 'password':
            updatePasswordStrength(field.value);
            break;
        case 'confirmPassword':
            const originalPassword = document.getElementById('password').value;
            if (field.value && field.value !== originalPassword) {
                showFieldError('confirmPassword', 'Las contraseñas no coinciden');
            }
            break;
    }
}

// ===================================================================
// FUNCIONES DE CONTRASEÑA
// ===================================================================
function handlePasswordToggle(e) {
    e.preventDefault();
    const button = e.currentTarget;
    const targetId = button.getAttribute('data-target');
    const passwordField = document.getElementById(targetId);
    const icon = button.querySelector('i');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function updatePasswordStrength(password) {
    const strengthBar = document.querySelector('.strength-fill');
    const strengthText = document.querySelector('.strength-text');
    
    console.log('🔒 Actualizando fortaleza:', { 
        strengthBar: strengthBar ? 'ENCONTRADO' : 'NO ENCONTRADO',
        strengthText: strengthText ? 'ENCONTRADO' : 'NO ENCONTRADO',
        password: password ? 'TIENE VALOR' : 'VACÍO'
    });
    
    if (!strengthBar || !strengthText) {
        console.log('❌ Elementos de fortaleza no encontrados');
        return;
    }
    
    const score = calculatePasswordStrength(password);
    const strength = ['muy débil', 'débil', 'aceptable', 'buena', 'muy fuerte'][score];
    const colors = ['#ff4757', '#ff6348', '#ffa502', '#2ed573', '#1dd1a1'];
    
    strengthBar.style.width = (score + 1) * 20 + '%';
    strengthBar.style.backgroundColor = colors[score];
    strengthText.textContent = `Fortaleza: ${strength}`;
    
    console.log('✅ Fortaleza actualizada:', { score, strength, width: (score + 1) * 20 + '%' });
}

function calculatePasswordStrength(password) {
    let score = 0;
    
    if (password.length >= 8) score++;
    if (password.match(/[a-z]/)) score++;
    if (password.match(/[A-Z]/)) score++;
    if (password.match(/[0-9]/)) score++;
    if (password.match(/[^a-zA-Z0-9]/)) score++;
    
    return Math.min(score, 4);
}

function isPasswordStrong(password) {
    const hasMinLength = password.length >= SIGNUP_CONFIG.PASSWORD_MIN_LENGTH;
    const hasLowerCase = password.match(/[a-z]/);
    const hasUpperCase = password.match(/[A-Z]/);
    const hasNumber = password.match(/[0-9]/);
    const hasSpecialChar = password.match(/[^a-zA-Z0-9]/);
    
    console.log('🔐 Análisis fortaleza:', {
        password: password ? `${password.length} chars` : 'VACÍO',
        hasMinLength: hasMinLength,
        hasLowerCase: !!hasLowerCase,
        hasUpperCase: !!hasUpperCase,
        hasNumber: !!hasNumber,
        hasSpecialChar: !!hasSpecialChar,
        requiredLength: SIGNUP_CONFIG.PASSWORD_MIN_LENGTH
    });
    
    const isStrong = hasMinLength && hasLowerCase && hasUpperCase && hasNumber && hasSpecialChar;
    console.log('🎯 Contraseña fuerte:', isStrong);
    
    return isStrong;
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
        
    const apiUrl = `${SIGNUP_CONFIG.API_BASE_URL}/register`;
    console.log('🌐 URL de la API (REGISTER):', apiUrl);
        
        // Enviar a la API
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(registrationData)
        });
        
        console.log('📡 Respuesta del servidor:', {
            status: response.status,
            statusText: response.statusText,
            headers: response.headers.get('content-type')
        });
        
        // Leer como texto y limpiar posibles BOM/espacios antes de parsear
        const rawText = await response.text();
        const cleaned = rawText.replace(/^\uFEFF/, '').trim();
        let result;
        try {
            result = cleaned ? JSON.parse(cleaned) : {};
            console.log('📦 Respuesta JSON (limpia):', result);
        } catch (parseErr) {
            console.error('❌ Error parseando JSON:', parseErr, 'RAW:', cleaned.substring(0, 300));
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
// UTILIDADES DE FORMATEO
// ===================================================================
function formatPhoneNumber(phone) {
    const digits = phone.replace(/\D/g, '');
    
    if (digits.length <= 3) return digits;
    if (digits.length <= 6) return digits.slice(0, 3) + '-' + digits.slice(3);
    return digits.slice(0, 3) + '-' + digits.slice(3, 6) + '-' + digits.slice(6, 10);
}

function sanitizeInput(input) {
    return input.trim().replace(/<script[^>]*>.*?<\/script>/gi, '');
}

// ===================================================================
// MANEJO DE ERRORES Y MENSAJES
// ===================================================================
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    const formGroup = field.closest('.form-group') || field.closest('.mb-3');
    if (!formGroup) return;
    
    const existingError = formGroup.querySelector('.invalid-feedback');
    
    // Añadir clase de error al campo
    field.classList.add('is-invalid');
    
    // Mostrar mensaje de error
    if (existingError) {
        existingError.textContent = message;
    } else {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        formGroup.appendChild(errorDiv);
    }
}

function clearFieldError(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    const formGroup = field.closest('.form-group') || field.closest('.mb-3');
    if (!formGroup) return;
    
    const errorElement = formGroup.querySelector('.invalid-feedback');
    
    field.classList.remove('is-invalid');
    if (errorElement) {
        errorElement.remove();
    }
}

function showStepError(message) {
    const alertContainer = document.querySelector('.form-step.active');
    if (!alertContainer) return;
    
    // Remover alertas existentes
    const existingAlerts = alertContainer.querySelectorAll('.alert-danger');
    existingAlerts.forEach(alert => alert.remove());
    
    // Crear nueva alerta
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show mt-3';
    alertDiv.innerHTML = `
        <i class="fas fa-exclamation-triangle me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.insertBefore(alertDiv, alertContainer.firstChild);
    
    // Auto-ocultar después de 5 segundos
    setTimeout(() => {
        if (alertDiv && alertDiv.parentNode) {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }
    }, 5000);
}

function showSuccessMessage(message) {
    const alertContainer = document.querySelector('.form-step.active') || document.querySelector('.signup-container');
    if (!alertContainer) return;
    
    // Crear alerta de éxito
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show mt-3';
    alertDiv.innerHTML = `
        <i class="fas fa-check-circle me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.insertBefore(alertDiv, alertContainer.firstChild);
}

// ===================================================================
// FUNCIONES DE DEBUG Y UTILIDADES GLOBALES
// ===================================================================
window.SignupDebug = {
    getCurrentStep: () => currentStep,
    getFormData: () => formData,
    getValidationState: () => validationState,
    validateCurrentStep: validateCurrentStep,
    testValidation: () => {
        console.log('🧪 TEST DE VALIDACIÓN:');
        const nombres = document.getElementById('nombres');
        const apellidos = document.getElementById('apellidos');
        
        console.log('Elementos encontrados:', {
            nombres: nombres ? nombres.value : 'NO ENCONTRADO',
            apellidos: apellidos ? apellidos.value : 'NO ENCONTRADO'
        });
        
        const resultado = validateCurrentStep();
        console.log('Resultado:', resultado);
        return resultado;
    },
    CONFIG: SIGNUP_CONFIG
};

console.log('✅ Sistema de registro cargado correctamente');
