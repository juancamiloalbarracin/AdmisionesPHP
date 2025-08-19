    </main>

    <!-- Footer -->
    <footer class="footer-container">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="footer-section">
                        <h5>Uniminuto</h5>
                        <p class="text-muted">
                            Institución de Educación Superior comprometida con la formación integral 
                            y el desarrollo regional de Colombia.
                        </p>
                        <div class="social-links">
                            <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-section">
                        <h6>Enlaces Rápidos</h6>
                        <ul class="footer-links">
                            <li><a href="https://www.uniminuto.edu" target="_blank">Sitio Web Oficial</a></li>
                            <li><a href="https://www.uniminuto.edu/admisiones" target="_blank">Admisiones</a></li>
                            <li><a href="https://www.uniminuto.edu/programas" target="_blank">Programas</a></li>
                            <li><a href="https://www.uniminuto.edu/investigacion" target="_blank">Investigación</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="footer-section">
                        <h6>Contacto</h6>
                        <ul class="contact-info">
                            <li>
                                <i class="fas fa-map-marker-alt"></i>
                                Carrera 6 No. 77-305<br>
                                Montería, Córdoba, Colombia
                            </li>
                            <li>
                                <i class="fas fa-phone"></i>
                                +57 (4) 786 0300
                            </li>
                            <li>
                                <i class="fas fa-envelope"></i>
                                admisiones@uniminuto.edu.co
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="footer-section">
                        <h6>Horarios de Atención</h6>
                        <ul class="schedule-info">
                            <li>
                                <strong>Lunes a Viernes:</strong><br>
                                8:00 AM - 12:00 PM<br>
                                2:00 PM - 6:00 PM
                            </li>
                            <li>
                                <strong>Sábados:</strong><br>
                                8:00 AM - 12:00 PM
                            </li>
                            <li>
                                <em>Domingos y festivos cerrado</em>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <hr class="footer-divider">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="copyright-text">
                        &copy; <?php echo date('Y'); ?> Uniminuto. Todos los derechos reservados.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="footer-links-inline">
                        <a href="#" class="footer-link-inline">Términos de Uso</a>
                        <a href="#" class="footer-link-inline">Política de Privacidad</a>
                        <a href="#" class="footer-link-inline">Ayuda</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript Principal -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/app.js"></script>
    
    <!-- JavaScript adicional específico de página -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script>
        // Inicializar aplicación cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar la aplicación
            if (typeof AdmisionesApp !== 'undefined') {
                const app = new AdmisionesApp({
                    apiUrl: '<?php echo $apiUrl; ?>',
                    baseUrl: '<?php echo $baseUrl; ?>',
                    isAuthenticated: <?php echo $isAuthenticated ? 'true' : 'false'; ?>,
                    currentUser: <?php echo json_encode($currentUser); ?>,
                    currentPage: '<?php echo $currentPage; ?>'
                });
                
                // Inicializar la aplicación
                app.init();
                
                // Hacer la app disponible globalmente
                window.app = app;
            }
            
            // Manejar flash messages
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(function(message) {
                const type = message.dataset.type || 'info';
                message.className = `alert alert-${type === 'danger' ? 'danger' : type === 'success' ? 'success' : 'info'} alert-dismissible fade show`;
                
                // Auto-dismiss después de 5 segundos
                setTimeout(function() {
                    if (message.parentNode) {
                        message.remove();
                    }
                }, 5000);
            });
            
            // Manejar logout
            const logoutLinks = document.querySelectorAll('.logout-link');
            logoutLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (window.app && typeof window.app.logout === 'function') {
                        window.app.logout();
                    } else {
                        window.location.href = 'logout.php';
                    }
                });
            });
            
            // Mejorar experiencia de usuario
            setupUIEnhancements();
        });
        
        // Funciones para mejorar la experiencia de usuario
        function setupUIEnhancements() {
            // Loading states para formularios
            const forms = document.querySelectorAll('form');
            forms.forEach(function(form) {
                form.addEventListener('submit', function() {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                    }
                });
            });
            
            // Tooltips para elementos con data-bs-toggle="tooltip"
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Confirmación para acciones destructivas
            const dangerousLinks = document.querySelectorAll('[data-confirm]');
            dangerousLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    const message = this.dataset.confirm || '¿Estás seguro de que deseas continuar?';
                    if (!confirm(message)) {
                        e.preventDefault();
                    }
                });
            });
        }
        
        // Utility functions
        function showAlert(message, type = 'info') {
            const alertContainer = document.querySelector('.alert-container');
            if (alertContainer) {
                const alertElement = document.createElement('div');
                alertElement.className = `alert alert-${type} alert-dismissible fade show`;
                alertElement.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                alertContainer.appendChild(alertElement);
                
                // Auto-dismiss
                setTimeout(() => {
                    if (alertElement.parentNode) {
                        alertElement.remove();
                    }
                }, 5000);
            }
        }
        
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-CO', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
        
        function formatDateTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('es-CO', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    </script>
    
    <style>
        /* Estilos del footer */
        .footer-container {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #ffffff;
            padding: 3rem 0 1rem;
            margin-top: auto;
        }
        
        .footer-section h5,
        .footer-section h6 {
            color: #ffffff;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 0.5rem;
        }
        
        .footer-links a {
            color: #cccccc;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: #ffffff;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .social-link:hover {
            background: #2980b9;
            color: #ffffff;
            transform: translateY(-2px);
        }
        
        .contact-info,
        .schedule-info {
            list-style: none;
            padding: 0;
        }
        
        .contact-info li,
        .schedule-info li {
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            color: #cccccc;
        }
        
        .contact-info i {
            color: #2980b9;
            width: 20px;
            margin-top: 2px;
        }
        
        .footer-divider {
            border-color: rgba(255, 255, 255, 0.1);
            margin: 2rem 0 1rem;
        }
        
        .copyright-text {
            color: #cccccc;
            margin: 0;
        }
        
        .footer-links-inline {
            display: flex;
            gap: 2rem;
            justify-content: end;
        }
        
        .footer-link-inline {
            color: #cccccc;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        
        .footer-link-inline:hover {
            color: #ffffff;
        }
        
        @media (max-width: 768px) {
            .footer-links-inline {
                justify-content: center;
                margin-top: 1rem;
            }
            
            .social-links {
                justify-content: center;
            }
        }
        
        /* Estilos adicionales para el layout */
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .main-content {
            flex: 1;
            padding: 2rem 0;
        }
        
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            max-width: 400px;
        }
        
        .flash-message {
            margin-bottom: 1rem;
        }
    </style>
</body>
</html>
