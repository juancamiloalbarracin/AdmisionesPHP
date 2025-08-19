// Test básico para verificar errores
console.log('✅ JavaScript se cargó correctamente');

// Agregar al botón de submit un listener básico
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ DOM cargado');
    
    const form = document.getElementById('signupForm');
    if (form) {
        console.log('✅ Formulario encontrado');
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('✅ Submit interceptado');
            alert('Formulario funcionando!');
        });
    } else {
        console.log('❌ Formulario no encontrado');
    }
});
