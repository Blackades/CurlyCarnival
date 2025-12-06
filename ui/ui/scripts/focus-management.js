/**
 * PHPNuxBill Modern Design System
 * Focus Management and Keyboard Navigation Enhancement
 * 
 * Provides enhanced focus management, keyboard navigation detection,
 * and accessibility improvements for interactive elements.
 */

(function() {
    'use strict';

    // Track keyboard vs mouse usage
    let isKeyboardUser = false;
    let isMouseUser = false;

    /**
     * Initialize focus management system
     */
    function initFocusManagement() {
        // Add keyboard navigation detection
        detectNavigationMethod();
        
        // Initialize focus traps for modals
        initFocusTraps();
        
        // Initialize skip links
        initSkipLinks();
        
        // Initialize ARIA live regions
        initLiveRegions();
        
        // Initialize focus-visible polyfill for older browsers
        initFocusVisiblePolyfill();
        
        // Initialize enhanced keyboard navigation
        initKeyboardNavigation();
    }

    /**
     * Detect whether user is navigating with keyboard or mouse
     */
    function detectNavigationMethod() {
        // Track keyboard usage
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Tab' || e.key === 'Enter' || e.key === ' ' || e.key === 'Escape') {
                isKeyboardUser = true;
                isMouseUser = false;
                document.body.classList.add('keyboard-navigation');
                document.body.classList.remove('mouse-navigation');
            }
        });

        // Track mouse usage
        document.addEventListener('mousedown', function() {
            isMouseUser = true;
            isKeyboardUser = false;
            document.body.classList.add('mouse-navigation');
            document.body.classList.remove('keyboard-navigation');
        });

        // Initial state
        document.body.classList.add('mouse-navigation');
    }

    /**
     * Initialize focus traps for modals and dropdowns
     */
    function initFocusTraps() {
        // Focus trap for modals
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                const modal = document.querySelector('.modal.show');
                if (modal) {
                    trapFocus(e, modal);
                }
            }
        });

        // Focus trap for dropdowns
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                const dropdown = document.querySelector('.dropdown.show');
                if (dropdown) {
                    trapFocus(e, dropdown);
                }
            }
        });
    }

    /**
     * Trap focus within a container
     */
    function trapFocus(event, container) {
        const focusableElements = container.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        const firstFocusable = focusableElements[0];
        const lastFocusable = focusableElements[focusableElements.length - 1];

        if (event.shiftKey) {
            // Shift + Tab
            if (document.activeElement === firstFocusable) {
                event.preventDefault();
                lastFocusable.focus();
            }
        } else {
            // Tab
            if (document.activeElement === lastFocusable) {
                event.preventDefault();
                firstFocusable.focus();
            }
        }
    }

    /**
     * Initialize skip links functionality
     */
    function initSkipLinks() {
        // Create skip link if it doesn't exist
        if (!document.querySelector('.skip-link')) {
            const skipLink = document.createElement('a');
            skipLink.href = '#main-content';
            skipLink.className = 'skip-link';
            skipLink.textContent = 'Skip to main content';
            document.body.insertBefore(skipLink, document.body.firstChild);
        }

        // Handle skip link clicks
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('skip-link')) {
                e.preventDefault();
                const target = document.querySelector(e.target.getAttribute('href'));
                if (target) {
                    target.focus();
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    }

    /**
     * Initialize ARIA live regions for dynamic content
     */
    function initLiveRegions() {
        // Create live region if it doesn't exist
        if (!document.querySelector('.live-region')) {
            const liveRegion = document.createElement('div');
            liveRegion.className = 'live-region';
            liveRegion.setAttribute('aria-live', 'polite');
            liveRegion.setAttribute('aria-atomic', 'true');
            document.body.appendChild(liveRegion);
        }
    }

    /**
     * Announce message to screen readers
     */
    function announceToScreenReader(message) {
        const liveRegion = document.querySelector('.live-region');
        if (liveRegion) {
            liveRegion.textContent = message;
            // Clear after announcement
            setTimeout(() => {
                liveRegion.textContent = '';
            }, 1000);
        }
    }

    /**
     * Focus-visible polyfill for older browsers
     */
    function initFocusVisiblePolyfill() {
        // Simple polyfill for :focus-visible
        if (!CSS.supports('selector(:focus-visible)')) {
            document.body.classList.add('js-focus-visible');
            
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Tab') {
                    document.body.classList.add('focus-visible-active');
                }
            });
            
            document.addEventListener('mousedown', function() {
                document.body.classList.remove('focus-visible-active');
            });
        }
    }

    /**
     * Enhanced keyboard navigation
     */
    function initKeyboardNavigation() {
        // Arrow key navigation for button groups
        initButtonGroupNavigation();
        
        // Enhanced table navigation
        initTableNavigation();
        
        // Enhanced sidebar navigation
        initSidebarNavigation();
        
        // Enhanced modal navigation
        initModalNavigation();
    }

    /**
     * Arrow key navigation for button groups
     */
    function initButtonGroupNavigation() {
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
                const buttonGroup = e.target.closest('.btn-group, .btn-toolbar');
                if (buttonGroup) {
                    e.preventDefault();
                    const buttons = buttonGroup.querySelectorAll('.btn:not(:disabled)');
                    const currentIndex = Array.from(buttons).indexOf(e.target);
                    
                    let nextIndex;
                    if (e.key === 'ArrowRight') {
                        nextIndex = (currentIndex + 1) % buttons.length;
                    } else {
                        nextIndex = (currentIndex - 1 + buttons.length) % buttons.length;
                    }
                    
                    buttons[nextIndex].focus();
                }
            }
        });
    }

    /**
     * Enhanced table navigation
     */
    function initTableNavigation() {
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                const cell = e.target.closest('td, th');
                if (cell && e.target.classList.contains('btn-table-action')) {
                    e.preventDefault();
                    const row = cell.closest('tr');
                    const table = row.closest('table');
                    const rows = table.querySelectorAll('tbody tr');
                    const currentRowIndex = Array.from(rows).indexOf(row);
                    const cellIndex = Array.from(row.cells).indexOf(cell);
                    
                    let nextRowIndex;
                    if (e.key === 'ArrowDown') {
                        nextRowIndex = Math.min(currentRowIndex + 1, rows.length - 1);
                    } else {
                        nextRowIndex = Math.max(currentRowIndex - 1, 0);
                    }
                    
                    const nextRow = rows[nextRowIndex];
                    const nextCell = nextRow.cells[cellIndex];
                    const nextButton = nextCell.querySelector('.btn-table-action');
                    
                    if (nextButton) {
                        nextButton.focus();
                    }
                }
            }
        });
    }

    /**
     * Enhanced sidebar navigation
     */
    function initSidebarNavigation() {
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                const menuLink = e.target.closest('.sidebar-menu-link');
                if (menuLink) {
                    e.preventDefault();
                    const menu = menuLink.closest('.sidebar-menu, .sidebar-submenu');
                    const menuItems = menu.querySelectorAll('.sidebar-menu-link:not(.disabled)');
                    const currentIndex = Array.from(menuItems).indexOf(menuLink);
                    
                    let nextIndex;
                    if (e.key === 'ArrowDown') {
                        nextIndex = (currentIndex + 1) % menuItems.length;
                    } else {
                        nextIndex = (currentIndex - 1 + menuItems.length) % menuItems.length;
                    }
                    
                    menuItems[nextIndex].focus();
                }
            }
        });
    }

    /**
     * Enhanced modal navigation
     */
    function initModalNavigation() {
        // Focus first focusable element when modal opens
        document.addEventListener('DOMNodeInserted', function(e) {
            if (e.target.classList && e.target.classList.contains('modal') && e.target.classList.contains('show')) {
                setTimeout(() => {
                    const firstFocusable = e.target.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
                    if (firstFocusable) {
                        firstFocusable.focus();
                    }
                }, 100);
            }
        });

        // Return focus when modal closes
        let lastFocusedElement = null;
        
        document.addEventListener('focus', function(e) {
            if (!e.target.closest('.modal')) {
                lastFocusedElement = e.target;
            }
        }, true);
        
        document.addEventListener('DOMNodeRemoved', function(e) {
            if (e.target.classList && e.target.classList.contains('modal')) {
                if (lastFocusedElement) {
                    setTimeout(() => {
                        lastFocusedElement.focus();
                    }, 100);
                }
            }
        });
    }

    /**
     * Enhanced form validation announcements
     */
    function announceFormValidation() {
        // Announce validation errors
        document.addEventListener('invalid', function(e) {
            const errorMessage = e.target.validationMessage || 'Please correct this field';
            announceToScreenReader(`Error: ${errorMessage}`);
        }, true);

        // Announce successful form submissions
        document.addEventListener('submit', function(e) {
            if (e.target.checkValidity()) {
                announceToScreenReader('Form submitted successfully');
            }
        });
    }

    /**
     * Enhanced alert announcements
     */
    function announceAlerts() {
        // Announce new alerts
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && node.classList.contains('alert')) {
                        const message = node.textContent.trim();
                        const type = node.className.match(/alert-(\w+)/);
                        const alertType = type ? type[1] : 'notification';
                        announceToScreenReader(`${alertType}: ${message}`);
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /**
     * Utility functions for external use
     */
    window.FocusManager = {
        announceToScreenReader: announceToScreenReader,
        trapFocus: trapFocus,
        isKeyboardUser: function() { return isKeyboardUser; },
        isMouseUser: function() { return isMouseUser; }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFocusManagement);
    } else {
        initFocusManagement();
    }

    // Initialize additional features
    announceFormValidation();
    announceAlerts();

})();