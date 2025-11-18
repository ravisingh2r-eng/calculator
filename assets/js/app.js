/**
 * Calculator Hub - Main Application JavaScript
 *
 * Handles global functionality like search, analytics tracking, etc.
 * All calculator-specific logic will be in separate files.
 *
 * @author  Your Name
 * @version 1.0.0
 */

(function() {
    'use strict';

    /**
     * Calculator Search Functionality
     *
     * Searches through calculator data and shows results in dropdown
     */
    const Search = {
        // DOM elements
        input: null,
        button: null,
        results: null,

        // Data
        calculators: [],

        /**
         * Initialize search
         */
        init: function() {
            this.input = document.getElementById('calculatorSearch');
            this.button = document.getElementById('searchBtn');
            this.results = document.getElementById('searchResults');

            // Check if search elements exist on this page
            if (!this.input || !this.results) {
                return;
            }

            // Get calculator data from PHP (passed via window object)
            this.calculators = window.calculatorData || [];

            // Bind events
            this.bindEvents();

            console.log('Search initialized with', this.calculators.length, 'items');
        },

        /**
         * Bind event listeners
         */
        bindEvents: function() {
            const self = this;

            // Input event - search as user types
            this.input.addEventListener('input', function(e) {
                const query = e.target.value.trim();

                if (query.length >= 2) {
                    self.search(query);
                } else {
                    self.hideResults();
                }
            });

            // Focus event - show results if there's a query
            this.input.addEventListener('focus', function() {
                const query = self.input.value.trim();
                if (query.length >= 2) {
                    self.search(query);
                }
            });

            // Button click
            this.button.addEventListener('click', function() {
                const query = self.input.value.trim();
                if (query) {
                    self.performSearch(query);
                }
            });

            // Enter key
            this.input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const query = self.input.value.trim();
                    if (query) {
                        self.performSearch(query);
                    }
                }

                // Escape key - hide results
                if (e.key === 'Escape') {
                    self.hideResults();
                }
            });

            // Click outside to close
            document.addEventListener('click', function(e) {
                if (!self.input.contains(e.target) && !self.results.contains(e.target)) {
                    self.hideResults();
                }
            });
        },

        /**
         * Search calculators
         *
         * @param {string} query - Search query
         */
        search: function(query) {
            const lowerQuery = query.toLowerCase();

            // Filter calculators
            const matches = this.calculators.filter(function(item) {
                return item.name.toLowerCase().includes(lowerQuery) ||
                       item.category.toLowerCase().includes(lowerQuery);
            });

            // Show results
            if (matches.length > 0) {
                this.showResults(matches.slice(0, 8)); // Max 8 results
            } else {
                this.showNoResults(query);
            }

            // Log search for debugging
            console.log('Search:', query, '- Found:', matches.length, 'results');
        },

        /**
         * Show search results
         *
         * @param {Array} items - Matching calculators
         */
        showResults: function(items) {
            let html = '';

            items.forEach(function(item) {
                html += `
                    <a href="/${item.slug}" class="search-result-item">
                        <span class="search-result-icon">${item.icon}</span>
                        <span class="search-result-name">${item.name}</span>
                        <span class="search-result-category">${item.category}</span>
                    </a>
                `;
            });

            this.results.innerHTML = html;
            this.results.classList.add('active');
        },

        /**
         * Show no results message
         *
         * @param {string} query - Search query
         */
        showNoResults: function(query) {
            this.results.innerHTML = `
                <div class="search-result-item" style="justify-content: center; color: var(--muted);">
                    No calculators found for "${query}"
                </div>
            `;
            this.results.classList.add('active');
        },

        /**
         * Hide results dropdown
         */
        hideResults: function() {
            this.results.classList.remove('active');
        },

        /**
         * Perform full search (navigate to search results page)
         *
         * @param {string} query - Search query
         */
        performSearch: function(query) {
            // Log the search action
            console.log('Performing search for:', query);

            // TODO: Navigate to search results page
            // window.location.href = '/search?q=' + encodeURIComponent(query);

            // For now, just show in console
            alert('Search submitted: ' + query + '\n\nSearch results page will be implemented later.');
        }
    };

    /**
     * Analytics Tracking (placeholder)
     *
     * Will be used for custom analytics + Google Analytics events
     */
    const Analytics = {
        /**
         * Track page view
         */
        trackPageView: function() {
            console.log('Page view:', window.location.pathname);

            // TODO: Send to custom analytics endpoint
            // fetch('/api/track.php', {
            //     method: 'POST',
            //     body: JSON.stringify({ type: 'pageview', path: window.location.pathname })
            // });
        },

        /**
         * Track calculator usage
         *
         * @param {string} calculatorSlug - Calculator identifier
         */
        trackCalculatorUse: function(calculatorSlug) {
            console.log('Calculator used:', calculatorSlug);

            // TODO: Send to custom analytics endpoint
        },

        /**
         * Track search
         *
         * @param {string} query - Search query
         */
        trackSearch: function(query) {
            console.log('Search query:', query);

            // TODO: Send to custom analytics endpoint
        }
    };

    /**
     * Smooth Scroll for anchor links
     */
    const SmoothScroll = {
        init: function() {
            document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
                anchor.addEventListener('click', function(e) {
                    const targetId = this.getAttribute('href');

                    if (targetId !== '#') {
                        e.preventDefault();
                        const target = document.querySelector(targetId);

                        if (target) {
                            target.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }
                    }
                });
            });
        }
    };

    /**
     * Initialize everything when DOM is ready
     */
    function init() {
        Search.init();
        SmoothScroll.init();
        Analytics.trackPageView();

        console.log('CalcHub App initialized');
    }

    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose for external use if needed
    window.CalcHub = {
        Search: Search,
        Analytics: Analytics
    };

})();

/**
 * USAGE:
 *
 * Search is automatic - just include this file and have the search elements.
 *
 * Manual analytics tracking:
 *   CalcHub.Analytics.trackCalculatorUse('bmi');
 *   CalcHub.Analytics.trackSearch('loan calculator');
 */
