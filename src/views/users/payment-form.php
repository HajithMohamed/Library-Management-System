<?php
$pageTitle = 'Pay Fine';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-credit-card"></i> Payment Details
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Payment Summary -->
                    <div class="alert alert-info">
                        <h5>Payment Summary</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Book:</strong> <?php echo htmlspecialchars($transaction['bookName'] ?? 'N/A'); ?>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <strong>Transaction ID:</strong> #<?php echo htmlspecialchars($transaction['tid']); ?>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <strong>Fine Amount:</strong> ₹<?php echo number_format($amount, 2); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Select Payment Method</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="payment_method_selector" id="credit_card" value="credit_card" checked>
                            <label class="btn btn-outline-primary" for="credit_card">
                                <i class="fas fa-credit-card"></i> Credit Card
                            </label>
                            
                            <input type="radio" class="btn-check" name="payment_method_selector" id="debit_card" value="debit_card">
                            <label class="btn btn-outline-primary" for="debit_card">
                                <i class="fas fa-credit-card"></i> Debit Card
                            </label>
                            
                            <input type="radio" class="btn-check" name="payment_method_selector" id="upi" value="upi">
                            <label class="btn btn-outline-primary" for="upi">
                                <i class="fas fa-mobile-alt"></i> UPI
                            </label>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <form id="paymentForm" method="POST" action="<?php echo BASE_URL; ?>user/payFine">
                        <input type="hidden" name="transaction_id" value="<?php echo htmlspecialchars($transaction['tid']); ?>">
                        <input type="hidden" name="borrow_id" value="<?php echo htmlspecialchars($transaction['tid']); ?>">
                        <input type="hidden" name="amount" value="<?php echo htmlspecialchars($amount); ?>">
                        <input type="hidden" name="payment_method" id="payment_method" value="credit_card">

                        <!-- Card Details Section -->
                        <div id="card_details">
                            <div class="mb-3">
                                <label for="card_name" class="form-label">Cardholder Name *</label>
                                <input type="text" class="form-control" id="card_name" name="card_name" 
                                       placeholder="John Doe" required>
                                <div class="invalid-feedback">Please enter cardholder name</div>
                            </div>

                            <div class="mb-3">
                                <label for="card_number" class="form-label">Card Number *</label>
                                <input type="text" class="form-control" id="card_number" name="card_number" 
                                       placeholder="1234 5678 9012 3456" maxlength="19" required>
                                <div class="invalid-feedback">Please enter a valid card number</div>
                                <small class="text-muted">We accept Visa, MasterCard, and RuPay</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="expiry_date" class="form-label">Expiry Date *</label>
                                    <input type="text" class="form-control" id="expiry_date" name="expiry_date" 
                                           placeholder="MM/YY" maxlength="5" required>
                                    <div class="invalid-feedback">Please enter valid expiry date (MM/YY)</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="cvv" class="form-label">CVV *</label>
                                    <input type="text" class="form-control" id="cvv" name="cvv" 
                                           placeholder="123" maxlength="4" required>
                                    <div class="invalid-feedback">Please enter valid CVV (3-4 digits)</div>
                                </div>
                            </div>
                        </div>

                        <!-- UPI Details Section (Hidden by default) -->
                        <div id="upi_details" style="display: none;">
                            <div class="mb-3">
                                <label for="upi_id" class="form-label">UPI ID *</label>
                                <input type="text" class="form-control" id="upi_id" name="upi_id" 
                                       placeholder="username@upi">
                                <div class="invalid-feedback">Please enter a valid UPI ID</div>
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the terms and conditions and authorize this payment
                            </label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg" id="payBtn">
                                <i class="fas fa-lock"></i> Pay ₹<?php echo number_format($amount, 2); ?>
                            </button>
                            <a href="<?php echo BASE_URL; ?>user/fines" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="alert alert-light mt-3">
                <i class="fas fa-shield-alt text-success"></i>
                <small>Your payment information is secure and encrypted. We do not store your complete card details.</small>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('paymentForm');
    const cardNumberInput = document.getElementById('card_number');
    const expiryDateInput = document.getElementById('expiry_date');
    const cvvInput = document.getElementById('cvv');
    const cardNameInput = document.getElementById('card_name');
    const paymentMethodInput = document.getElementById('payment_method');
    const cardDetails = document.getElementById('card_details');
    const upiDetails = document.getElementById('upi_details');
    const upiIdInput = document.getElementById('upi_id');

    // Payment method selection
    document.querySelectorAll('input[name="payment_method_selector"]').forEach(radio => {
        radio.addEventListener('change', function() {
            paymentMethodInput.value = this.value;
            
            if (this.value === 'upi') {
                cardDetails.style.display = 'none';
                upiDetails.style.display = 'block';
                
                // Disable card fields
                cardNumberInput.removeAttribute('required');
                expiryDateInput.removeAttribute('required');
                cvvInput.removeAttribute('required');
                cardNameInput.removeAttribute('required');
                
                // Enable UPI field
                upiIdInput.setAttribute('required', 'required');
            } else {
                cardDetails.style.display = 'block';
                upiDetails.style.display = 'none';
                
                // Enable card fields
                cardNumberInput.setAttribute('required', 'required');
                expiryDateInput.setAttribute('required', 'required');
                cvvInput.setAttribute('required', 'required');
                cardNameInput.setAttribute('required', 'required');
                
                // Disable UPI field
                upiIdInput.removeAttribute('required');
            }
        });
    });

    // Format card number
    cardNumberInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s+/g, '');
        let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
        e.target.value = formattedValue;
        
        // Detect card type
        detectCardType(value);
    });

    // Format expiry date
    expiryDateInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.slice(0, 2) + '/' + value.slice(2, 4);
        }
        e.target.value = value;
    });

    // CVV validation
    cvvInput.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
    });

    // Card name validation - only letters and spaces
    cardNameInput.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/[^a-zA-Z\s]/g, '');
    });

    // Luhn algorithm for card validation
    function validateCardNumber(number) {
        number = number.replace(/\s+/g, '');
        
        if (!/^\d{13,19}$/.test(number)) {
            return false;
        }
        
        let sum = 0;
        let isEven = false;
        
        for (let i = number.length - 1; i >= 0; i--) {
            let digit = parseInt(number.charAt(i), 10);
            
            if (isEven) {
                digit *= 2;
                if (digit > 9) {
                    digit -= 9;
                }
            }
            
            sum += digit;
            isEven = !isEven;
        }
        
        return (sum % 10) === 0;
    }

    // Detect card type
    function detectCardType(number) {
        const cardBrands = {
            visa: /^4/,
            mastercard: /^5[1-5]/,
            rupay: /^(60|65|81|82)/,
            amex: /^3[47]/
        };
        
        for (let brand in cardBrands) {
            if (cardBrands[brand].test(number)) {
                console.log('Card type:', brand);
                break;
            }
        }
    }

    // Form validation
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        let isValid = true;
        
        if (paymentMethodInput.value !== 'upi') {
            // Validate card name
            if (cardNameInput.value.trim().length < 3) {
                cardNameInput.classList.add('is-invalid');
                isValid = false;
            } else {
                cardNameInput.classList.remove('is-invalid');
                cardNameInput.classList.add('is-valid');
            }
            
            // Validate card number
            if (!validateCardNumber(cardNumberInput.value)) {
                cardNumberInput.classList.add('is-invalid');
                isValid = false;
            } else {
                cardNumberInput.classList.remove('is-invalid');
                cardNumberInput.classList.add('is-valid');
            }
            
            // Validate expiry date
            const expiryRegex = /^(0[1-9]|1[0-2])\/\d{2}$/;
            if (!expiryRegex.test(expiryDateInput.value)) {
                expiryDateInput.classList.add('is-invalid');
                isValid = false;
            } else {
                const [month, year] = expiryDateInput.value.split('/');
                const expiry = new Date(2000 + parseInt(year), parseInt(month) - 1);
                const now = new Date();
                
                if (expiry < now) {
                    expiryDateInput.classList.add('is-invalid');
                    isValid = false;
                } else {
                    expiryDateInput.classList.remove('is-invalid');
                    expiryDateInput.classList.add('is-valid');
                }
            }
            
            // Validate CVV
            if (!/^\d{3,4}$/.test(cvvInput.value)) {
                cvvInput.classList.add('is-invalid');
                isValid = false;
            } else {
                cvvInput.classList.remove('is-invalid');
                cvvInput.classList.add('is-valid');
            }
        } else {
            // Validate UPI ID
            const upiRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9]+$/;
            if (!upiRegex.test(upiIdInput.value)) {
                upiIdInput.classList.add('is-invalid');
                isValid = false;
            } else {
                upiIdInput.classList.remove('is-invalid');
                upiIdInput.classList.add('is-valid');
            }
        }
        
        if (isValid) {
            // Show loading state
            const payBtn = document.getElementById('payBtn');
            payBtn.disabled = true;
            payBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            
            // Submit form
            form.submit();
        } else {
            alert('Please fill in all required fields correctly');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
