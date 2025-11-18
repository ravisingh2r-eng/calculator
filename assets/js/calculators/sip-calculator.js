/**
 * SIP (Systematic Investment Plan) Calculator
 *
 * Calculates future value of regular monthly investments
 * with compound interest and year-wise growth chart
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
            '<form class="calc-form" id="sipForm" onsubmit="return false;">' +
                // Monthly Investment
                '<div class="field">' +
                    '<label class="field-label" for="monthlyInvestment">Monthly Investment</label>' +
                    '<div class="field-input-wrapper">' +
                        '<input type="number" class="field-input" id="monthlyInvestment" ' +
                               'placeholder="5000" min="100" max="10000000" step="100" required>' +
                        '<span class="field-unit">₹</span>' +
                    '</div>' +
                    '<span class="field-hint">Amount you invest every month</span>' +
                '</div>' +
                // Expected Return Rate and Time Period row
                '<div class="calc-row">' +
                    // Expected Return Rate
                    '<div class="field">' +
                        '<label class="field-label" for="returnRate">Expected Return Rate</label>' +
                        '<div class="field-input-wrapper">' +
                            '<input type="number" class="field-input" id="returnRate" ' +
                                   'placeholder="12" min="1" max="50" step="0.1" required>' +
                            '<span class="field-unit">%</span>' +
                        '</div>' +
                        '<span class="field-hint">Annual return rate (e.g., 12%)</span>' +
                    '</div>' +
                    // Time Period
                    '<div class="field">' +
                        '<label class="field-label" for="timePeriod">Time Period</label>' +
                        '<div class="field-input-wrapper">' +
                            '<input type="number" class="field-input" id="timePeriod" ' +
                                   'placeholder="10" min="1" max="40" step="1" required>' +
                            '<span class="field-unit">Years</span>' +
                        '</div>' +
                        '<span class="field-hint">Investment duration (1-40 years)</span>' +
                    '</div>' +
                '</div>' +
                // Action buttons
                '<div class="calc-actions">' +
                    '<button type="button" class="calc-btn" onclick="SIPCalculator.calculate()">' +
                        'Calculate Returns' +
                    '</button>' +
                    '<button type="button" class="calc-btn calc-btn-secondary" onclick="SIPCalculator.reset()">' +
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
     * Calculate SIP returns
     */
    function calculate() {
        var monthlyInput = document.getElementById('monthlyInvestment');
        var rateInput = document.getElementById('returnRate');
        var timeInput = document.getElementById('timePeriod');
        var resultContainer = document.getElementById(config.resultId);

        // Get values
        var monthlyInvestment = parseFloat(monthlyInput.value);
        var annualRate = parseFloat(rateInput.value);
        var years = parseInt(timeInput.value, 10);

        // Clear previous errors
        clearErrors();

        var hasError = false;

        // Validate inputs
        if (!monthlyInvestment || monthlyInvestment < 100 || monthlyInvestment > 10000000) {
            showError(monthlyInput, 'Please enter a valid amount (₹100 - ₹1 Crore)');
            hasError = true;
        }

        if (!annualRate || annualRate < 1 || annualRate > 50) {
            showError(rateInput, 'Please enter a valid rate (1% - 50%)');
            hasError = true;
        }

        if (!years || years < 1 || years > 40) {
            showError(timeInput, 'Please enter a valid period (1-40 years)');
            hasError = true;
        }

        if (hasError) {
            return;
        }

        // Calculate SIP future value
        // FV = P × [{(1 + r)^n – 1} / r] × (1 + r)
        var monthlyRate = annualRate / 12 / 100;
        var months = years * 12;

        var futureValue = monthlyInvestment *
            (((Math.pow(1 + monthlyRate, months) - 1) / monthlyRate) * (1 + monthlyRate));

        var investedAmount = monthlyInvestment * months;
        var estimatedReturns = futureValue - investedAmount;

        // Calculate year-wise data for chart
        var yearlyData = calculateYearlyGrowth(monthlyInvestment, monthlyRate, years);

        // Display result
        showResult(resultContainer, {
            monthlyInvestment: monthlyInvestment,
            annualRate: annualRate,
            years: years,
            investedAmount: investedAmount,
            estimatedReturns: estimatedReturns,
            futureValue: futureValue
        });

        // Create chart
        createChart(yearlyData);

        // Log analytics event
        if (typeof logEvent === 'function' && window.CALCULATOR_ID) {
            logEvent(window.CALCULATOR_ID, 'calculate', {
                monthly: monthlyInvestment,
                rate: annualRate,
                years: years,
                futureValue: Math.round(futureValue)
            });
        }
    }

    /**
     * Calculate year-wise growth data
     */
    function calculateYearlyGrowth(monthlyInvestment, monthlyRate, years) {
        var data = {
            labels: [],
            invested: [],
            value: []
        };

        for (var year = 1; year <= years; year++) {
            var months = year * 12;
            var invested = monthlyInvestment * months;
            var value = monthlyInvestment *
                (((Math.pow(1 + monthlyRate, months) - 1) / monthlyRate) * (1 + monthlyRate));

            data.labels.push('Year ' + year);
            data.invested.push(Math.round(invested));
            data.value.push(Math.round(value));
        }

        return data;
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

        var wealthGain = ((data.estimatedReturns / data.investedAmount) * 100).toFixed(1);

        var html = '' +
            '<div class="calc-output calc-output-success">' +
                '<div class="calc-output-title">SIP Investment Summary</div>' +
                '<div class="calc-output-main">' +
                    '<span class="calc-output-value">' + formatCurrency(data.futureValue) + '</span>' +
                '</div>' +
                '<div class="calc-output-label">Total Future Value</div>' +
                '<p class="calc-output-message">' +
                    'Investing ' + formatCurrency(data.monthlyInvestment) + ' monthly for ' +
                    data.years + ' years at ' + data.annualRate + '% annual returns.' +
                '</p>' +
                // Details
                '<div class="calc-details">' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Total Invested</span>' +
                        '<span class="calc-detail-value">' + formatCurrency(data.investedAmount) + '</span>' +
                    '</div>' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Estimated Returns</span>' +
                        '<span class="calc-detail-value" style="color: var(--success);">' + formatCurrency(data.estimatedReturns) + '</span>' +
                    '</div>' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Wealth Gain</span>' +
                        '<span class="calc-detail-value">' + wealthGain + '%</span>' +
                    '</div>' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Monthly SIP</span>' +
                        '<span class="calc-detail-value">' + formatCurrency(data.monthlyInvestment) + '</span>' +
                    '</div>' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Investment Period</span>' +
                        '<span class="calc-detail-value">' + data.years + ' years (' + (data.years * 12) + ' months)</span>' +
                    '</div>' +
                '</div>' +
            '</div>';

        container.innerHTML = html;
        container.style.display = 'block';

        // Scroll to result
        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /**
     * Create growth chart
     */
    function createChart(data) {
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
                createBarChart(canvas, data);
            };
            document.head.appendChild(script);
        } else {
            createBarChart(canvas, data);
        }
    }

    /**
     * Create the actual bar chart
     */
    function createBarChart(canvas, data) {
        var ctx = canvas.getContext('2d');

        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Invested Amount',
                        data: data.invested,
                        backgroundColor: '#3498db',
                        borderColor: '#2980b9',
                        borderWidth: 1
                    },
                    {
                        label: 'Total Value',
                        data: data.value,
                        backgroundColor: '#27ae60',
                        borderColor: '#1e8449',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 10000000) {
                                    return '₹' + (value / 10000000).toFixed(1) + ' Cr';
                                } else if (value >= 100000) {
                                    return '₹' + (value / 100000).toFixed(1) + ' L';
                                } else if (value >= 1000) {
                                    return '₹' + (value / 1000).toFixed(0) + ' K';
                                }
                                return '₹' + value;
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatCurrency(context.raw);
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
        var form = document.getElementById('sipForm');
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

        // Focus first input
        var firstInput = document.getElementById('monthlyInvestment');
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
    window.SIPCalculator = {
        calculate: calculate,
        reset: reset
    };

})();
