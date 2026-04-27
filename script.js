// ==========================================
// RESEARCH GRANT MANAGEMENT SYSTEM v2.0
// Enhanced JavaScript
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    initFormValidation();
    initConfirmDialogs();
    initAutoHideAlerts();
    initDropZone();
    initSmoothAnimations();
    console.log('✅ RGMS v2.0 Loaded');
});

// ==========================================
// FORM VALIDATION
// ==========================================
function initFormValidation() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('border-red-500');
                    field.classList.remove('border-white/10');
                } else {
                    field.classList.remove('border-red-500');
                    field.classList.add('border-white/10');
                }
            });
            
            if (!isValid) {
                event.preventDefault();
                showToast('Please fill all required fields', 'error');
            }
        });
    });
}

// ==========================================
// CONFIRMATION DIALOGS
// ==========================================
function initConfirmDialogs() {
    const deleteLinks = document.querySelectorAll('a[href*="delete"]');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            if (!confirm('Are you sure? This action cannot be undone.')) {
                event.preventDefault();
            }
        });
    });
    
    const statusForms = document.querySelectorAll('.update-form');
    statusForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            const statusSelect = form.querySelector('select[name="application_status"]');
            if (statusSelect) {
                const newStatus = statusSelect.value;
                if (!confirm(`Update status to "${newStatus}"?`)) {
                    event.preventDefault();
                }
            }
        });
    });
}

// ==========================================
// AUTO-HIDE ALERTS
// ==========================================
function initAutoHideAlerts() {
    const alerts = document.querySelectorAll('[class*="bg-green-500/10"], [class*="bg-red-500/10"]');
    alerts.forEach(alert => {
        if (alert.closest('form')) return; // Don't hide form errors
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
}

// ==========================================
// FILE DROP ZONE
// ==========================================
function initDropZone() {
    const dropZone = document.getElementById('drop-zone');
    if (!dropZone) return;

    ['dragenter', 'dragover'].forEach(event => {
        dropZone.addEventListener(event, (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(event => {
        dropZone.addEventListener(event, (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
        });
    });

    dropZone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        const fileInput = document.getElementById('proposal_pdf');
        if (fileInput && files.length > 0) {
            fileInput.files = files;
            showFileName(fileInput);
        }
    });
}

// ==========================================
// SMOOTH ANIMATIONS
// ==========================================
function initSmoothAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.bg-dark-800\\/50').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(10px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(card);
    });
}

// ==========================================
// TOAST NOTIFICATIONS
// ==========================================
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ==========================================
// FILE NAME DISPLAY
// ==========================================
function showFileName(input) {
    const nameEl = document.getElementById('file-name');
    if (nameEl && input.files.length > 0) {
        nameEl.textContent = '📄 ' + input.files[0].name;
        nameEl.classList.remove('hidden');
    }
}

// ==========================================
// SIDEBAR TOGGLE
// ==========================================
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (sidebar) sidebar.classList.toggle('-translate-x-full');
    if (overlay) overlay.classList.toggle('hidden');
}

// ==========================================
// UTILITY FUNCTIONS
// ==========================================
function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-IN', options);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}