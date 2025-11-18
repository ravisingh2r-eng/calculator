/**
 * Sleep Cycle Calculator
 *
 * Calculates ideal sleep and wake times based on 90-minute
 * sleep cycles for optimal rest
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

    // Sleep cycle duration in minutes
    var CYCLE_DURATION = 90;

    // Time to fall asleep (minutes)
    var FALL_ASLEEP_TIME = 15;

    // Recommended cycles
    var RECOMMENDED_CYCLES = [5, 6]; // 7.5 - 9 hours

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
        // Get current time for default value
        var now = new Date();
        var hours = String(now.getHours()).padStart(2, '0');
        var minutes = String(now.getMinutes()).padStart(2, '0');
        var currentTime = hours + ':' + minutes;

        var html = '' +
            '<form class="calc-form" id="sleepForm" onsubmit="return false;">' +
                // Calculation Mode
                '<div class="field">' +
                    '<label class="field-label">I want to calculate</label>' +
                    '<div class="field-options">' +
                        '<label class="field-option">' +
                            '<input type="radio" name="mode" value="wakeup" checked onchange="SleepCalculator.updateMode()">' +
                            ' Wake-up time' +
                        '</label>' +
                        '<label class="field-option">' +
                            '<input type="radio" name="mode" value="bedtime" onchange="SleepCalculator.updateMode()">' +
                            ' Bedtime' +
                        '</label>' +
                    '</div>' +
                '</div>' +
                // Time input
                '<div class="field">' +
                    '<label class="field-label" for="timeInput" id="timeLabel">If I go to bed at</label>' +
                    '<input type="time" class="field-input" id="timeInput" value="' + currentTime + '" required>' +
                    '<span class="field-hint" id="timeHint">Enter the time you plan to go to bed</span>' +
                '</div>' +
                // Action buttons
                '<div class="calc-actions">' +
                    '<button type="button" class="calc-btn" onclick="SleepCalculator.calculate()">' +
                        'Calculate Sleep Times' +
                    '</button>' +
                    '<button type="button" class="calc-btn calc-btn-secondary" onclick="SleepCalculator.reset()">' +
                        'Reset' +
                    '</button>' +
                '</div>' +
            '</form>';

        container.innerHTML = html;

        // Add enter key handler
        var timeInput = container.querySelector('#timeInput');
        if (timeInput) {
            timeInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    calculate();
                }
            });
        }
    }

    /**
     * Update mode labels
     */
    function updateMode() {
        var mode = document.querySelector('input[name="mode"]:checked').value;
        var label = document.getElementById('timeLabel');
        var hint = document.getElementById('timeHint');

        if (mode === 'wakeup') {
            label.textContent = 'If I go to bed at';
            hint.textContent = 'Enter the time you plan to go to bed';
        } else {
            label.textContent = 'If I need to wake up at';
            hint.textContent = 'Enter the time you need to wake up';
        }
    }

    /**
     * Calculate sleep cycles
     */
    function calculate() {
        var timeInput = document.getElementById('timeInput');
        var resultContainer = document.getElementById(config.resultId);
        var mode = document.querySelector('input[name="mode"]:checked').value;

        // Get time value
        var timeValue = timeInput.value;

        // Clear previous errors
        clearErrors();

        if (!timeValue) {
            showError(timeInput, 'Please enter a time');
            return;
        }

        // Parse time
        var timeParts = timeValue.split(':');
        var hours = parseInt(timeParts[0], 10);
        var minutes = parseInt(timeParts[1], 10);

        // Create base date
        var baseDate = new Date();
        baseDate.setHours(hours, minutes, 0, 0);

        var times = [];

        if (mode === 'wakeup') {
            // Calculate wake-up times from bedtime
            // Add fall asleep time first
            var sleepTime = new Date(baseDate.getTime() + FALL_ASLEEP_TIME * 60000);

            // Calculate 6 cycles (3 to 6 recommended)
            for (var i = 3; i <= 6; i++) {
                var wakeTime = new Date(sleepTime.getTime() + (i * CYCLE_DURATION * 60000));
                var sleepHours = (i * CYCLE_DURATION) / 60;
                times.push({
                    time: wakeTime,
                    cycles: i,
                    hours: sleepHours,
                    recommended: RECOMMENDED_CYCLES.indexOf(i) !== -1
                });
            }
        } else {
            // Calculate bedtimes from wake-up time
            // Work backwards
            for (var i = 6; i >= 3; i--) {
                var totalMinutes = (i * CYCLE_DURATION) + FALL_ASLEEP_TIME;
                var bedTime = new Date(baseDate.getTime() - (totalMinutes * 60000));
                var sleepHours = (i * CYCLE_DURATION) / 60;
                times.push({
                    time: bedTime,
                    cycles: i,
                    hours: sleepHours,
                    recommended: RECOMMENDED_CYCLES.indexOf(i) !== -1
                });
            }
        }

        // Display result
        showResult(resultContainer, {
            mode: mode,
            inputTime: formatTime(baseDate),
            times: times
        });

        // Log analytics event
        if (typeof logEvent === 'function' && window.CALCULATOR_ID) {
            logEvent(window.CALCULATOR_ID, 'calculate', {
                mode: mode,
                inputTime: timeValue
            });
        }
    }

    /**
     * Format time as 12-hour with AM/PM
     */
    function formatTime(date) {
        var hours = date.getHours();
        var minutes = date.getMinutes();
        var ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // 0 becomes 12
        var minutesStr = minutes < 10 ? '0' + minutes : minutes;
        return hours + ':' + minutesStr + ' ' + ampm;
    }

    /**
     * Show result
     */
    function showResult(container, data) {
        if (!container) return;

        var modeText = data.mode === 'wakeup' ? 'wake up at' : 'go to bed at';
        var inputLabel = data.mode === 'wakeup' ? 'Bedtime' : 'Wake-up time';

        var timesHtml = '';
        data.times.forEach(function(item) {
            var statusClass = item.recommended ? 'calc-output-success' : '';
            var badge = item.recommended ? '<span style="background: var(--success); color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; margin-left: 8px;">IDEAL</span>' : '';

            timesHtml += '' +
                '<div class="calc-detail-row ' + statusClass + '" style="padding: var(--space-md); border-radius: var(--radius-sm); margin-bottom: var(--space-sm); background: ' + (item.recommended ? 'var(--success-soft)' : 'var(--bg)') + ';">' +
                    '<div>' +
                        '<span style="font-size: var(--text-lg); font-weight: 700;">' + formatTime(item.time) + '</span>' +
                        badge +
                        '<br>' +
                        '<span style="font-size: var(--text-sm); color: var(--muted);">' +
                            item.cycles + ' cycles • ' + item.hours + ' hours of sleep' +
                        '</span>' +
                    '</div>' +
                '</div>';
        });

        var html = '' +
            '<div class="calc-output">' +
                '<div class="calc-output-title">Recommended ' + (data.mode === 'wakeup' ? 'Wake-up' : 'Bed') + ' Times</div>' +
                '<p class="calc-output-message" style="margin-bottom: var(--space-lg);">' +
                    'Based on ' + inputLabel.toLowerCase() + ' at <strong>' + data.inputTime + '</strong>, ' +
                    'here are the ideal times to ' + modeText + ' for complete sleep cycles:' +
                '</p>' +
                // Time options
                '<div style="margin-bottom: var(--space-md);">' +
                    timesHtml +
                '</div>' +
                // Info box
                '<div style="padding: var(--space-md); background: var(--info-soft); border-radius: var(--radius-md); font-size: var(--text-sm);">' +
                    '<strong>How it works:</strong><br>' +
                    '• Each sleep cycle lasts about 90 minutes<br>' +
                    '• Waking between cycles helps you feel refreshed<br>' +
                    '• Adults typically need 5-6 cycles (7.5-9 hours)<br>' +
                    '• Calculation includes ~15 minutes to fall asleep' +
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
        var form = document.getElementById('sleepForm');
        var resultContainer = document.getElementById(config.resultId);

        if (form) {
            form.reset();
        }

        if (resultContainer) {
            resultContainer.style.display = 'none';
            resultContainer.innerHTML = '';
        }

        clearErrors();
        updateMode();

        // Focus time input
        var timeInput = document.getElementById('timeInput');
        if (timeInput) {
            timeInput.focus();
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose public methods
    window.SleepCalculator = {
        calculate: calculate,
        reset: reset,
        updateMode: updateMode
    };

})();
