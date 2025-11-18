/**
 * Calculator Template
 *
 * A reusable template for creating new calculators.
 * Copy this file and modify for your specific calculator.
 *
 * ============================================================================
 * HOW TO USE THIS TEMPLATE:
 * ============================================================================
 *
 * 1. Copy this file and rename it (e.g., my-calculator.js)
 *
 * 2. Update the calculator name:
 *    - Change "TemplateCalculator" to "MyCalculator" everywhere
 *    - Update the form ID from "templateForm" to "myForm"
 *
 * 3. Define your input fields in buildForm():
 *    - Use .field, .field-input, .field-select, .field-options classes
 *    - See existing calculators for examples
 *
 * 4. Implement your calculation logic in calculate():
 *    - Get input values
 *    - Validate them
 *    - Perform calculations
 *    - Call showResult() with your data
 *
 * 5. Customize showResult() to display your specific output
 *
 * 6. In admin panel, set js_file to your new file path
 *
 * ============================================================================
 * AVAILABLE CSS CLASSES (from style.css):
 * ============================================================================
 *
 * Form Structure:
 *   .calc-form      - Form container
 *   .calc-row       - Two column row
 *   .calc-row-3     - Three column row
 *   .calc-actions   - Button container
 *
 * Fields:
 *   .field              - Field wrapper
 *   .field-label        - Label text
 *   .field-input        - Text/number input
 *   .field-input-wrapper - For inputs with unit suffix
 *   .field-unit         - Unit suffix (kg, %, etc.)
 *   .field-select       - Dropdown select
 *   .field-options      - Radio/checkbox group
 *   .field-option       - Single radio/checkbox item
 *   .field-hint         - Help text
 *   .field-error        - Error state
 *
 * Buttons:
 *   .calc-btn           - Primary button
 *   .calc-btn-secondary - Secondary button
 *
 * Results:
 *   .calc-output         - Result container
 *   .calc-output-success - Green left border
 *   .calc-output-warning - Yellow left border
 *   .calc-output-danger  - Red left border
 *   .calc-output-info    - Blue left border
 *   .calc-output-title   - Small title
 *   .calc-output-main    - Main value container
 *   .calc-output-value   - Large number
 *   .calc-output-unit    - Unit next to value
 *   .calc-output-label   - Category/label
 *   .calc-output-message - Description text
 *   .calc-details        - Details table
 *   .calc-detail-row     - Detail row
 *   .calc-detail-label   - Detail label
 *   .calc-detail-value   - Detail value
 *
 * ============================================================================
 *
 * @author  Your Name
 * @version 1.0.0
 */

