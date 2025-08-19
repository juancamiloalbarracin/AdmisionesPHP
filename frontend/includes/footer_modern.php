<?php
/**
 * ===================================================================
 * UNIMINUTO FOOTER MODERN - FOOTER ELEGANTE CON DISEÑO INSTITUCIONAL
 * ===================================================================
 * Archivo: footer_modern.php
 * Descripción: Footer moderno con información institucional y enlaces
 * Fecha: 2025
 */

// Configuración
$currentYear = date('Y');
$footerVersion = '2.1.0';
?>
    </main>
    
    <!-- Modern Footer -->
    <footer class="modern-footer" id="main-footer">
        <!-- Footer Content -->
        <div class="footer-content">
            <div class="footer-container">
                <!-- Institutional Section -->
                <div class="footer-section institutional">
                    <div class="footer-brand">
                        <div class="footer-logo">
                            <div class="logo-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="logo-animation-bg"></div>
                        </div>
                        <div class="footer-brand-text">
                            <h3>Uniminuto</h3>
                            <p>Corporación Universitaria Minuto de Dios</p>
                            <span class="brand-motto">Educación para el Desarrollo</span>
                        </div>
                    </div>
                    
                    <div class="footer-description">
                        <p>Sistema integral de admisiones diseñado para facilitar tu ingreso a la educación superior. Transformamos vidas a través de la educación de calidad.</p>
                    </div>
                    
                    <div class="footer-certifications">
                        <div class="certification-item">
                            <i class="fas fa-award"></i>
                            <span>Acreditación Institucional</span>
                        </div>
                        <div class="certification-item">
                            <i class="fas fa-medal"></i>
                            <span>Calidad Educativa</span>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="footer-section links">
                    <h4>Enlaces Rápidos</h4>
                    <ul class="footer-links">
                        <li><a href="main_modern.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="#"><i class="fas fa-file-alt"></i> Nueva Solicitud</a></li>
                        <li><a href="#"><i class="fas fa-folder-open"></i> Mis Documentos</a></li>
                        <li><a href="#"><i class="fas fa-user-circle"></i> Mi Perfil</a></li>
                        <li><a href="#"><i class="fas fa-calendar-alt"></i> Agendar Cita</a></li>
                        <li><a href="#"><i class="fas fa-headset"></i> Centro de Ayuda</a></li>
                    </ul>
                </div>
                
                <!-- Academic Programs -->
                <div class="footer-section programs">
                    <h4>Programas Académicos</h4>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-laptop-code"></i> Ingeniería de Sistemas</a></li>
                        <li><a href="#"><i class="fas fa-building"></i> Administración de Empresas</a></li>
                        <li><a href="#"><i class="fas fa-balance-scale"></i> Derecho</a></li>
                        <li><a href="#"><i class="fas fa-stethoscope"></i> Psicología</a></li>
                        <li><a href="#"><i class="fas fa-chalkboard-teacher"></i> Educación</a></li>
                        <li><a href="#"><i class="fas fa-plus"></i> Ver Todos los Programas</a></li>
                    </ul>
                </div>
                
                <!-- Contact & Support -->
                <div class="footer-section contact">
                    <h4>Contacto y Soporte</h4>
                    
                    <div class="contact-info">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div class="contact-details">
                                <span class="contact-label">Línea de Atención</span>
                                <span class="contact-value">(+57) 1 291 6520</span>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-details">
                                <span class="contact-label">Email Admisiones</span>
                                <span class="contact-value">admisiones@uniminuto.edu</span>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="contact-details">
                                <span class="contact-label">Horario de Atención</span>
                                <span class="contact-value">Lun - Vie: 8:00 AM - 6:00 PM</span>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-details">
                                <span class="contact-label">Sede Principal</span>
                                <span class="contact-value">Bogotá D.C., Colombia</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="footer-social">
                        <h5>Síguenos</h5>
                        <div class="social-links">
                            <a href="#" class="social-link facebook" aria-label="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-link twitter" aria-label="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-link instagram" aria-label="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="social-link linkedin" aria-label="LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="#" class="social-link youtube" aria-label="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer Actions Bar -->
        <div class="footer-actions">
            <div class="footer-container">
                <div class="footer-actions-content">
                    <div class="help-center">
                        <button class="help-trigger" id="help-trigger">
                            <i class="fas fa-question-circle"></i>
                            <span>Centro de Ayuda</span>
                        </button>
                        
                        <div class="help-panel" id="help-panel">
                            <div class="help-header">
                                <h6>¿En qué te podemos ayudar?</h6>
                                <button class="help-close" id="help-close">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="help-content">
                                <div class="help-item">
                                    <div class="help-icon">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div class="help-text">
                                        <span class="help-title">Solicitud de Admisión</span>
                                        <span class="help-desc">Aprende cómo completar tu solicitud</span>
                                    </div>
                                </div>
                                <div class="help-item">
                                    <div class="help-icon">
                                        <i class="fas fa-upload"></i>
                                    </div>
                                    <div class="help-text">
                                        <span class="help-title">Subir Documentos</span>
                                        <span class="help-desc">Guía para cargar documentos</span>
                                    </div>
                                </div>
                                <div class="help-item">
                                    <div class="help-icon">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                    <div class="help-text">
                                        <span class="help-title">Agendar Cita</span>
                                        <span class="help-desc">Programa tu entrevista</span>
                                    </div>
                                </div>
                                <div class="help-actions">
                                    <a href="#" class="btn-help-chat">
                                        <i class="fas fa-comments"></i>
                                        Chat en Vivo
                                    </a>
                                    <a href="#" class="btn-help-faq">
                                        <i class="fas fa-book-open"></i>
                                        FAQ
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="quick-actions">
                        <button class="quick-action-btn" title="Nueva Solicitud">
                            <i class="fas fa-plus"></i>
                            <span>Nueva Solicitud</span>
                        </button>
                        
                        <button class="quick-action-btn" title="Subir Documento">
                            <i class="fas fa-upload"></i>
                            <span>Subir Documento</span>
                        </button>
                        
                        <button class="quick-action-btn" title="Estado de Solicitud">
                            <i class="fas fa-search"></i>
                            <span>Consultar Estado</span>
                        </button>
                    </div>
                    
                    <div class="footer-tools">
                        <button class="scroll-to-top" id="scroll-to-top" title="Volver al inicio">
                            <i class="fas fa-chevron-up"></i>
                        </button>
                        
                        <div class="theme-selector">
                            <button class="theme-toggle" id="theme-toggle" title="Cambiar tema">
                                <i class="fas fa-moon"></i>
                            </button>
                        </div>
                        
                        <div class="language-selector">
                            <button class="language-toggle" title="Cambiar idioma">
                                <i class="fas fa-globe"></i>
                                <span>ES</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="footer-container">
                <div class="footer-bottom-content">
                    <div class="copyright">
                        <p>&copy; <?php echo $currentYear; ?> Corporación Universitaria Minuto de Dios - UNIMINUTO. Todos los derechos reservados.</p>
                        <div class="footer-meta">
                            <span class="footer-version">Sistema v<?php echo $footerVersion; ?></span>
                            <span class="footer-separator">•</span>
                            <span class="footer-update">Última actualización: <?php echo date('d/m/Y'); ?></span>
                        </div>
                    </div>
                    
                    <div class="footer-legal">
                        <div class="legal-links">
                            <a href="#" class="legal-link">Términos de Uso</a>
                            <a href="#" class="legal-link">Política de Privacidad</a>
                            <a href="#" class="legal-link">Política de Cookies</a>
                            <a href="#" class="legal-link">PQRS</a>
                            <a href="#" class="legal-link">Mapa del Sitio</a>
                        </div>
                        
                        <div class="security-badges">
                            <div class="security-badge" title="Conexión Segura">
                                <i class="fas fa-lock"></i>
                                <span>SSL</span>
                            </div>
                            <div class="security-badge" title="Datos Protegidos">
                                <i class="fas fa-shield-alt"></i>
                                <span>GDPR</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Chat Widget -->
    <div class="chat-widget" id="chat-widget">
        <div class="chat-trigger" id="chat-trigger">
            <div class="chat-icon">
                <i class="fas fa-comments"></i>
            </div>
            <div class="chat-pulse"></div>
            <div class="chat-notification" id="chat-notification">1</div>
        </div>
        
        <div class="chat-panel" id="chat-panel">
            <div class="chat-header">
                <div class="chat-agent">
                    <div class="agent-avatar">
                        <img src="https://ui-avatars.com/api/?name=Soporte&background=FF8C00&color=fff&size=32" alt="Soporte">
                        <div class="agent-status online"></div>
                    </div>
                    <div class="agent-info">
                        <span class="agent-name">Soporte Uniminuto</span>
                        <span class="agent-status-text">En línea</span>
                    </div>
                </div>
                <button class="chat-close" id="chat-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="chat-messages" id="chat-messages">
                <div class="chat-message agent">
                    <div class="message-avatar">
                        <img src="https://ui-avatars.com/api/?name=Bot&background=FF8C00&color=fff&size=24" alt="Bot">
                    </div>
                    <div class="message-content">
                        <div class="message-text">¡Hola! 👋 Soy el asistente virtual de Uniminuto. ¿En qué puedo ayudarte hoy?</div>
                        <div class="message-time">Ahora mismo</div>
                    </div>
                </div>
            </div>
            
            <div class="chat-input-area">
                <div class="quick-replies">
                    <button class="quick-reply">Nueva solicitud</button>
                    <button class="quick-reply">Estado de aplicación</button>
                    <button class="quick-reply">Documentos requeridos</button>
                </div>
                <div class="chat-input-wrapper">
                    <input type="text" class="chat-input" placeholder="Escribe tu mensaje..." id="chat-input">
                    <button class="chat-send" id="chat-send">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Back to Top Button (Alternative) -->
    <button class="back-to-top" id="back-to-top-alt" title="Volver arriba">
        <i class="fas fa-chevron-up"></i>
        <span class="back-to-top-text">Top</span>
    </button>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="../assets/js/navigation_modern.js"></script>
    <script src="../assets/js/footer_modern.js"></script>
    
    <?php if (!empty($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script>
        // Initialize AOS animations
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 800,
                easing: 'ease-out-cubic',
                once: true,
                offset: 100
            });
        }
        
        // Page-specific initialization
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🎯 Page loaded - Uniminuto Modern System');
            
            // Initialize tooltips
            if (typeof bootstrap !== 'undefined') {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl, {
                        placement: 'top',
                        trigger: 'hover'
                    });
                });
            }
        });
    </script>
</body>
</html>
