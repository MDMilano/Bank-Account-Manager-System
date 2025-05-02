document.addEventListener('DOMContentLoaded', function() {
    // Form toggle functionality
    const signInForm = document.getElementById('signInForm');
    const signUpForm = document.getElementById('signUpForm');
    const showSignUpBtn = document.getElementById('showSignUpBtn');
    const showSignInBtn = document.getElementById('showSignInBtn');
    
    // Show sign up form
    showSignUpBtn.addEventListener('click', function() {
        signInForm.classList.remove('active');
        setTimeout(() => {
            signInForm.style.display = 'none';
            signUpForm.style.display = 'block';
            setTimeout(() => {
                signUpForm.classList.add('active');
            }, 50);
        }, 300);
    });
    
    // Show sign in form
    showSignInBtn.addEventListener('click', function() {
        signUpForm.classList.remove('active');
        setTimeout(() => {
            signUpForm.style.display = 'none';
            signInForm.style.display = 'block';
            setTimeout(() => {
                signInForm.classList.add('active');
            }, 50);
        }, 300);
    });
    
    // PIN validation functionality
    const pinInput = document.getElementById('pin');
    const confirmPinInput = document.getElementById('confirm_pin');
    const pinFeedback = document.getElementById('pinFeedback');
    const signupButton = document.getElementById('signupButton');
    const createAccountForm = document.getElementById('createAccountForm');
    
    // Function to validate PIN format (4 digits)
    function validatePinFormat(pin) {
        return /^\d{4}$/.test(pin);
    }
    
    // Function to check if PINs match
    function checkPinsMatch() {
        const pin = pinInput.value;
        const confirmPin = confirmPinInput.value;
        
        // Clear feedback if either field is empty
        if (!pin || !confirmPin) {
            pinFeedback.textContent = '';
            pinFeedback.className = 'pin-feedback';
            signupButton.disabled = true;
            return;
        }
        
        // Validate PIN format
        if (!validatePinFormat(pin)) {
            pinFeedback.textContent = 'PIN must be exactly 4 digits';
            pinFeedback.className = 'pin-feedback text-danger small';
            signupButton.disabled = true;
            return;
        }
        
        // Check if PINs match
        if (pin === confirmPin) {
            pinFeedback.textContent = 'PINs match!';
            pinFeedback.className = 'pin-feedback text-success small';
            signupButton.disabled = false;
        } else {
            pinFeedback.textContent = 'PINs do not match';
            pinFeedback.className = 'pin-feedback text-danger small';
            signupButton.disabled = true;
        }
    }
    
    // Real-time validation
    pinInput.addEventListener('input', function() {
        // Restrict to numbers only
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Limit to 4 digits
        if (this.value.length > 4) {
            this.value = this.value.slice(0, 4);
        }
        
        checkPinsMatch();
    });
    
    confirmPinInput.addEventListener('input', function() {
        // Restrict to numbers only
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Limit to 4 digits
        if (this.value.length > 4) {
            this.value = this.value.slice(0, 4);
        }
        
        checkPinsMatch();
    });
    
    // Form submission validation
    createAccountForm.addEventListener('submit', function(event) {
        const pin = pinInput.value;
        const confirmPin = confirmPinInput.value;
        
        if (!validatePinFormat(pin)) {
            event.preventDefault();
            pinFeedback.textContent = 'PIN must be exactly 4 digits';
            pinFeedback.className = 'pin-feedback text-danger small';
            return;
        }
        
        if (pin !== confirmPin) {
            event.preventDefault();
            pinFeedback.textContent = 'PINs do not match';
            pinFeedback.className = 'pin-feedback text-danger small';
            return;
        }
    });
    
    // Add input masking for PIN fields
    const pinInputs = document.querySelectorAll('.pin-input');
    pinInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            // Allow only numbers
            if (e.key < '0' || e.key > '9') {
                e.preventDefault();
            }
        });
    });
});