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
    
    // Test básico de submit
    const form = document.getElementById('signupForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('✅ Formulario enviado - TEST OK');
            alert('Formulario funcionando (versión simplificada)');
        });
    }
    
    console.log('✅ Inicialización completa');
});
