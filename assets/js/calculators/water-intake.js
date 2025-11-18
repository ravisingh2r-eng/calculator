/**
 * Water Intake Calculator
 *
 * Calculates recommended daily water intake based on weight,
 * activity level, and climate conditions
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

    // Activity level multipliers
    var activityLevels = {
        sedentary: { label: 'Sedentary (Little or no exercise)', multiplier: 1.0 },
        light: { label: 'Light (Exercise 1-3 days/week)', multiplier: 1.1 },
        moderate: { label: 'Moderate (Exercise 3-5 days/week)', multiplier: 1.2 },
        active: { label: 'Active (Exercise 6-7 days/week)', multiplier: 1.3 },
        veryActive: { label: 'Very Active (Hard exercise daily)', multiplier: 1.4 }
    };

    // Climate multipliers
    var climateTypes = {
        normal: { label: 'Normal/Cool Climate', multiplier: 1.0 },
        hot: { label: 'Hot/Humid Climate', multiplier: 1.2 }
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
            '<form class="calc-form" id="waterForm" onsubmit="return false;">' +
                // Weight
                '<div class="field">' +
                    '<label class="field-label" for="weight">Body Weight</label>' +
                    '<div class="field-input-wrapper">' +
                        '<input type="number" class="field-input" id="weight" ' +
                               'placeholder="70" min="20" max="300" step="0.1" required>' +
                        '<span class="field-unit">kg</span>' +
                    '</div>' +
                    '<span class="field-hint">Enter your body weight in kilograms</span>' +
                '</div>' +
                // Activity Level
                '<div class="field">' +
                    '<label class="field-label" for="activity">Activity Level</label>' +
                    '<select class="field-select" id="activity" required>' +
                        '<option value="">Select activity level</option>' +
                        '<option value="sedentary">' + activityLevels.sedentary.label + '</option>' +
                        '<option value="light">' + activityLevels.light.label + '</option>' +
                        '<option value="moderate" selected>' + activityLevels.moderate.label + '</option>' +
                        '<option value="active">' + activityLevels.active.label + '</option>' +
                        '<option value="veryActive">' + activityLevels.veryActive.label + '</option>' +
                    '</select>' +
                    '<span class="field-hint">How often do you exercise?</span>' +
                '</div>' +
                // Climate
                '<div class="field">' +
                    '<label class="field-label">Climate</label>' +
                    '<div class="field-options">' +
                        '<label class="field-option">' +
                            '<input type="radio" name="climate" value="normal" checked>' +
                            ' Normal/Cool' +
                        '</label>' +
                        '<label class="field-option">' +
                            '<input type="radio" name="climate" value="hot">' +
                            ' Hot/Humid' +
                        '</label>' +
                    '</div>' +
                    '<span class="field-hint">Your typical climate conditions</span>' +
                '</div>' +
                // Action buttons
                '<div class="calc-actions">' +
                    '<button type="button" class="calc-btn" onclick="WaterCalculator.calculate()">' +
                        'Calculate Water Intake' +
                    '</button>' +
                    '<button type="button" class="calc-btn calc-btn-secondary" onclick="WaterCalculator.reset()">' +
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
     * Calculate water intake
     */
    function calculate() {
        var weightInput = document.getElementById('weight');
        var activitySelect = document.getElementById('activity');
        var resultContainer = document.getElementById(config.resultId);

        // Get values
        var weight = parseFloat(weightInput.value);
        var activityKey = activitySelect.value;
        var climateKey = document.querySelector('input[name="climate"]:checked').value;

        // Clear previous errors
        clearErrors();

        var hasError = false;

        // Validate inputs
        if (!weight || weight < 20 || weight > 300) {
            showError(weightInput, 'Please enter a valid weight (20-300 kg)');
            hasError = true;
        }

        if (!activityKey) {
            showError(activitySelect, 'Please select an activity level');
            hasError = true;
        }

        if (hasError) {
            return;
        }

        // Base water intake: 30-35 ml per kg of body weight
        var baseWater = weight * 0.033; // liters (33ml per kg)

        // Apply activity multiplier
        var activityMultiplier = activityLevels[activityKey].multiplier;
        var waterWithActivity = baseWater * activityMultiplier;

        // Apply climate multiplier
        var climateMultiplier = climateTypes[climateKey].multiplier;
        var totalWater = waterWithActivity * climateMultiplier;

        // Round to 1 decimal place
        totalWater = Math.round(totalWater * 10) / 10;

        // Calculate glasses (250ml each)
        var glasses = Math.round(totalWater * 4);

        // Display result
        showResult(resultContainer, {
            liters: totalWater,
            glasses: glasses,
            ml: Math.round(totalWater * 1000),
            weight: weight,
            activity: activityLevels[activityKey].label,
            climate: climateTypes[climateKey].label
        });

        // Log analytics event
        if (typeof logEvent === 'function' && window.CALCULATOR_ID) {
            logEvent(window.CALCULATOR_ID, 'calculate', {
                weight: weight,
                activity: activityKey,
                climate: climateKey,
                liters: totalWater
            });
        }
    }

    /**
     * Show result
     */
    function showResult(container, data) {
        if (!container) return;

        var html = '' +
            '<div class="calc-output calc-output-info">' +
                '<div class="calc-output-title">Recommended Daily Water Intake</div>' +
                '<div class="calc-output-main">' +
                    '<span class="calc-output-value">' + data.liters + '</span>' +
                    '<span class="calc-output-unit">liters/day</span>' +
                '</div>' +
                '<div class="calc-output-label">≈ ' + data.glasses + ' glasses (250ml each)</div>' +
                '<p class="calc-output-message">' +
                    'Based on your weight of ' + data.weight + ' kg, ' +
                    data.activity.toLowerCase() + ' lifestyle, and ' +
                    data.climate.toLowerCase() + ' conditions.' +
                '</p>' +
                // Details
                '<div class="calc-details">' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Daily Intake</span>' +
                        '<span class="calc-detail-value">' + data.ml + ' ml</span>' +
                    '</div>' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Glasses (250ml)</span>' +
                        '<span class="calc-detail-value">' + data.glasses + ' glasses</span>' +
                    '</div>' +
                    '<div class="calc-detail-row">' +
                        '<span class="calc-detail-label">Bottles (500ml)</span>' +
                        '<span class="calc-detail-value">' + Math.round(data.liters * 2) + ' bottles</span>' +
                    '</div>' +
                '</div>' +
                // Tips
                '<div style="margin-top: var(--space-md); padding: var(--space-md); background: var(--accent-soft); border-radius: var(--radius-md); font-size: var(--text-sm);">' +
                    '<strong>Tips:</strong><br>' +
                    '• Drink a glass of water first thing in the morning<br>' +
                    '• Carry a water bottle throughout the day<br>' +
                    '• Drink more during exercise or hot weather<br>' +
                    '• Eat water-rich fruits and vegetables' +
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
        var form = document.getElementById('waterForm');
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
        var firstInput = document.getElementById('weight');
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
    window.WaterCalculator = {
        calculate: calculate,
        reset: reset
    };

})();
