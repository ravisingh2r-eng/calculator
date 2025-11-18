/**
 * Calculator Hub - Main Application JavaScript
 *
 * Handles global functionality like search, analytics tracking, FAQ rendering
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

        // Debounce timer
        debounceTimer: null,

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
        },

        /**
         * Bind event listeners
         */
        bindEvents: function() {
            const self = this;

            // Input event - search as user types (with debounce)
            this.input.addEventListener('keyup', function(e) {
                const query = e.target.value.trim();

                // Clear previous timer
                clearTimeout(self.debounceTimer);

                // Debounce API calls
                self.debounceTimer = setTimeout(function() {
                    if (query.length >= 2) {
                        self.search(query);
                    } else {
                        self.hideResults();
                    }
                }, 300);
            });

            // Focus event - show results if there's a query
            this.input.addEventListener('focus', function() {
                const query = self.input.value.trim();
                if (query.length >= 2) {
                    self.search(query);
                }
            });

            // Button click
            if (this.button) {
                this.button.addEventListener('click', function() {
                    const query = self.input.value.trim();
                    if (query) {
                        self.performSearch(query);
                    }
                });
            }

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
            const self = this;
            const lowerQuery = query.toLowerCase();

            // If we have local data, use it first
            if (this.calculators.length > 0) {
                const matches = this.calculators.filter(function(item) {
                    return item.name.toLowerCase().includes(lowerQuery) ||
                           (item.category && item.category.toLowerCase().includes(lowerQuery));
                });

                if (matches.length > 0) {
                    this.showResults(matches.slice(0, 8));
                } else {
                    this.showNoResults(query);
                }
            } else {
                // Call API for search
                this.searchAPI(query);
            }
        },

        /**
         * Search via API
         *
         * @param {string} query - Search query
         */
        searchAPI: function(query) {
            const self = this;

            // Show loading
            this.results.innerHTML = '<div class="search-result-item" style="justify-content: center; color: var(--muted);">Searching...</div>';
            this.results.classList.add('active');

            // Fetch from API
            fetch('/api/search.php?q=' + encodeURIComponent(query))
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.results && data.results.length > 0) {
                        self.showResults(data.results.slice(0, 8));
                    } else {
                        self.showNoResults(query);
                    }
                })
                .catch(function(error) {
                    console.error('Search error:', error);
                    // Fallback to local search if API fails
                    self.showNoResults(query);
                });
        },

        /**
         * Show search results
         *
         * @param {Array} items - Matching calculators
         */
        showResults: function(items) {
            let html = '';

            items.forEach(function(item) {
                const icon = item.icon || item.category_icon || '🧮';
                const category = item.category || item.category_name || '';
                const slug = item.slug || '';

                html += '<a href="/calculator.php?slug=' + encodeURIComponent(slug) + '" class="search-result-item">' +
                    '<span class="search-result-icon">' + icon + '</span>' +
                    '<span class="search-result-name">' + escapeHtml(item.name) + '</span>' +
                    '<span class="search-result-category">' + escapeHtml(category) + '</span>' +
                '</a>';
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
            this.results.innerHTML = '<div class="search-result-item" style="justify-content: center; color: var(--muted);">' +
                'No calculators found for "' + escapeHtml(query) + '"' +
            '</div>';
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
            // Navigate to search results page
            window.location.href = '/search.php?q=' + encodeURIComponent(query);
        }
    };

    /**
     * FAQ Rendering for Calculator Pages
     */
    const FAQ = {
        container: null,

        /**
         * Initialize FAQ rendering
         */
        init: function() {
            this.container = document.getElementById('calculator-faq');

            // Check if FAQ container exists and we have FAQ data
            if (!this.container) {
                return;
            }

            // Check if FAQs are passed via window object
            if (window.CALCULATOR_FAQS && window.CALCULATOR_FAQS.length > 0) {
                this.render(window.CALCULATOR_FAQS);
            }
        },

        /**
         * Render FAQ items
         *
         * @param {Array} faqs - Array of FAQ objects with question and answer
         */
        render: function(faqs) {
            let html = '<h2 class="faq-title">Frequently Asked Questions</h2>';

            faqs.forEach(function(faq, index) {
                if (faq.question && faq.answer) {
                    html += '<div class="faq-item" id="faq-' + index + '">' +
                        '<div class="faq-question" onclick="CalcHub.FAQ.toggle(' + index + ')">' +
                            '<span>' + escapeHtml(faq.question) + '</span>' +
                            '<span class="faq-toggle">▼</span>' +
                        '</div>' +
                        '<div class="faq-answer">' + escapeHtml(faq.answer).replace(/\n/g, '<br>') + '</div>' +
                    '</div>';
                }
            });

            this.container.innerHTML = html;

            // Open first FAQ by default
            var firstFaq = document.getElementById('faq-0');
            if (firstFaq) {
                firstFaq.classList.add('active');
            }
        },

        /**
         * Toggle FAQ item
         *
         * @param {number} index - FAQ index
         */
        toggle: function(index) {
            var item = document.getElementById('faq-' + index);
            if (item) {
                item.classList.toggle('active');
            }
        }
    };

    /**
     * Analytics & Event Logging
     */
    const Analytics = {
        /**
         * Log event to server
         *
         * @param {number} calcId - Calculator ID
         * @param {string} eventType - Event type (view, calculate, etc.)
         * @param {object} data - Additional data
         */
        logEvent: function(calcId, eventType, data) {
            // Prepare payload
            var payload = {
                calculator_id: calcId,
                event_type: eventType,
                data: data || {}
            };

            // Send to API
            fetch('/api/log_event.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(result) {
                if (result.success) {
                    console.log('Event logged:', eventType, 'for calculator', calcId);
                }
            })
            .catch(function(error) {
                console.error('Failed to log event:', error);
            });
        },

        /**
         * Track page view
         */
        trackPageView: function() {
            // If on calculator page, log view event
            if (window.CALCULATOR_ID) {
                this.logEvent(window.CALCULATOR_ID, 'view');
            }
        },

        /**
         * Track calculator usage (when user performs calculation)
         *
         * @param {object} data - Calculation data
         */
        trackCalculation: function(data) {
            if (window.CALCULATOR_ID) {
                this.logEvent(window.CALCULATOR_ID, 'calculate', data);
            }
        },

        /**
         * Track search
         *
         * @param {string} query - Search query
         */
        trackSearch: function(query) {
            this.logEvent(0, 'search', { query: query });
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
     * Utility: Escape HTML
     */
    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    /**
     * Global logEvent function for external use
     */
    window.logEvent = function(calcId, eventType, data) {
        Analytics.logEvent(calcId, eventType, data);
    };

    /**
     * Initialize everything when DOM is ready
     */
    function init() {
        Search.init();
        FAQ.init();
        SmoothScroll.init();
        Analytics.trackPageView();
    }

    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose for external use
    window.CalcHub = {
        Search: Search,
        FAQ: FAQ,
        Analytics: Analytics,
        logEvent: Analytics.logEvent.bind(Analytics)
    };

})();

/**
 * USAGE:
 *
 * Search is automatic - include this file and have search elements.
 *
 * For calculator pages, set these globals before including this script:
 *   window.CALCULATOR_ID = 5;
 *   window.CALCULATOR_SLUG = 'bmi';
 *   window.CALCULATOR_FAQS = [{ question: '...', answer: '...' }];
 *
 * Manual event logging:
 *   logEvent(calculatorId, 'view');
 *   logEvent(calculatorId, 'calculate', { result: 22.5 });
 *
 * Or via CalcHub object:
 *   CalcHub.Analytics.trackCalculation({ bmi: 22.5 });
 *   CalcHub.FAQ.toggle(0);
 */