(function() {
    'use strict';

    // ========================================================================
    // CONFIGURATION
    // ========================================================================

    var config = {
        formId: 'calculator-form',      // Form container ID (from calculator.php)
        resultId: 'calculator-result',  // Result container ID (from calculator.php)
        chartId: 'calculator-chart'     // Chart canvas ID (optional)
    };

    // TODO: Define any constants or lookup tables here
    // Example:
    // var CONSTANTS = {
    //     PI: 3.14159,
    //     GRAVITY: 9.8
    // };

    // ========================================================================
    // INITIALIZATION
    // ========================================================================

    /**
     * Initialize calculator
     * Called when DOM is ready
     */
    function init() {
        var formContainer = document.getElementById(config.formId);
        var resultContainer = document.getElementById(config.resultId);

        if (!formContainer) {
            console.error('Calculator form container not found');
            return;
        }

        // Build the form
        buildForm(formContainer);

        // Initially hide result
        if (resultContainer) {
            resultContainer.style.display = 'none';
        }
    }

    // ========================================================================
    // FORM BUILDING
    // ========================================================================

    /**
     * Build calculator form HTML
     *
     * TODO: Customize this for your calculator's inputs
     *
     * @param {HTMLElement} container - The form container element
     */
    function buildForm(container) {
        var html = '' +
            '<form class="calc-form" id="templateForm" onsubmit="return false;">' +

                // TODO: Add your input fields here
                // Example: Single field
                '<div class="field">' +
                    '<label class="field-label" for="input1">Input Label</label>' +
                    '<div class="field-input-wrapper">' +
                        '<input type="number" class="field-input" id="input1" ' +
                               'placeholder="0" min="0" max="1000" step="1" required>' +
                        '<span class="field-unit">unit</span>' +
                    '</div>' +
                    '<span class="field-hint">Help text for this field</span>' +
                '</div>' +

                // Example: Two fields side by side
                '<div class="calc-row">' +
                    '<div class="field">' +
                        '<label class="field-label" for="input2">Field 2</label>' +
                        '<input type="number" class="field-input" id="input2" ' +
                               'placeholder="0" required>' +
                    '</div>' +
                    '<div class="field">' +
                        '<label class="field-label" for="input3">Field 3</label>' +
                        '<input type="number" class="field-input" id="input3" ' +
                               'placeholder="0" required>' +
                    '</div>' +
                '</div>' +

                // Example: Select dropdown
                '<div class="field">' +
                    '<label class="field-label" for="selectInput">Select Option</label>' +
                    '<select class="field-select" id="selectInput">' +
                        '<option value="option1">Option 1</option>' +
                        '<option value="option2">Option 2</option>' +
                    '</select>' +
                '</div>' +

                // Example: Radio options
                '<div class="field">' +
                    '<label class="field-label">Choose One</label>' +
                    '<div class="field-options">' +
                        '<label class="field-option">' +
                            '<input type="radio" name="radioOption" value="a" checked> Option A' +
                        '</label>' +
                        '<label class="field-option">' +
                            '<input type="radio" name="radioOption" value="b"> Option B' +
                        '</label>' +
                    '</div>' +
                '</div>' +

                // Action buttons
                '<div class="calc-actions">' +
                    '<button type="button" class="calc-btn" onclick="TemplateCalculator.calculate()">' +
                        'Calculate' +
                    '</button>' +
                    '<button type="button" class="calc-btn calc-btn-secondary" onclick="TemplateCalculator.reset()">' +
                        'Reset' +
                    '</button>' +
                '</div>' +
            '</form>';

        container.innerHTML = html;

        // Add enter key handler to all number inputs
        var inputs = container.querySelectorAll('input[type="number"]');
        inputs.forEach(function(input) {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    calculate();
                }
            });
        });
    }

    // ========================================================================
    // CALCULATION
    // ========================================================================

    /**
     * Main calculation function
     *
     * TODO: Implement your calculation logic here
     */
    function calculate() {
        var resultContainer = document.getElementById(config.resultId);

        // Clear previous errors
        clearErrors();

        // ====================================================================
        // STEP 1: Get input values
        // ====================================================================

        var input1 = parseNumber(document.getElementById('input1').value);
        var input2 = parseNumber(document.getElementById('input2').value);
        var input3 = parseNumber(document.getElementById('input3').value);
        var selectValue = document.getElementById('selectInput').value;
        var radioValue = document.querySelector('input[name="radioOption"]:checked').value;

        // ====================================================================
        // STEP 2: Validate inputs
        // ====================================================================

        var hasError = false;

        if (isNaN(input1) || input1 <= 0) {
            showError(document.getElementById('input1'), 'Please enter a valid value');
            hasError = true;
        }

        if (isNaN(input2) || input2 <= 0) {
            showError(document.getElementById('input2'), 'Please enter a valid value');
            hasError = true;
        }

        if (hasError) {
            return;
        }

        // ====================================================================
        // STEP 3: Perform calculations
        // ====================================================================

        // TODO: Add your calculation logic here
        // Example:
        var result = input1 + input2 + input3;
        var percentage = (input1 / result) * 100;

        // ====================================================================
        // STEP 4: Display result
        // ====================================================================

        showResult(resultContainer, {
            input1: input1,
            input2: input2,
            input3: input3,
            result: result,
            percentage: percentage
        });

        // ====================================================================
        // STEP 5: Log analytics event
        // ====================================================================

        if (typeof logEvent === 'function' && window.CALCULATOR_ID) {
            logEvent(window.CALCULATOR_ID, 'calculate', {
                // TODO: Add relevant data to track
                result: result
            });
        }
    }

    // ========================================================================
    // RESULT DISPLAY
    // ========================================================================

    /**
     * Display calculation result
     *
     * TODO: Customize for your result display
     *
     * @param {HTMLElement} container - Result container element
     * @param {Object} data - Calculation results
     */
    function showResult(container, data) {
        if (!container) return;

        // TODO: Customize this HTML for your result display
        var html = '' +
            '<div class="calc-output calc-output-success">' +
                '<div class="calc-output-title">Result</div>' +
                '<div class="calc-output-main">' +
                    '<span class="calc-output-value">' + formatNumber(data.result) + '</span>' +
                    '<span class="calc-output-unit">unit</span>' +
                '</div>' +
                '<div class="calc-output-label">Result Label</div>' +
                '<p class="calc-output-message">' +
                    'Your result description here.' +
                '</p>' +
                // Details table
                '<div class="calc-details">' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Input 1</span>' +
                        '<span class="calc-detail-value">' + formatNumber(data.input1) + '</span>' +
                    '</div>' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Input 2</span>' +
                        '<span class="calc-detail-value">' + formatNumber(data.input2) + '</span>' +
                    '</div>' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Percentage</span>' +
                        '<span class="calc-detail-value">' + data.percentage.toFixed(2) + '%</span>' +
                    '</div>' +
                '</div>' +
            '</div>';

        container.innerHTML = html;
        container.style.display = 'block';

        // Scroll to result
        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // ========================================================================
    // HELPER FUNCTIONS
    // ========================================================================

    /**
     * Parse a string to number
     *
     * @param {string} value - Input value
     * @returns {number} Parsed number or NaN
     */
    function parseNumber(value) {
        if (value === '' || value === null || value === undefined) {
            return NaN;
        }
        return parseFloat(value);
    }

    /**
     * Format number with locale formatting
     *
     * @param {number} num - Number to format
     * @param {number} decimals - Decimal places (default: 2)
     * @returns {string} Formatted number
     */
    function formatNumber(num, decimals) {
        if (typeof decimals === 'undefined') decimals = 2;
        return num.toLocaleString('en-IN', {
            minimumFractionDigits: 0,
            maximumFractionDigits: decimals
        });
    }

    /**
     * Format number as Indian currency
     *
     * @param {number} num - Number to format
     * @returns {string} Formatted currency
     */
    function formatCurrency(num) {
        return '₹' + formatNumber(num, 0);
    }

    /**
     * Format number as percentage
     *
     * @param {number} num - Number to format
     * @returns {string} Formatted percentage
     */
    function formatPercent(num) {
        return num.toFixed(2) + '%';
    }

    /**
     * Show field error
     *
     * @param {HTMLElement} input - Input element
     * @param {string} message - Error message
     */
    function showError(input, message) {
        var field = input.closest('.field');
        if (field) {
            field.classList.add('field-error');
            var errorEl = document.createElement('span');
            errorEl.className = 'field-error-message';
            errorEl.textContent = message;
            field.appendChild(errorEl);
        }
        input.focus();
    }

    /**
     * Clear all field errors
     */
    function clearErrors() {
        var errors = document.querySelectorAll('.field-error');
        errors.forEach(function(field) {
            field.classList.remove('field-error');
            var errorMsg = field.querySelector('.field-error-message');
            if (errorMsg) {
                errorMsg.remove();
            }
        });
    }

    // ========================================================================
    // RESET
    // ========================================================================

    /**
     * Reset calculator to initial state
     */
    function reset() {
        var form = document.getElementById('templateForm');
        var resultContainer = document.getElementById(config.resultId);
        var canvas = document.getElementById(config.chartId);

        // Reset form
        if (form) {
            form.reset();
        }

        // Hide result
        if (resultContainer) {
            resultContainer.style.display = 'none';
            resultContainer.innerHTML = '';
        }

        // Hide chart (if using Chart.js)
        if (canvas) {
            canvas.style.display = 'none';
        }

        // Clear errors
        clearErrors();

        // Focus first input
        var firstInput = form ? form.querySelector('input') : null;
        if (firstInput) {
            firstInput.focus();
        }
    }

    // ========================================================================
    // CHART (Optional - uncomment if needed)
    // ========================================================================

    /*
    var chartInstance = null;

    function createChart(data) {
        var canvas = document.getElementById(config.chartId);
        if (!canvas) return;

        canvas.style.display = 'block';

        // Destroy existing chart
        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }

        // Load Chart.js if needed
        if (typeof Chart === 'undefined') {
            var script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
            script.onload = function() {
                renderChart(canvas, data);
            };
            document.head.appendChild(script);
        } else {
            renderChart(canvas, data);
        }
    }

    function renderChart(canvas, data) {
        var ctx = canvas.getContext('2d');

        chartInstance = new Chart(ctx, {
            type: 'doughnut', // or 'bar', 'line', 'pie'
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.values,
                    backgroundColor: ['#3498db', '#e74c3c', '#27ae60'],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
    */

    // ========================================================================
    // INITIALIZATION
    // ========================================================================

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose public methods
    // TODO: Rename "TemplateCalculator" to your calculator name
    window.TemplateCalculator = {
        calculate: calculate,
        reset: reset
    };

})();
