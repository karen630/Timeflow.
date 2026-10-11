/* ============================================
   TimeFlow - JavaScript Global
   ============================================ */

function initSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const hamburger = document.querySelector('.hamburger');
    if (!sidebar) return;

    function openSidebar() {
        sidebar.classList.add('open');
        overlay && overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay && overlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    hamburger && hamburger.addEventListener('click', openSidebar);
    overlay && overlay.addEventListener('click', closeSidebar);
}

function showToast(message, type = 'info', duration = 3000) {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(8px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

function openModal(id) {
    const m = document.getElementById(id);
    if (m) { m.classList.add('open'); document.body.style.overflow = 'hidden'; }
}

function closeModal(id) {
    const m = document.getElementById(id);
    if (m) { m.classList.remove('open'); document.body.style.overflow = ''; }
}

document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('open');
        document.body.style.overflow = '';
    }
});

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('es-MX', { day:'numeric', month:'short', year:'numeric' });
}

function formatTime(timeStr) {
    if (!timeStr) return '';
    const [h, m] = timeStr.split(':');
    const hour = parseInt(h);
    const ampm = hour >= 12 ? 'pm' : 'am';
    return `${hour % 12 || 12}:${m} ${ampm}`;
}

function isToday(dateStr) {
    if (!dateStr) return false;
    const today = new Date().toISOString().split('T')[0];
    return dateStr === today;
}

const CAT_CONFIG = {
    'Estudio':   { color: '#4A90D9', icon: '📚' },
    'Trabajo':   { color: '#9B59B6', icon: '💼' },
    'Ejercicio': { color: '#27AE60', icon: '🏃' },
    'Social':    { color: '#E91E8C', icon: '👥' },
    'Hábito':    { color: '#F39C12', icon: '⭐' },
};

async function apiRequest(url, data = {}) {
    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(data)
        });
        return await res.json();
    } catch (e) {
        console.error('API error:', e);
        return { success: false, error: 'Error de conexión' };
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    const current = window.location.pathname;
    document.querySelectorAll('.nav-item').forEach(item => {
        if (item.getAttribute('href') && current.includes(item.getAttribute('href').split('/').pop())) {
            item.classList.add('active');
        }
    });
});
