/**
 * EMI Calculator
 *
 * Calculates Equated Monthly Installment for loans
 * Includes Chart.js pie chart visualization
 *
 * @author  Your Name
 * @version 1.0.0
 */

(function() {
    'use strict';

    // Calculator configuration
    var config = {
        formId: 'calculator-form',
        resultId: 'calculator-result',
        chartId: 'calculator-chart'
    };

    // Chart instance
    var chartInstance = null;

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
            '<form class="calc-form" id="emiForm" onsubmit="return false;">' +
                // Principal Amount
                '<div class="field">' +
                    '<label class="field-label" for="principal">Loan Amount (Principal)</label>' +
                    '<div class="field-input-wrapper">' +
                        '<input type="number" class="field-input" id="principal" ' +
                               'placeholder="500000" min="1000" max="100000000" step="1000" required>' +
                        '<span class="field-unit">₹</span>' +
                    '</div>' +
                    '<span class="field-hint">Enter loan amount between ₹1,000 and ₹10 Crore</span>' +
                '</div>' +
                // Interest Rate and Tenure row
                '<div class="calc-row">' +
                    // Interest Rate
                    '<div class="field">' +
                        '<label class="field-label" for="interestRate">Annual Interest Rate</label>' +
                        '<div class="field-input-wrapper">' +
                            '<input type="number" class="field-input" id="interestRate" ' +
                                   'placeholder="8.5" min="0.1" max="50" step="0.1" required>' +
                            '<span class="field-unit">%</span>' +
                        '</div>' +
                        '<span class="field-hint">Annual interest rate (e.g., 8.5%)</span>' +
                    '</div>' +
                    // Tenure
                    '<div class="field">' +
                        '<label class="field-label" for="tenure">Loan Tenure</label>' +
                        '<div class="field-input-wrapper">' +
                            '<input type="number" class="field-input" id="tenure" ' +
                                   'placeholder="20" min="1" max="360" step="1" required>' +
                            '<span class="field-unit" id="tenureUnit">Years</span>' +
                        '</div>' +
                        '<span class="field-hint">Loan period</span>' +
                    '</div>' +
                '</div>' +
                // Tenure Type Toggle
                '<div class="field">' +
                    '<label class="field-label">Tenure Type</label>' +
                    '<div class="field-options">' +
                        '<label class="field-option">' +
                            '<input type="radio" name="tenureType" value="years" checked onchange="EMICalculator.updateTenureUnit()">' +
                            ' Years' +
                        '</label>' +
                        '<label class="field-option">' +
                            '<input type="radio" name="tenureType" value="months" onchange="EMICalculator.updateTenureUnit()">' +
                            ' Months' +
                        '</label>' +
                    '</div>' +
                '</div>' +
                // Action buttons
                '<div class="calc-actions">' +
                    '<button type="button" class="calc-btn" onclick="EMICalculator.calculate()">' +
                        'Calculate EMI' +
                    '</button>' +
                    '<button type="button" class="calc-btn calc-btn-secondary" onclick="EMICalculator.reset()">' +
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
     * Update tenure unit label
     */
    function updateTenureUnit() {
        var tenureType = document.querySelector('input[name="tenureType"]:checked').value;
        var unitLabel = document.getElementById('tenureUnit');
        var tenureInput = document.getElementById('tenure');

        if (tenureType === 'years') {
            unitLabel.textContent = 'Years';
            tenureInput.max = '30';
            tenureInput.placeholder = '20';
        } else {
            unitLabel.textContent = 'Months';
            tenureInput.max = '360';
            tenureInput.placeholder = '240';
        }
    }

    /**
     * Calculate EMI
     */
    function calculate() {
        var principalInput = document.getElementById('principal');
        var interestInput = document.getElementById('interestRate');
        var tenureInput = document.getElementById('tenure');
        var resultContainer = document.getElementById(config.resultId);

        // Get values
        var principal = parseFloat(principalInput.value);
        var annualRate = parseFloat(interestInput.value);
        var tenure = parseFloat(tenureInput.value);
        var tenureType = document.querySelector('input[name="tenureType"]:checked').value;

        // Clear previous errors
        clearErrors();

        var hasError = false;

        // Validate inputs
        if (!principal || principal < 1000 || principal > 100000000) {
            showError(principalInput, 'Please enter a valid amount (₹1,000 - ₹10 Crore)');
            hasError = true;
        }

        if (!annualRate || annualRate < 0.1 || annualRate > 50) {
            showError(interestInput, 'Please enter a valid rate (0.1% - 50%)');
            hasError = true;
        }

        var maxTenure = tenureType === 'years' ? 30 : 360;
        if (!tenure || tenure < 1 || tenure > maxTenure) {
            showError(tenureInput, 'Please enter a valid tenure (1 - ' + maxTenure + ' ' + tenureType + ')');
            hasError = true;
        }

        if (hasError) {
            return;
        }

        // Convert tenure to months
        var tenureMonths = tenureType === 'years' ? tenure * 12 : tenure;

        // Calculate EMI
        // EMI = P × r × (1 + r)^n / ((1 + r)^n - 1)
        var monthlyRate = annualRate / 12 / 100;
        var emi, totalPayment, totalInterest;

        if (monthlyRate === 0) {
            // No interest case
            emi = principal / tenureMonths;
            totalPayment = principal;
            totalInterest = 0;
        } else {
            var compoundFactor = Math.pow(1 + monthlyRate, tenureMonths);
            emi = principal * monthlyRate * compoundFactor / (compoundFactor - 1);
            totalPayment = emi * tenureMonths;
            totalInterest = totalPayment - principal;
        }

        // Display result
        showResult(resultContainer, {
            emi: emi,
            principal: principal,
            totalInterest: totalInterest,
            totalPayment: totalPayment,
            annualRate: annualRate,
            tenureMonths: tenureMonths
        });

        // Create chart
        createChart(principal, totalInterest);

        // Log analytics event
        if (typeof logEvent === 'function' && window.CALCULATOR_ID) {
            logEvent(window.CALCULATOR_ID, 'calculate', {
                principal: principal,
                rate: annualRate,
                tenure: tenureMonths,
                emi: Math.round(emi)
            });
        }
    }

    /**
     * Format number as Indian currency
     */
    function formatCurrency(num) {
        return '₹' + num.toLocaleString('en-IN', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    /**
     * Show result
     */
    function showResult(container, data) {
        if (!container) return;

        var tenureYears = Math.floor(data.tenureMonths / 12);
        var tenureRemMonths = data.tenureMonths % 12;
        var tenureDisplay = '';
        if (tenureYears > 0) {
            tenureDisplay = tenureYears + ' year' + (tenureYears > 1 ? 's' : '');
            if (tenureRemMonths > 0) {
                tenureDisplay += ' ' + tenureRemMonths + ' month' + (tenureRemMonths > 1 ? 's' : '');
            }
        } else {
            tenureDisplay = data.tenureMonths + ' months';
        }

        var html = '' +
            '<div class="calc-output calc-output-info">' +
                '<div class="calc-output-title">Monthly EMI</div>' +
                '<div class="calc-output-main">' +
                    '<span class="calc-output-value">' + formatCurrency(data.emi) + '</span>' +
                    '<span class="calc-output-unit">/month</span>' +
                '</div>' +
                '<p class="calc-output-message">' +
                    'Your monthly EMI for a loan of ' + formatCurrency(data.principal) +
                    ' at ' + data.annualRate + '% interest for ' + tenureDisplay + '.' +
                '</p>' +
                // Details
                '<div class="calc-details">' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Principal Amount</span>' +
                        '<span class="calc-detail-value">' + formatCurrency(data.principal) + '</span>' +
                    '</div>' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Total Interest</span>' +
                        '<span class="calc-detail-value">' + formatCurrency(data.totalInterest) + '</span>' +
                    '</div>' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Total Payment</span>' +
                        '<span class="calc-detail-value" style="font-weight: 700;">' + formatCurrency(data.totalPayment) + '</span>' +
                    '</div>' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Interest Rate</span>' +
                        '<span class="calc-detail-value">' + data.annualRate + '% per annum</span>' +
                    '</div>' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Loan Tenure</span>' +
                        '<span class="calc-detail-value">' + data.tenureMonths + ' months (' + tenureDisplay + ')</span>' +
                    '</div>' +
                '</div>' +
            '</div>';

        container.innerHTML = html;
        container.style.display = 'block';

        // Scroll to result
        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /**
     * Create pie chart
     */
    function createChart(principal, interest) {
        var canvas = document.getElementById(config.chartId);
        if (!canvas) return;

        // Show canvas
        canvas.style.display = 'block';

        // Destroy existing chart
        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }

        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            // Load Chart.js dynamically
            var script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
            script.onload = function() {
                createPieChart(canvas, principal, interest);
            };
            document.head.appendChild(script);
        } else {
            createPieChart(canvas, principal, interest);
        }
    }

    /**
     * Create the actual pie chart
     */
    function createPieChart(canvas, principal, interest) {
        var ctx = canvas.getContext('2d');

        chartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Principal Amount', 'Total Interest'],
                datasets: [{
                    data: [principal, interest],
                    backgroundColor: [
                        '#3498db', // Blue for principal
                        '#e74c3c'  // Red for interest
                    ],
                    borderColor: [
                        '#2980b9',
                        '#c0392b'
                    ],
                    borderWidth: 2,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: {
                                size: 14
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var value = context.raw;
                                var total = principal + interest;
                                var percentage = ((value / total) * 100).toFixed(1);
                                return context.label + ': ' + formatCurrency(value) + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
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
        var form = document.getElementById('emiForm');
        var resultContainer = document.getElementById(config.resultId);
        var canvas = document.getElementById(config.chartId);

        if (form) {
            form.reset();
        }

        if (resultContainer) {
            resultContainer.style.display = 'none';
            resultContainer.innerHTML = '';
        }

        // Destroy and hide chart
        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }

        if (canvas) {
            canvas.style.display = 'none';
        }

        clearErrors();
        updateTenureUnit();

        // Focus first input
        var firstInput = document.getElementById('principal');
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
    window.EMICalculator = {
        calculate: calculate,
        reset: reset,
        updateTenureUnit: updateTenureUnit
    };

})();
