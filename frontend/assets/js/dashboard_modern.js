/**
 * ===================================================================
 * UNIMINUTO DASHBOARD MODERN 3D - FUNCIONALIDAD INTERACTIVA
 * ===================================================================
 * Archivo: dashboard_modern.js
 * Descripción: JavaScript para dashboard con efectos 3D y gráficos
 * Fecha: 2025
 */

// ===================================================================
// CONFIGURACIÓN GLOBAL Y VARIABLES
// ===================================================================
const DASHBOARD_CONFIG = {
    API_BASE_URL: 'http://localhost:8000/api',
    CHART_COLORS: {
        primary: '#FF8C00',
        primaryLight: '#FFB347',
        secondary: '#FFA500',
        success: '#10B981',
        warning: '#F59E0B',
        danger: '#EF4444',
        info: '#3B82F6',
        dark: '#1F2937',
        light: '#F9FAFB'
    },
    ANIMATION_DURATION: 300,
    UPDATE_INTERVAL: 30000 // 30 segundos
};

let charts = {};
let updateTimer;
let statsAnimated = false;

// ===================================================================
// INICIALIZACIÓN AL CARGAR EL DOM
// ===================================================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando Dashboard Moderno Uniminuto...');
    
    // Inicializar componentes
    initializeParticleBackground();
    initializeNavigation();
    initializeCharts();
    initializeCounters();
    initializeProgressBars();
    initializeInteractions();
    
    // Inicializar AOS (Animate On Scroll)
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-out-cubic',
            once: true,
            offset: 100
        });
    }
    
    // Auto-actualización
    startAutoUpdate();
    
    console.log('✅ Dashboard inicializado correctamente');
});

// ===================================================================
// FONDO DE PARTÍCULAS INTERACTIVO
// ===================================================================
function initializeParticleBackground() {
    const particlesContainer = document.getElementById('particles-bg');
    if (!particlesContainer) return;
    
    // Crear partículas flotantes
    for (let i = 0; i < 50; i++) {
        createParticle(particlesContainer);
    }
    
    // Efecto de mouse
    document.addEventListener('mousemove', handleMouseMove);
}

function createParticle(container) {
    const particle = document.createElement('div');
    particle.className = 'floating-particle';
    
    // Posición aleatoria
    const x = Math.random() * window.innerWidth;
    const y = Math.random() * window.innerHeight;
    const size = Math.random() * 4 + 2;
    const opacity = Math.random() * 0.3 + 0.1;
    
    particle.style.cssText = `
        position: absolute;
        left: ${x}px;
        top: ${y}px;
        width: ${size}px;
        height: ${size}px;
        background: radial-gradient(circle, #FF8C00, #FFB347);
        border-radius: 50%;
        opacity: ${opacity};
        pointer-events: none;
        animation: particleFloat ${Math.random() * 20 + 10}s infinite linear;
    `;
    
    container.appendChild(particle);
    
    // Auto-destruir después de la animación
    setTimeout(() => {
        if (particle.parentNode) {
            particle.remove();
            createParticle(container); // Crear nueva partícula
        }
    }, (Math.random() * 20 + 10) * 1000);
}

function handleMouseMove(e) {
    const mouseX = e.clientX / window.innerWidth;
    const mouseY = e.clientY / window.innerHeight;
    
    // Mover fondo de partículas
    const particles = document.getElementById('particles-bg');
    if (particles) {
        particles.style.transform = `translate(${mouseX * 10}px, ${mouseY * 10}px)`;
    }
}

// ===================================================================
// NAVEGACIÓN INTERACTIVA
// ===================================================================
function initializeNavigation() {
    // Búsqueda inteligente
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(e.target.value);
            }, 300);
        });
    }
    
    // Notificaciones
    initializeNotifications();
    
    // Menú de usuario
    initializeUserMenu();
    
    // Efectos de scroll en navbar
    let lastScroll = 0;
    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;
        const navbar = document.querySelector('.navbar-modern');
        
        if (navbar) {
            if (currentScroll > lastScroll && currentScroll > 100) {
                navbar.style.transform = 'translateY(-100%)';
            } else {
                navbar.style.transform = 'translateY(0)';
            }
        }
        
        lastScroll = currentScroll;
    });
}

function performSearch(query) {
    if (query.length < 2) return;
    
    console.log('🔍 Buscando:', query);
    // Aquí iría la lógica de búsqueda real
    // Simulamos resultados
    const suggestions = document.querySelector('.search-suggestions');
    if (suggestions) {
        suggestions.innerHTML = `
            <div class="suggestion-item">
                <i class="fas fa-search"></i>
                <span>Resultados para "${query}"</span>
            </div>
        `;
        suggestions.style.display = 'block';
    }
}

