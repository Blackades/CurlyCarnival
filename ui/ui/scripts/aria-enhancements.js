/**
 * ARIA Enhancements for PHPNuxBill
 * Provides dynamic ARIA support for forms, modals, and interactive elements
 */

(function() {
    'use strict';

    // Initialize ARIA enhancements when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initFormValidationAria();
        initModalAria();
        initDynamicContentAria();
        initNavigationAria();
        initTableAria();
    });

    /**
     * Form Validation ARIA Support
     * Adds aria-describedby for form errors and manages error states
     */
    function initFormValidationAria() {
        // Find all forms with validation
        const forms = document.querySelectorAll('form');
        
        forms.forEach(function(form) {
            // Add form validation event listeners
            form.addEventListener('submit', function(e) {
                validateFormAria(form, e);
            });

            // Add real-time validation for required fields
            const requiredFields = form.querySelectorAll('input[required], textarea[required], select[required]');
            requiredFields.forEach(function(field) {
                field.addEventListener('blur', function() {
                    validateFieldAria(field);
                });
                
                field.addEventListener('input', function() {
                    clearFieldError(field);
                });
            });
        });
    }

    /**
     * Validate individual field and update ARIA attributes
     */
    function validateFieldAria(field) {
        const fieldId = field.id;
        const errorId = fieldId + '-error';
        let errorElement = document.getElementById(errorId);
        
        // Create error element if it doesn't exist
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.id = errorId;
            errorElement.className = 'error-message';
            errorElement.setAttribute('role', 'alert');
            errorElement.setAttribute('aria-live', 'polite');
            errorElement.style.display = 'none';
            field.parentNode.appendChild(errorElement);
        }

        // Validate field
        let isValid = true;
        let errorMessage = '';

        if (field.hasAttribute('required') && !field.value.trim()) {
            isValid = false;
            errorMessage = 'This field is required.';
        } else if (field.type === 'email' && field.value && !isValidEmail(field.value)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address.';
        }

        // Update ARIA attributes and display
        if (!isValid) {
            field.setAttribute('aria-invalid', 'true');
            field.setAttribute('aria-describedby', getAriaDescribedBy(field, errorId));
            errorElement.textContent = errorMessage;
            errorElement.style.display = 'block';
            errorElement.style.color = '#d32f2f';
            errorElement.style.fontSize = '0.875rem';
            errorElement.style.marginTop = '0.25rem';
        } else {
            clearFieldError(field);
        }

        return isValid;
    }

    /**
     * Clear field error state
     */
    function clearFieldError(field) {
        const fieldId = field.id;
        const errorId = fieldId + '-error';
        const errorElement = document.getElementById(errorId);
        
        field.removeAttribute('aria-invalid');
        if (errorElement) {
            errorElement.style.display = 'none';
            errorElement.textContent = '';
        }
        
        // Update aria-describedby to remove error reference
        const describedBy = getAriaDescribedBy(field, errorId, true);
        if (describedBy) {
            field.setAttribute('aria-describedby', describedBy);
        } else {
            field.removeAttribute('aria-describedby');
        }
    }

    /**
     * Validate entire form and prevent submission if invalid
     */
    function validateFormAria(form, event) {
        const requiredFields = form.querySelectorAll('input[required], textarea[required], select[required]');
        let isFormValid = true;
        let firstInvalidField = null;

        requiredFields.forEach(function(field) {
            const isFieldValid = validateFieldAria(field);
            if (!isFieldValid) {
                isFormValid = false;
                if (!firstInvalidField) {
                    firstInvalidField = field;
                }
            }
        });

        if (!isFormValid) {
            event.preventDefault();
            if (firstInvalidField) {
                firstInvalidField.focus();
            }
        }
    }

    /**
     * Modal ARIA Support
     * Manages focus and ARIA attributes for modals
     */
    function initModalAria() {
        // Handle Bootstrap modals
        const modals = document.querySelectorAll('.modal');
        
        modals.forEach(function(modal) {
            modal.addEventListener('show.bs.modal', function() {
                // Store the element that triggered the modal
                modal.previousActiveElement = document.activeElement;
                
                // Set aria-hidden on other content
                setAriaHiddenOnSiblings(modal, true);
            });

            modal.addEventListener('shown.bs.modal', function() {
                // Focus the first focusable element in the modal
                const focusableElement = modal.querySelector('input, textarea, select, button, [tabindex]:not([tabindex="-1"])');
                if (focusableElement) {
                    focusableElement.focus();
                }
            });

            modal.addEventListener('hidden.bs.modal', function() {
                // Restore focus to the triggering element
                if (modal.previousActiveElement) {
                    modal.previousActiveElement.focus();
                }
                
                // Remove aria-hidden from other content
                setAriaHiddenOnSiblings(modal, false);
            });
        });
    }

    /**
     * Dynamic Content ARIA Support
     * Manages ARIA live regions for dynamic content updates
     */
    function initDynamicContentAria() {
        // Monitor API content updates
        const apiElements = document.querySelectorAll('[api-get-text]');
        
        apiElements.forEach(function(element) {
            // Add aria-live if not already present
            if (!element.hasAttribute('aria-live')) {
                element.setAttribute('aria-live', 'polite');
            }
            
            // Monitor for content changes
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' || mutation.type === 'characterData') {
                        // Content has changed, ensure it's announced
                        element.setAttribute('aria-live', 'polite');
                    }
                });
            });
            
            observer.observe(element, {
                childList: true,
                subtree: true,
                characterData: true
            });
        });

        // Handle notification updates
        const notificationContainers = document.querySelectorAll('.notifications-container, .alert-container');
        notificationContainers.forEach(function(container) {
            if (!container.hasAttribute('aria-live')) {
                container.setAttribute('aria-live', 'polite');
            }
        });
    }

    /**
     * Navigation ARIA Support
     * Enhances navigation menus with proper ARIA attributes
     */
    function initNavigationAria() {
        // Handle dropdown menus
        const dropdownToggles = document.querySelectorAll('[data-toggle="dropdown"]');
        
        dropdownToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
                toggle.setAttribute('aria-expanded', !isExpanded);
            });
        });

        // Handle sidebar toggle
        const sidebarToggle = document.querySelector('.sidebar-toggle, .mobile-menu-toggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                const sidebar = document.querySelector('.main-sidebar, #main-sidebar, #customer-sidebar');
                if (sidebar) {
                    const isExpanded = sidebarToggle.getAttribute('aria-expanded') === 'true';
                    sidebarToggle.setAttribute('aria-expanded', !isExpanded);
                }
            });
        }

        // Handle submenu toggles
        const submenuToggles = document.querySelectorAll('.submenu-toggle');
        submenuToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
                toggle.setAttribute('aria-expanded', !isExpanded);
                
                const submenu = toggle.nextElementSibling;
                if (submenu) {
                    submenu.style.display = isExpanded ? 'none' : 'block';
                }
            });
        });
    }

    /**
     * Table ARIA Support
     * Enhances data tables with proper ARIA attributes
     */
    function initTableAria() {
        // Add table captions if missing
        const tables = document.querySelectorAll('table:not([role="presentation"])');
        
        tables.forEach(function(table) {
            if (!table.querySelector('caption')) {
                const caption = document.createElement('caption');
                caption.className = 'sr-only';
                caption.textContent = 'Data table';
                table.insertBefore(caption, table.firstChild);
            }
            
            // Add scope attributes to headers
            const headers = table.querySelectorAll('th');
            headers.forEach(function(header) {
                if (!header.hasAttribute('scope')) {
                    header.setAttribute('scope', 'col');
                }
            });
        });

        // Handle sortable columns
        const sortableHeaders = document.querySelectorAll('th.sortable');
        sortableHeaders.forEach(function(header) {
            header.setAttribute('role', 'columnheader');
            header.setAttribute('tabindex', '0');
            
            if (!header.hasAttribute('aria-sort')) {
                header.setAttribute('aria-sort', 'none');
            }
        });
    }

    /**
     * Utility Functions
     */
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function getAriaDescribedBy(field, errorId, removeError = false) {
        const currentDescribedBy = field.getAttribute('aria-describedby') || '';
        const describedByIds = currentDescribedBy.split(' ').filter(id => id.trim());
        
        if (removeError) {
            // Remove error ID from the list
            const filteredIds = describedByIds.filter(id => id !== errorId);
            return filteredIds.join(' ');
        } else {
            // Add error ID if not already present
            if (!describedByIds.includes(errorId)) {
                describedByIds.push(errorId);
            }
            return describedByIds.join(' ');
        }
    }

    function setAriaHiddenOnSiblings(element, hidden) {
        const siblings = Array.from(element.parentNode.children);
        siblings.forEach(function(sibling) {
            if (sibling !== element) {
                if (hidden) {
                    sibling.setAttribute('aria-hidden', 'true');
                } else {
                    sibling.removeAttribute('aria-hidden');
                }
            }
        });
    }

    // Export functions for external use
    window.AriaEnhancements = {
        validateFieldAria: validateFieldAria,
        clearFieldError: clearFieldError,
        validateFormAria: validateFormAria
    };

})();