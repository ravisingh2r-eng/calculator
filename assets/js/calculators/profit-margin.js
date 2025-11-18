/**
 * Profit Margin Calculator
 *
 * Calculates profit, profit percentage, and margin percentage
 * from cost price and selling price
 *
 * @author  Your Name
 * @version 1.0.0
 */

(function() {
    'use strict';

    // Calculator configuration
    var config = {
        formId: 'calculator-form',
        resultId: 'calculator-result'
    };

    /**
     * Initialize calculator
     */
    function init() {
        var formContainer = document.getElementById(config.formId);
        var resultContainer = document.getElementById(config.resultId);

        if (!formContainer) {
            console.error('Calculator form container not found');
            return;
        }

        // Build form
        buildForm(formContainer);

        // Initially hide result
        if (resultContainer) {
            resultContainer.style.display = 'none';
        }
    }

    /**
     * Build calculator form HTML
     */
    function buildForm(container) {
        var html = '' +
            '<form class="calc-form" id="marginForm" onsubmit="return false;">' +
                // Cost Price and Selling Price row
                '<div class="calc-row">' +
                    // Cost Price
                    '<div class="field">' +
                        '<label class="field-label" for="costPrice">Cost Price</label>' +
                        '<div class="field-input-wrapper">' +
                            '<input type="number" class="field-input" id="costPrice" ' +
                                   'placeholder="100" min="0.01" max="999999999" step="0.01" required>' +
                            '<span class="field-unit">₹</span>' +
                        '</div>' +
                        '<span class="field-hint">Original cost of the item</span>' +
                    '</div>' +
                    // Selling Price
                    '<div class="field">' +
                        '<label class="field-label" for="sellingPrice">Selling Price</label>' +
                        '<div class="field-input-wrapper">' +
                            '<input type="number" class="field-input" id="sellingPrice" ' +
                                   'placeholder="150" min="0.01" max="999999999" step="0.01" required>' +
                            '<span class="field-unit">₹</span>' +
                        '</div>' +
                        '<span class="field-hint">Price at which item is sold</span>' +
                    '</div>' +
                '</div>' +
                // Action buttons
                '<div class="calc-actions">' +
                    '<button type="button" class="calc-btn" onclick="ProfitCalculator.calculate()">' +
                        'Calculate Profit' +
                    '</button>' +
                    '<button type="button" class="calc-btn calc-btn-secondary" onclick="ProfitCalculator.reset()">' +
                        'Reset' +
                    '</button>' +
                '</div>' +
            '</form>';

        container.innerHTML = html;

        // Add enter key handler
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

    /**
     * Calculate profit and margins
     */
    function calculate() {
        var costInput = document.getElementById('costPrice');
        var sellingInput = document.getElementById('sellingPrice');
        var resultContainer = document.getElementById(config.resultId);

        // Get values
        var costPrice = parseFloat(costInput.value);
        var sellingPrice = parseFloat(sellingInput.value);

        // Clear previous errors
        clearErrors();

        var hasError = false;

        // Validate inputs
        if (!costPrice || costPrice <= 0) {
            showError(costInput, 'Please enter a valid cost price');
            hasError = true;
        }

        if (!sellingPrice || sellingPrice <= 0) {
            showError(sellingInput, 'Please enter a valid selling price');
            hasError = true;
        }

        if (hasError) {
            return;
        }

        // Calculate profit/loss
        var profit = sellingPrice - costPrice;
        var isProfit = profit >= 0;

        // Profit Percentage = (Profit / Cost Price) × 100
        var profitPercentage = (profit / costPrice) * 100;

        // Margin Percentage = (Profit / Selling Price) × 100
        var marginPercentage = (profit / sellingPrice) * 100;

        // Markup = (Selling Price / Cost Price) × 100
        var markup = (sellingPrice / costPrice) * 100;

        // Display result
        showResult(resultContainer, {
            costPrice: costPrice,
            sellingPrice: sellingPrice,
            profit: profit,
            profitPercentage: profitPercentage,
            marginPercentage: marginPercentage,
            markup: markup,
            isProfit: isProfit
        });

        // Log analytics event
        if (typeof logEvent === 'function' && window.CALCULATOR_ID) {
            logEvent(window.CALCULATOR_ID, 'calculate', {
                cost: costPrice,
                selling: sellingPrice,
                profit: profit,
                margin: marginPercentage.toFixed(2)
            });
        }
    }

    /**
     * Format number as currency
     */
    function formatCurrency(num) {
        var prefix = num < 0 ? '-₹' : '₹';
        return prefix + Math.abs(num).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    /**
     * Format percentage
     */
    function formatPercent(num) {
        var prefix = num < 0 ? '' : '+';
        return prefix + num.toFixed(2) + '%';
    }

    /**
     * Show result
     */
    function showResult(container, data) {
        if (!container) return;

        var statusClass = data.isProfit ? 'calc-output-success' : 'calc-output-danger';
        var statusLabel = data.isProfit ? 'Profit' : 'Loss';
        var statusColor = data.isProfit ? 'var(--success)' : 'var(--danger)';

        var html = '' +
            '<div class="calc-output ' + statusClass + '">' +
                '<div class="calc-output-title">' + statusLabel + ' Analysis</div>' +
                '<div class="calc-output-main">' +
                    '<span class="calc-output-value" style="color: ' + statusColor + ';">' +
                        formatCurrency(data.profit) +
                    '</span>' +
                '</div>' +
                '<div class="calc-output-label">' +
                    formatPercent(data.profitPercentage) + ' ' + statusLabel +
                '</div>' +
                '<p class="calc-output-message">' +
                    (data.isProfit
                        ? 'You make ' + formatCurrency(data.profit) + ' profit on each sale.'
                        : 'You incur ' + formatCurrency(Math.abs(data.profit)) + ' loss on each sale.'
                    ) +
                '</p>' +
                // Details
                '<div class="calc-details">' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Cost Price</span>' +
                        '<span class="calc-detail-value">' + formatCurrency(data.costPrice) + '</span>' +
                    '</div>' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Selling Price</span>' +
                        '<span class="calc-detail-value">' + formatCurrency(data.sellingPrice) + '</span>' +
                    '</div>' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">' + statusLabel + ' Amount</span>' +
                        '<span class="calc-detail-value" style="color: ' + statusColor + ';">' +
                            formatCurrency(data.profit) +
                        '</span>' +
                    '</div>' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">' + statusLabel + ' Percentage</span>' +
                        '<span class="calc-detail-value" style="color: ' + statusColor + ';">' +
                            formatPercent(data.profitPercentage) +
                        '</span>' +
                    '</div>' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Margin Percentage</span>' +
                        '<span class="calc-detail-value">' + data.marginPercentage.toFixed(2) + '%</span>' +
                    '</div>' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Markup</span>' +
                        '<span class="calc-detail-value">' + data.markup.toFixed(2) + '%</span>' +
                    '</div>' +
                '</div>' +
                // Explanation
                '<div style="margin-top: var(--space-md); padding: var(--space-md); background: var(--bg); border-radius: var(--radius-md); font-size: var(--text-sm);">' +
                    '<strong>Formula Definitions:</strong><br>' +
                    '• <strong>Profit %</strong> = (Profit / Cost Price) × 100<br>' +
                    '• <strong>Margin %</strong> = (Profit / Selling Price) × 100<br>' +
                    '• <strong>Markup %</strong> = (Selling Price / Cost Price) × 100' +
                '</div>' +
            '</div>';

        container.innerHTML = html;
        container.style.display = 'block';

        // Scroll to result
        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /**
     * Show field error
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
     * Clear all errors
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

    /**
     * Reset calculator
     */
    function reset() {
        var form = document.getElementById('marginForm');
        var resultContainer = document.getElementById(config.resultId);

        if (form) {
            form.reset();
        }

        if (resultContainer) {
            resultContainer.style.display = 'none';
            resultContainer.innerHTML = '';
        }

        clearErrors();

        // Focus first input
        var firstInput = document.getElementById('costPrice');
        if (firstInput) {
            firstInput.focus();
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose public methods
    window.ProfitCalculator = {
        calculate: calculate,
        reset: reset
    };

})();