function initializeNotifications() {
    const notificationBtn = document.querySelector('.notification-btn');
    if (notificationBtn) {
        // Simular notificaciones en tiempo real
        setInterval(() => {
            const currentCount = parseInt(notificationBtn.getAttribute('data-count'));
            if (Math.random() < 0.1) { // 10% probabilidad cada intervalo
                notificationBtn.setAttribute('data-count', currentCount + 1);
                showNotificationToast('Nueva notificación recibida');
            }
        }, 10000);
    }
}

function initializeUserMenu() {
    const userBtn = document.querySelector('.user-btn');
    const userPanel = document.querySelector('.user-panel');
    
    if (userBtn && userPanel) {
        let isOpen = false;
        
        userBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            isOpen = !isOpen;
            userPanel.style.opacity = isOpen ? '1' : '0';
            userPanel.style.visibility = isOpen ? 'visible' : 'hidden';
            userPanel.style.transform = isOpen ? 'translateY(0)' : 'translateY(-10px)';
        });
        
        document.addEventListener('click', () => {
            if (isOpen) {
                isOpen = false;
                userPanel.style.opacity = '0';
                userPanel.style.visibility = 'hidden';
                userPanel.style.transform = 'translateY(-10px)';
            }
        });
    }
}

// ===================================================================
// GRÁFICOS Y VISUALIZACIONES
// ===================================================================
function initializeCharts() {
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js no está disponible');
        return;
    }
    
    // Configuración global de Chart.js
    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.color = '#E5E7EB';
    Chart.defaults.backgroundColor = 'rgba(255, 140, 0, 0.1)';
    
    // Gráfico principal de analytics
    initializeMainChart();
    
    // Gráficos de estadísticas
    initializeStatsCharts();
    
    // Gráfico de distribución de programas
    initializeProgramsChart();
}

function initializeMainChart() {
    const ctx = document.getElementById('main-analytics-chart');
    if (!ctx) return;
    
    const data = window.DASHBOARD_DATA?.chartData?.monthly_stats || 
                 [120, 135, 155, 142, 168, 195, 210, 189, 225, 245, 267, 280];
    
    charts.main = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 
                    'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            datasets: [{
                label: 'Solicitudes',
                data: data,
                borderColor: DASHBOARD_CONFIG.CHART_COLORS.primary,
                backgroundColor: createGradient(ctx, [
                    {offset: 0, color: 'rgba(255, 140, 0, 0.3)'},
                    {offset: 1, color: 'rgba(255, 140, 0, 0.05)'}
                ]),
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: DASHBOARD_CONFIG.CHART_COLORS.primary,
                pointBorderColor: '#FFFFFF',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointHoverBackgroundColor: DASHBOARD_CONFIG.CHART_COLORS.primaryLight,
                pointHoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 15, 35, 0.95)',
                    titleColor: '#FFFFFF',
                    bodyColor: '#E5E7EB',
                    borderColor: DASHBOARD_CONFIG.CHART_COLORS.primary,
                    borderWidth: 1,
                    cornerRadius: 12,
                    displayColors: false,
                    titleFont: {
                        size: 14,
                        weight: 600
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 12
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    border: {
                        display: false
                    },
                    ticks: {
                        color: '#9CA3AF',
                        font: {
                            size: 12,
                            weight: 500
                        }
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)',
                        borderDash: [5, 5]
                    },
                    border: {
                        display: false
                    },
                    ticks: {
                        color: '#9CA3AF',
                        font: {
                            size: 12,
                            weight: 500
                        },
                        padding: 10
                    }
                }
            },
            elements: {
                point: {
                    hoverRadius: 10
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeOutQuart'
            }
        }
    });
    
    // Tabs de período
    const chartTabs = document.querySelectorAll('.chart-tab');
    chartTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            chartTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            const period = tab.getAttribute('data-period');
            updateMainChart(period);
        });
    });
}

