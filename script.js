// ==========================================
// RESEARCH GRANT MANAGEMENT SYSTEM - JS
// ==========================================

// Wait for DOM to fully load
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize all features
    initFormValidation();
    initConfirmDialogs();
    initAutoHideAlerts();
    initTableSearch();
    initTooltips();
    
    console.log('✅ RGMS JavaScript Loaded Successfully');
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
                    field.style.borderColor = '#e74c3c';
                } else {
                    field.style.borderColor = '#e0e0e0';
                }
            });
            
            if (!isValid) {
                event.preventDefault();
                showAlert('Please fill all required fields', 'error');
            }
        });
    });
}

// ==========================================
// CONFIRMATION DIALOGS
// ==========================================
function initConfirmDialogs() {
    // Delete confirmations
    const deleteLinks = document.querySelectorAll('a[href*="delete"]');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                event.preventDefault();
            }
        });
    });
    
    // Status update confirmations
    const statusForms = document.querySelectorAll('.update-form');
    statusForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            const statusSelect = form.querySelector('select[name="application_status"]');
            if (statusSelect) {
                const newStatus = statusSelect.value;
                if (!confirm(`Are you sure you want to update the status to "${newStatus}"?`)) {
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
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000); // Hide after 5 seconds
    });
}

// ==========================================
// TABLE SEARCH
// ==========================================
function initTableSearch() {
    const searchInputs = document.querySelectorAll('.table-search');
    
    searchInputs.forEach(input => {
        input.addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const table = this.closest('.panel').querySelector('table');
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
}

// ==========================================
// TOOLTIPS
// ==========================================
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltipText = this.getAttribute('data-tooltip');
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = tooltipText;
            tooltip.style.cssText = `
                position: absolute;
                background: #333;
                color: white;
                padding: 8px 12px;
                border-radius: 5px;
                font-size: 12px;
                z-index: 1000;
                white-space: nowrap;
            `;
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
            tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
            
            this._tooltip = tooltip;
        });
        
        element.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
                delete this._tooltip;
            }
        });
    });
}

// ==========================================
// SHOW ALERT FUNCTION
// ==========================================
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    alertDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        min-width: 300px;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => alertDiv.remove(), 300);
    }, 3000);
}

// ==========================================
// FORM FIELD VALIDATION HELPERS
// ==========================================
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^[0-9]{10}$/;
    return re.test(phone.replace(/[\s-]/g, ''));
}

function validateAmount(amount) {
    return !isNaN(amount) && parseFloat(amount) > 0;
}

// ==========================================
// ANIMATIONS
// ==========================================
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ==========================================
// UTILITY FUNCTIONS
// ==========================================

// Format currency
function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-IN', options);
}

// Debounce function for search
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

console.log('🚀 All systems ready!');
```

---

## **📋 FINAL CHECKLIST - AB SAB COMPLETE HAI!**

| File | Status | Description |
|------|--------|-------------|
| ✅ **index.php** | Complete | Landing page with 3 login options |
| ✅ **login.php** | Complete | User login page |
| ✅ **signup.php** | Complete | New user registration |
| ✅ **admin_login.php** | Complete | Admin login (password only) |
| ✅ **logout.php** | Complete | Logout handler |
| ✅ **auth_check.php** | Complete | Session management |
| ✅ **db_connection.php** | Complete | Database config |
| ✅ **user_dashboard.php** | Complete | User dashboard (10+ features) |
| ✅ **admin_dashboard.php** | Complete | Admin dashboard (10+ features) |
| ✅ **apply_grant.php** | Complete | Grant application form |
| ✅ **my_applications.php** | Complete | View user applications |
| ✅ **manage_researchers.php** | Complete | Admin - manage researchers |
| ✅ **manage_agencies.php** | Complete | Admin - manage agencies |
| ✅ **style.css** | Complete | Professional CSS |
| ✅ **script.js** | Complete | Enhanced JavaScript |
| ✅ **SQL Database** | Complete | Full schema with sample data |

---

## **🔐 ADMIN PASSWORD (YE CHAT ME HAI, PAGE PE NAHI DIKHEGA)**
```
Admin Password: AdminSecure@2025
```

**Testing Credentials:**
- **Admin:** Password `AdminSecure@2025`
- **Users:** Username `rajesh_kumar`, `priya_sharma`, etc. | Password `User@123`

---

## **🚀 DEPLOYMENT STEPS**

### **1. Upload sab files apne server pe:**
```
/public_html/
├── index.php
├── login.php
├── signup.php
├── admin_login.php
├── logout.php
├── auth_check.php
├── db_connection.php
├── user_dashboard.php
├── admin_dashboard.php
├── apply_grant.php
├── my_applications.php
├── manage_researchers.php
├── manage_agencies.php
├── style.css
└── script.js