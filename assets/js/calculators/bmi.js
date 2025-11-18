/**
 * BMI Calculator
 *
 * Calculates Body Mass Index from height and weight
 * Uses reusable CSS classes from style.css
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

    // BMI Categories
    var categories = {
        underweight: { max: 18.5, label: 'Underweight', status: 'warning', message: 'You are underweight. Consider consulting a healthcare provider about healthy weight gain strategies.' },
        normal: { max: 24.9, label: 'Normal Weight', status: 'success', message: 'Congratulations! You have a healthy weight. Maintain it with a balanced diet and regular exercise.' },
        overweight: { max: 29.9, label: 'Overweight', status: 'warning', message: 'You are overweight. Consider adopting healthier eating habits and increasing physical activity.' },
        obese1: { max: 34.9, label: 'Obese (Class I)', status: 'danger', message: 'You are in the obese category. Consult a healthcare provider for personalized advice on weight management.' },
        obese2: { max: 39.9, label: 'Obese (Class II)', status: 'danger', message: 'You are in the obese category. It is recommended to seek medical advice for a comprehensive weight loss plan.' },
        obese3: { max: Infinity, label: 'Obese (Class III)', status: 'danger', message: 'You are in the severe obesity category. Please consult a healthcare provider immediately for medical guidance.' }
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
            '<form class="calc-form" id="bmiForm" onsubmit="return false;">' +
                // Height and Weight row
                '<div class="calc-row">' +
                    // Height field
                    '<div class="field">' +
                        '<label class="field-label" for="height">Height</label>' +
                        '<div class="field-input-wrapper">' +
                            '<input type="number" class="field-input" id="height" ' +
                                   'placeholder="170" min="50" max="300" step="0.1" required>' +
                            '<span class="field-unit">cm</span>' +
                        '</div>' +
                        '<span class="field-hint">Enter your height in centimeters</span>' +
                    '</div>' +
                    // Weight field
                    '<div class="field">' +
                        '<label class="field-label" for="weight">Weight</label>' +
                        '<div class="field-input-wrapper">' +
                            '<input type="number" class="field-input" id="weight" ' +
                                   'placeholder="70" min="10" max="500" step="0.1" required>' +
                            '<span class="field-unit">kg</span>' +
                        '</div>' +
                        '<span class="field-hint">Enter your weight in kilograms</span>' +
                    '</div>' +
                '</div>' +
                // Action buttons
                '<div class="calc-actions">' +
                    '<button type="button" class="calc-btn" onclick="BMICalculator.calculate()">' +
                        'Calculate BMI' +
                    '</button>' +
                    '<button type="button" class="calc-btn calc-btn-secondary" onclick="BMICalculator.reset()">' +
                        'Reset' +
                    '</button>' +
                '</div>' +
            '</form>';

        container.innerHTML = html;

        // Add enter key handler
        var inputs = container.querySelectorAll('input');
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
     * Calculate BMI
     */
    function calculate() {
        var heightInput = document.getElementById('height');
        var weightInput = document.getElementById('weight');
        var resultContainer = document.getElementById(config.resultId);

        // Validate inputs
        var height = parseFloat(heightInput.value);
        var weight = parseFloat(weightInput.value);

        // Clear previous errors
        clearErrors();

        var hasError = false;

        if (!height || height < 50 || height > 300) {
            showError(heightInput, 'Please enter a valid height (50-300 cm)');
            hasError = true;
        }

        if (!weight || weight < 10 || weight > 500) {
            showError(weightInput, 'Please enter a valid weight (10-500 kg)');
            hasError = true;
        }

        if (hasError) {
            return;
        }

        // Calculate BMI: weight(kg) / height(m)^2
        var heightM = height / 100;
        var bmi = weight / (heightM * heightM);
        var bmiRounded = Math.round(bmi * 10) / 10;

        // Get category
        var category = getCategory(bmi);

        // Display result
        showResult(resultContainer, bmiRounded, category, height, weight);

        // Log analytics event
        if (typeof logEvent === 'function' && window.CALCULATOR_ID) {
            logEvent(window.CALCULATOR_ID, 'calculate', {
                bmi: bmiRounded,
                category: category.label
            });
        }
    }

    /**
     * Get BMI category
     */
    function getCategory(bmi) {
        if (bmi < categories.underweight.max) return categories.underweight;
        if (bmi < categories.normal.max) return categories.normal;
        if (bmi < categories.overweight.max) return categories.overweight;
        if (bmi < categories.obese1.max) return categories.obese1;
        if (bmi < categories.obese2.max) return categories.obese2;
        return categories.obese3;
    }

    /**
     * Show result
     */
    function showResult(container, bmi, category, height, weight) {
        if (!container) return;

        // Calculate ideal weight range (BMI 18.5 - 24.9)
        var heightM = height / 100;
        var idealMin = Math.round(18.5 * heightM * heightM * 10) / 10;
        var idealMax = Math.round(24.9 * heightM * heightM * 10) / 10;

        var html = '' +
            '<div class="calc-output calc-output-' + category.status + '">' +
                '<div class="calc-output-title">Your BMI Result</div>' +
                '<div class="calc-output-main">' +
                    '<span class="calc-output-value">' + bmi + '</span>' +
                    '<span class="calc-output-unit">kg/m²</span>' +
                '</div>' +
                '<div class="calc-output-label">' + category.label + '</div>' +
                '<p class="calc-output-message">' + category.message + '</p>' +
                // Details
                '<div class="calc-details">' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Height</span>' +
                        '<span class="calc-detail-value">' + height + ' cm</span>' +
                    '</div>' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Weight</span>' +
                        '<span class="calc-detail-value">' + weight + ' kg</span>' +
                    '</div>' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Ideal Weight Range</span>' +
                        '<span class="calc-detail-value">' + idealMin + ' - ' + idealMax + ' kg</span>' +
                    '</div>' +
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
        var form = document.getElementById('bmiForm');
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
        var firstInput = document.getElementById('height');
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
    window.BMICalculator = {
        calculate: calculate,
        reset: reset
    };

})();