function initializeStatsCharts() {
    // Pequeños gráficos en las tarjetas de estadísticas
    const chartElements = ['chart-total', 'chart-pending', 'chart-approved', 'chart-rejected'];
    
    chartElements.forEach((id, index) => {
        const ctx = document.getElementById(id);
        if (!ctx) return;
        
        const colors = [
            DASHBOARD_CONFIG.CHART_COLORS.primary,
            DASHBOARD_CONFIG.CHART_COLORS.warning,
            DASHBOARD_CONFIG.CHART_COLORS.success,
            DASHBOARD_CONFIG.CHART_COLORS.danger
        ];
        
        charts[id] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['1', '2', '3', '4', '5', '6', '7'],
                datasets: [{
                    data: generateRandomData(7),
                    borderColor: colors[index],
                    backgroundColor: colors[index] + '20',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                },
                scales: {
                    x: { display: false },
                    y: { display: false }
                },
                elements: {
                    point: { radius: 0 }
                },
                animation: {
                    duration: 1500,
                    delay: index * 200
                }
            }
        });
    });
}

function initializeProgramsChart() {
    const ctx = document.getElementById('programs-chart');
    if (!ctx) return;
    
    const programsData = window.DASHBOARD_DATA?.chartData?.program_distribution || [
        {name: 'Ingeniería', value: 35, color: '#FF8C00'},
        {name: 'Administración', value: 28, color: '#FFB347'},
        {name: 'Salud', value: 22, color: '#FFA500'},
        {name: 'Educación', value: 15, color: '#FF7F50'}
    ];
    
    charts.programs = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: programsData.map(p => p.name),
            datasets: [{
                data: programsData.map(p => p.value),
                backgroundColor: programsData.map(p => p.color),
                borderColor: '#0F0F23',
                borderWidth: 3,
                hoverBorderWidth: 4,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 15, 35, 0.95)',
                    titleColor: '#FFFFFF',
                    bodyColor: '#E5E7EB',
                    borderColor: DASHBOARD_CONFIG.CHART_COLORS.primary,
                    borderWidth: 1,
                    cornerRadius: 12,
                    displayColors: true,
                    titleFont: { size: 14, weight: 600 },
                    bodyFont: { size: 13 },
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + '%';
                        }
                    }
                }
            },
            animation: {
                animateRotate: true,
                duration: 2000,
                easing: 'easeOutBounce'
            }
        }
    });
}

// ===================================================================
// CONTADORES ANIMADOS
// ===================================================================
function initializeCounters() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !statsAnimated) {
                animateCounters();
                statsAnimated = true;
            }
        });
    }, { threshold: 0.5 });
    
    const statsSection = document.querySelector('.stats-section');
    if (statsSection) {
        observer.observe(statsSection);
    }
}

function animateCounters() {
    const counters = document.querySelectorAll('.stat-number[data-count]');
    
    counters.forEach((counter, index) => {
        const target = parseInt(counter.getAttribute('data-count'));
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;
        
        setTimeout(() => {
            const timer = setInterval(() => {
                current += step;
                counter.textContent = Math.floor(current);
                
                if (current >= target) {
                    counter.textContent = target;
                    clearInterval(timer);
                    
                    // Efecto de bounce al finalizar
                    counter.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        counter.style.transform = 'scale(1)';
                    }, 200);
                }
            }, 16);
        }, index * 200);
    });
}

// ===================================================================
// BARRAS DE PROGRESO ANIMADAS
// ===================================================================
function initializeProgressBars() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const progressBar = entry.target.querySelector('.progress-fill');
                if (progressBar && !progressBar.classList.contains('animated')) {
                    const percentage = entry.target.getAttribute('data-percentage');
                    animateProgressBar(progressBar, percentage);
                    progressBar.classList.add('animated');
                }
            }
        });
    }, { threshold: 0.5 });
    
    document.querySelectorAll('.progress-bar').forEach(bar => {
        observer.observe(bar);
    });
}

function animateProgressBar(element, targetPercentage) {
    let currentPercentage = 0;
    const increment = targetPercentage / 100;
    
    const animation = setInterval(() => {
        currentPercentage += increment;
        element.style.width = currentPercentage + '%';
        
        if (currentPercentage >= targetPercentage) {
            element.style.width = targetPercentage + '%';
            clearInterval(animation);
        }
    }, 20);
}

// ===================================================================
// INTERACCIONES Y EFECTOS
// ===================================================================
function initializeInteractions() {
    // Acciones rápidas
    document.querySelectorAll('.action-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.getAttribute('data-action');
            handleQuickAction(action);
            
            // Efecto visual
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });
    
    // Efectos hover en cards
    document.querySelectorAll('.dashboard-card, .stat-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
    
    // Parallax effect en scroll
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const rate = scrolled * -0.5;
        
        const hero = document.querySelector('.hero-section');
        if (hero) {
            hero.style.transform = `translateY(${rate}px)`;
        }
    });
}

