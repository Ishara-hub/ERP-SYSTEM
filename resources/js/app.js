// Bootstrap JavaScript
import * as bootstrap from 'bootstrap';

// Make bootstrap available globally
window.bootstrap = bootstrap;

// Custom JavaScript for ERP System
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Sidebar toggle functionality
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
        });
    }

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert[data-auto-hide]');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Confirm delete functionality
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            const message = this.getAttribute('data-confirm-message') || 'Are you sure you want to delete this item?';
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });

    // Auto-submit forms on change (for filters)
    const autoSubmitForms = document.querySelectorAll('[data-auto-submit]');
    autoSubmitForms.forEach(function(form) {
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(function(input) {
            input.addEventListener('change', function() {
                form.submit();
            });
        });
    });

    // Table row click functionality
    const tableRows = document.querySelectorAll('[data-href]');
    tableRows.forEach(function(row) {
        row.addEventListener('click', function() {
            const href = this.getAttribute('data-href');
            if (href) {
                window.location.href = href;
            }
        });
    });

    // Search functionality
    const searchInputs = document.querySelectorAll('[data-search-target]');
    searchInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            const target = this.getAttribute('data-search-target');
            const searchTerm = this.value.toLowerCase();
            const targetElements = document.querySelectorAll(target);
            
            targetElements.forEach(function(element) {
                const text = element.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    element.style.display = '';
                } else {
                    element.style.display = 'none';
                }
            });
        });
    });

    // Loading states for buttons
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const form = this.closest('form');
            if (form && form.checkValidity()) {
                this.disabled = true;
                this.innerHTML = '<span class="spinner"></span> Processing...';
            }
        });
    });

    // Currency formatting
    const currencyInputs = document.querySelectorAll('[data-currency]');
    currencyInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            const value = parseFloat(this.value);
            if (!isNaN(value)) {
                this.value = value.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        });
    });

    // Date formatting
    const dateInputs = document.querySelectorAll('[data-date]');
    dateInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            const date = new Date(this.value);
            if (!isNaN(date.getTime())) {
                this.value = date.toISOString().split('T')[0];
            }
        });
    });
});

// Utility functions
window.ERP = {
    // Format currency
    formatCurrency: function(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    },

    // Format date
    formatDate: function(date) {
        return new Intl.DateTimeFormat('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        }).format(new Date(date));
    },

    // Show toast notification
    showToast: function(message, type = 'info') {
        const toastContainer = document.getElementById('toast-container') || this.createToastContainer();
        const toast = this.createToast(message, type);
        toastContainer.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', function() {
            toast.remove();
        });
    },

    // Create toast container
    createToastContainer: function() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '1055';
        document.body.appendChild(container);
        return container;
    },

    // Create toast element
    createToast: function(message, type) {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        const bgClass = {
            'success': 'bg-success',
            'error': 'bg-danger',
            'warning': 'bg-warning',
            'info': 'bg-info'
        }[type] || 'bg-info';
        
        toast.innerHTML = `
            <div class="toast-header ${bgClass} text-white">
                <strong class="me-auto">${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;
        
        return toast;
    },

    // Confirm action
    confirm: function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    },

    // AJAX helper
    ajax: function(url, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        };
        
        return fetch(url, { ...defaultOptions, ...options })
            .then(response => response.json())
            .catch(error => {
                console.error('AJAX Error:', error);
                this.showToast('An error occurred while processing your request', 'error');
            });
    }
};

