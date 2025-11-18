/**
 * HTML Partial Include System
 *
 * Yeh script data-include attribute wale elements ko find karta hai
 * aur unme specified HTML file ka content inject karta hai.
 *
 * Usage:
 *   <div data-include="/partials/header.html"></div>
 *   <div data-include="/partials/footer.html"></div>
 *
 * Features:
 * - Vanilla JavaScript (no jQuery)
 * - Error handling with fallback message
 * - Loading state management
 * - Cache-friendly
 * - Works with relative and absolute paths
 *
 * @author  Your Name
 * @version 1.0.0
 */

(function() {
    'use strict';

    /**
     * Include Configuration
     */
    const config = {
        // Attribute name to look for
        attribute: 'data-include',

        // Class added while loading
        loadingClass: 'partial-loading',

        // Class added after successful load
        loadedClass: 'partial-loaded',

        // Class added on error
        errorClass: 'partial-error',

        // Show error message in element on failure
        showErrorMessage: true,

        // Cache fetched partials (in memory)
        useCache: true,

        // Request timeout (milliseconds)
        timeout: 5000
    };

    /**
     * Cache for fetched partials
     * Key: URL, Value: HTML content
     */
    const cache = {};

    /**
     * Fetch HTML content from URL
     *
     * @param {string} url - URL to fetch
     * @returns {Promise<string>} - HTML content
     */
    async function fetchPartial(url) {
        // Check cache first
        if (config.useCache && cache[url]) {
            return cache[url];
        }

        // Create AbortController for timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), config.timeout);

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'text/html'
                },
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const html = await response.text();

            // Store in cache
            if (config.useCache) {
                cache[url] = html;
            }

            return html;

        } catch (error) {
            clearTimeout(timeoutId);

            if (error.name === 'AbortError') {
                throw new Error('Request timeout');
            }

            throw error;
        }
    }

    /**
     * Load partial into element
     *
     * @param {HTMLElement} element - Element with data-include attribute
     */
    async function loadPartial(element) {
        const url = element.getAttribute(config.attribute);

        if (!url) {
            console.warn('Include: Empty URL in data-include attribute');
            return;
        }

        // Add loading state
        element.classList.add(config.loadingClass);

        try {
            // Fetch the partial
            const html = await fetchPartial(url);

            // Inject HTML content
            element.innerHTML = html;

            // Execute any scripts in the partial
            executeScripts(element);

            // Update classes
            element.classList.remove(config.loadingClass);
            element.classList.add(config.loadedClass);

            // Dispatch custom event
            element.dispatchEvent(new CustomEvent('partial:loaded', {
                detail: { url: url }
            }));

        } catch (error) {
            console.error(`Include: Failed to load ${url}`, error);

            // Update classes
            element.classList.remove(config.loadingClass);
            element.classList.add(config.errorClass);

            // Show error message
            if (config.showErrorMessage) {
                element.innerHTML = `
                    <div style="padding: 10px; background: #fee; color: #c00; border: 1px solid #fcc; border-radius: 4px; font-size: 0.85rem;">
                        Failed to load: ${url}
                    </div>
                `;
            }

            // Dispatch error event
            element.dispatchEvent(new CustomEvent('partial:error', {
                detail: { url: url, error: error.message }
            }));
        }
    }

    /**
     * Execute scripts found in loaded partial
     *
     * When HTML is injected via innerHTML, scripts don't execute automatically.
     * This function finds and executes them.
     *
     * @param {HTMLElement} element - Container element
     */
    function executeScripts(element) {
        const scripts = element.querySelectorAll('script');

        scripts.forEach(function(oldScript) {
            const newScript = document.createElement('script');

            // Copy attributes
            Array.from(oldScript.attributes).forEach(function(attr) {
                newScript.setAttribute(attr.name, attr.value);
            });

            // Copy inline script content
            newScript.textContent = oldScript.textContent;

            // Replace old script with new one (this executes it)
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    }

    /**
     * Initialize: Find and load all partials
     */
    function init() {
        // Find all elements with data-include attribute
        const elements = document.querySelectorAll(`[${config.attribute}]`);

        if (elements.length === 0) {
            return;
        }

        // Load each partial
        elements.forEach(function(element) {
            loadPartial(element);
        });
    }

    /**
     * Public API
     *
     * Exposed methods for manual control
     */
    window.PartialInclude = {
        /**
         * Manually load a specific element
         * @param {HTMLElement} element
         */
        load: function(element) {
            return loadPartial(element);
        },

        /**
         * Reload all partials on page
         */
        reloadAll: function() {
            init();
        },

        /**
         * Clear the cache
         */
        clearCache: function() {
            Object.keys(cache).forEach(function(key) {
                delete cache[key];
            });
        },

        /**
         * Update configuration
         * @param {Object} newConfig
         */
        configure: function(newConfig) {
            Object.assign(config, newConfig);
        }
    };

    /**
     * Auto-initialize when DOM is ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        // DOM already loaded
        init();
    }

})();

/**
 * USAGE EXAMPLES:
 *
 * Basic usage (automatic):
 *   <div data-include="/partials/header.html"></div>
 *   <div data-include="/partials/footer.html"></div>
 *   <script src="/assets/js/include.js"></script>
 *
 * Listen for load complete:
 *   document.querySelector('[data-include="/partials/header.html"]')
 *     .addEventListener('partial:loaded', function(e) {
 *       console.log('Header loaded from:', e.detail.url);
 *     });
 *
 * Manual reload:
 *   PartialInclude.reloadAll();
 *
 * Clear cache and reload:
 *   PartialInclude.clearCache();
 *   PartialInclude.reloadAll();
 *
 * Disable error messages:
 *   PartialInclude.configure({ showErrorMessage: false });
 */