function handleQuickAction(action) {
    console.log('🎯 Acción rápida:', action);
    
    switch (action) {
        case 'new-application':
            showNotificationToast('Redirigiendo a nueva solicitud...');
            // window.location.href = 'nueva-solicitud.php';
            break;
        case 'view-documents':
            showNotificationToast('Cargando documentos...');
            break;
        case 'schedule-meeting':
            showNotificationToast('Abriendo calendario...');
            break;
        case 'contact-support':
            showNotificationToast('Conectando con soporte...');
            break;
        default:
            console.log('Acción no reconocida:', action);
    }
}

// ===================================================================
// NOTIFICACIONES TOAST
// ===================================================================
function showNotificationToast(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    
    const icons = {
        success: 'fas fa-check-circle',
        warning: 'fas fa-exclamation-triangle',
        error: 'fas fa-times-circle',
        info: 'fas fa-info-circle'
    };
    
    toast.innerHTML = `
        <div class="toast-content">
            <i class="${icons[type]}"></i>
            <span>${message}</span>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Estilos
    toast.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: rgba(15, 15, 35, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 16px 20px;
        color: white;
        font-family: Inter, sans-serif;
        font-weight: 500;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        z-index: 9999;
        transform: translateX(100%);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 300px;
        max-width: 400px;
    `;
    
    document.body.appendChild(toast);
    
    // Animación de entrada
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto-remove
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, duration);
}

// ===================================================================
// ACTUALIZACIÓN AUTOMÁTICA DE DATOS
// ===================================================================
function startAutoUpdate() {
    updateTimer = setInterval(() => {
        updateDashboardData();
    }, DASHBOARD_CONFIG.UPDATE_INTERVAL);
}

async function updateDashboardData() {
    try {
        console.log('🔄 Actualizando datos del dashboard...');
        
        // Aquí iría la llamada real a la API
        // const response = await fetch(`${DASHBOARD_CONFIG.API_BASE_URL}/dashboard/stats`);
        // const data = await response.json();
        
        // Simular actualización de datos
        const randomUpdate = Math.random() * 10;
        
        // Actualizar gráficos
        if (charts.main) {
            const newData = charts.main.data.datasets[0].data.map(val => 
                val + (Math.random() - 0.5) * 10
            );
            charts.main.data.datasets[0].data = newData;
            charts.main.update('none');
        }
        
        console.log('✅ Datos actualizados correctamente');
        
    } catch (error) {
        console.error('❌ Error actualizando dashboard:', error);
    }
}

// ===================================================================
// FUNCIONES AUXILIARES
// ===================================================================
function createGradient(ctx, colorStops) {
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    colorStops.forEach(stop => {
        gradient.addColorStop(stop.offset, stop.color);
    });
    return gradient;
}

function generateRandomData(count) {
    return Array.from({length: count}, () => Math.floor(Math.random() * 100));
}

function updateMainChart(period) {
    if (!charts.main) return;
    
    let newData, newLabels;
    
    switch (period) {
        case 'weekly':
            newLabels = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
            newData = [45, 52, 48, 61, 55, 67, 43];
            break;
        case 'daily':
            newLabels = ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '24:00'];
            newData = [12, 8, 15, 28, 22, 18, 14];
            break;
        default: // monthly
            newLabels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 
                        'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            newData = window.DASHBOARD_DATA?.chartData?.monthly_stats || 
                     [120, 135, 155, 142, 168, 195, 210, 189, 225, 245, 267, 280];
    }
    
    charts.main.data.labels = newLabels;
    charts.main.data.datasets[0].data = newData;
    charts.main.update();
}

// ===================================================================
// EVENT LISTENERS GLOBALES
// ===================================================================
window.addEventListener('resize', () => {
    Object.values(charts).forEach(chart => {
        if (chart && typeof chart.resize === 'function') {
            chart.resize();
        }
    });
});

window.addEventListener('beforeunload', () => {
    if (updateTimer) {
        clearInterval(updateTimer);
    }
});

// ===================================================================
// API PARA USO EXTERNO
// ===================================================================
window.DashboardManager = {
    showToast: showNotificationToast,
    updateChart: updateMainChart,
    refreshData: updateDashboardData,
    getCharts: () => charts,
    config: DASHBOARD_CONFIG
};

// ===================================================================
// DEBUGGING (Solo en desarrollo)
// ===================================================================
if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    window.DASHBOARD_DEBUG = {
        charts,
        config: DASHBOARD_CONFIG,
        data: window.DASHBOARD_DATA,
        showToast: showNotificationToast,
        updateChart: updateMainChart
    };
    
    console.log('🔧 Modo desarrollo activado. Variables disponibles en window.DASHBOARD_DEBUG');
}
