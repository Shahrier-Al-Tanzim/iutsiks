import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Event form enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Registration type change handler
    const registrationTypeSelect = document.getElementById('registration_type');
    const maxParticipantsField = document.getElementById('max_participants');
    const feeAmountField = document.getElementById('fee_amount');
    
    if (registrationTypeSelect) {
        registrationTypeSelect.addEventListener('change', function() {
            const value = this.value;
            
            // Show/hide max participants field based on registration type
            if (maxParticipantsField) {
                const container = maxParticipantsField.closest('div');
                if (value === 'on_spot') {
                    container.style.opacity = '0.5';
                    maxParticipantsField.disabled = true;
                } else {
                    container.style.opacity = '1';
                    maxParticipantsField.disabled = false;
                }
            }
        });
        
        // Trigger change event on page load
        registrationTypeSelect.dispatchEvent(new Event('change'));
    }
    
    // Fee amount formatting
    if (feeAmountField) {
        feeAmountField.addEventListener('blur', function() {
            const value = parseFloat(this.value);
            if (!isNaN(value)) {
                this.value = value.toFixed(2);
            }
        });
    }
    
    // Event date validation
    const eventDateField = document.getElementById('event_date');
    if (eventDateField) {
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        eventDateField.setAttribute('min', today);
    }
    
    // Registration deadline validation
    const registrationDeadlineField = document.getElementById('registration_deadline');
    if (registrationDeadlineField && eventDateField) {
        function updateDeadlineMax() {
            const eventDate = eventDateField.value;
            if (eventDate) {
                registrationDeadlineField.setAttribute('max', eventDate + 'T23:59');
            }
        }
        
        eventDateField.addEventListener('change', updateDeadlineMax);
        updateDeadlineMax();
    }
});
