<?php
$pageTitle = 'Payment';
include APP_ROOT . '/views/layouts/header.php';

// Get transaction data from URL or passed variables
$borrowId = $_GET['borrow_id'] ?? ($transaction['tid'] ?? '');
$amount = $_GET['amount'] ?? ($transaction['fineAmount'] ?? 0);
$bookName = $_GET['book_name'] ?? ($transaction['bookName'] ?? 'N/A');

$payAll = $pay_all ?? false;
$totalAmount = $payAll ? ($total_amount ?? 0) : $amount;
?>

<style>
    body {
        overflow-x: hidden;
        scrollbar-width: none;
    }
    body::-webkit-scrollbar { display: none; }
    
    .payment-wrapper {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 3rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .payment-container {
        max-width: 900px;
        width: 100%;
        background: rgba(255, 255, 255, 0.98);
        border-radius: 32px;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.3);
        overflow: hidden;
    }
    
    .payment-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2rem 2.5rem;
        color: white;
    }
    
    .payment-header h2 {
        margin: 0;
        font-size: 2rem;
        font-weight: 900;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .payment-body {
        padding: 2.5rem;
    }
    
    .payment-summary {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        border-left: 5px solid #667eea;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .payment-summary h5 {
        font-weight: 800;
        margin-bottom: 1rem;
        color: #1f2937;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    .summary-row:last-child {
        border-bottom: none;
        font-size: 1.3rem;
        font-weight: 900;
        color: #667eea;
        padding-top: 1rem;
    }
    
    .payment-method-selector {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .payment-method-option {
        position: relative;
    }
    
    .payment-method-option input[type="radio"] {
        position: absolute;
        opacity: 0;
    }
    
    .payment-method-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        padding: 1.5rem;
        border: 2px solid #e5e7eb;
        border-radius: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }
    
    .payment-method-label i {
        font-size: 2rem;
        color: #9ca3af;
    }
    
    .payment-method-option input:checked + .payment-method-label {
        border-color: #667eea;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    }
    
    .payment-method-option input:checked + .payment-method-label i {
        color: #667eea;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: #374151;
    }
    
    .form-control {
        width: 100%;
        padding: 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .saved-cards {
        margin-bottom: 2rem;
    }
    
    .saved-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        margin-bottom: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .saved-card:hover {
        border-color: #667eea;
        background: rgba(102, 126, 234, 0.05);
    }
    
    .saved-card input[type="radio"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    
    .card-icon {
        width: 50px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 8px;
        color: white;
        font-size: 1.2rem;
    }
    
    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        background: rgba(102, 126, 234, 0.05);
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }
    
    .checkbox-group input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    
    .btn-submit {
        width: 100%;
        padding: 1.25rem;
        border: none;
        border-radius: 16px;
        font-size: 1.2rem;
        font-weight: 900;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
    }
    
    .btn-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(16, 185, 129, 0.4);
    }
    
    .btn-cancel {
        width: 100%;
        padding: 1rem;
        margin-top: 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 16px;
        background: white;
        color: #6b7280;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: block;
        text-align: center;
    }
    
    .btn-cancel:hover {
        border-color: #9ca3af;
        color: #374151;
    }
    
    .security-notice {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: rgba(16, 185, 129, 0.1);
        border-radius: 12px;
        margin-top: 2rem;
        color: #059669;
        font-size: 0.9rem;
    }
    
    .error-message {
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        display: none;
    }
    
    .form-control.is-invalid {
        border-color: #ef4444;
    }
    
    .form-control.is-invalid + .error-message {
        display: block;
    }
    
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        .payment-method-selector {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="payment-wrapper">
    <div class="payment-container">
        <div class="payment-header">
            <h2>
                <i class="fas fa-credit-card"></i>
                Payment Details
            </h2>
        </div>
        
        <div class="payment-body">
            <div class="payment-summary">
                <h5><i class="fas fa-info-circle"></i> Payment Summary</h5>
                <?php if ($payAll) { ?>
                    <div class="summary-row">
                        <span>Number of Transactions:</span>
                        <strong><?= count($transactions ?? []) ?></strong>
                    </div>
                <?php } else { ?>
                    <div class="summary-row">
                        <span>Book:</span>
                        <strong><?= htmlspecialchars($bookName) ?></strong>
                    </div>
                    <div class="summary-row">
                        <span>Transaction ID:</span>
                        <strong>#<?= htmlspecialchars($borrowId) ?></strong>
                    </div>
                <?php } ?>
                <div class="summary-row">
                    <span>Total Amount:</span>
                    <strong>LKR <?= number_format($totalAmount, 2) ?></strong>
                </div>
            </div>
            
            <form id="paymentForm" method="POST" action="<?= BASE_URL ?>faculty/fines">
                <input type="hidden" name="pay_online" value="1">
                <?php if ($payAll) { ?>
                    <input type="hidden" name="pay_all" value="true">
                    <?php foreach ($transactions ?? [] as $txn) { ?>
                        <input type="hidden" name="transaction_ids[]" value="<?= htmlspecialchars($txn['tid']) ?>">
                    <?php } ?>
                <?php } else { ?>
                    <input type="hidden" name="borrow_id" value="<?= htmlspecialchars($borrowId) ?>">
                <?php } ?>
                <input type="hidden" name="amount" value="<?= htmlspecialchars($totalAmount) ?>">
                <input type="hidden" name="payment_method" id="payment_method" value="credit_card">
                
                <?php if (!empty($savedCards)) { ?>
                <div class="saved-cards">
                    <label style="font-weight: 800; margin-bottom: 1rem; display: block;">
                        <i class="fas fa-wallet"></i> Saved Cards
                    </label>
                    <?php foreach ($savedCards as $card) { ?>
                    <label class="saved-card">
                        <input type="radio" name="saved_card" value="<?= $card['id'] ?>" data-last-four="<?= $card['cardLastFour'] ?>">
                        <div class="card-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 700;"><?= htmlspecialchars($card['cardType']) ?> •••• <?= htmlspecialchars($card['cardLastFour']) ?></div>
                            <div style="font-size: 0.875rem; color: #6b7280;">
                                <?= htmlspecialchars($card['cardHolderName']) ?> | Expires <?= htmlspecialchars($card['expiryMonth']) ?>/<?= substr($card['expiryYear'], -2) ?>
                            </div>
                        </div>
                    </label>
                    <?php } ?>
                    <div style="text-align: center; margin: 1rem 0;">
                        <button type="button" id="useNewCard" style="background: none; border: none; color: #667eea; font-weight: 700; cursor: pointer; text-decoration: underline;">
                            Or use a new card
                        </button>
                    </div>
                </div>
                <?php } ?>
                
                <div id="newCardSection" <?= !empty($savedCards) ? 'style="display: none;"' : '' ?>>
                    <label style="font-weight: 800; margin-bottom: 1rem; display: block;">
                        <i class="fas fa-money-check-alt"></i> Payment Method
                    </label>
                    <div class="payment-method-selector">
                        <div class="payment-method-option">
                            <input type="radio" name="payment_method_selector" id="credit_card" value="credit_card" checked>
                            <label class="payment-method-label" for="credit_card">
                                <i class="fas fa-credit-card"></i>
                                <span>Credit Card</span>
                            </label>
                        </div>
                        <div class="payment-method-option">
                            <input type="radio" name="payment_method_selector" id="debit_card" value="debit_card">
                            <label class="payment-method-label" for="debit_card">
                                <i class="fas fa-credit-card"></i>
                                <span>Debit Card</span>
                            </label>
                        </div>
                        <div class="payment-method-option">
                            <input type="radio" name="payment_method_selector" id="upi" value="upi">
                            <label class="payment-method-label" for="upi">
                                <i class="fas fa-mobile-alt"></i>
                                <span>UPI</span>
                            </label>
                        </div>
                    </div>
                    
                    <div id="card_details">
                        <div class="form-group">
                            <label for="card_name">Cardholder Name *</label>
                            <input type="text" class="form-control" id="card_name" name="card_name" 
                                   placeholder="JOHN DOE" required>
                            <div class="error-message">Please enter cardholder name</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="card_number">Card Number *</label>
                            <input type="text" class="form-control" id="card_number" name="card_number" 
                                   placeholder="1234 5678 9012 3456" maxlength="19" required>
                            <div class="error-message">Please enter a valid card number</div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="expiry_date">Expiry Date *</label>
                                <input type="text" class="form-control" id="expiry_date" name="expiry_date" 
                                       placeholder="MM/YY" maxlength="5" required>
                                <div class="error-message">Card has expired or invalid date</div>
                            </div>
                            <div class="form-group">
                                <label for="cvv">CVV *</label>
                                <input type="text" class="form-control" id="cvv" name="cvv" 
                                       placeholder="123" maxlength="4" required>
                                <div class="error-message">Invalid CVV (3-4 digits)</div>
                            </div>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="save_card" name="save_card" value="1">
                            <label for="save_card" style="margin: 0; cursor: pointer;">
                                <i class="fas fa-save"></i> Save this card for future payments
                            </label>
                        </div>
                        
                        <div id="card_nickname_group" class="form-group" style="display: none;">
                            <label for="card_nickname">Card Nickname (Optional)</label>
                            <input type="text" class="form-control" id="card_nickname" name="card_nickname" 
                                   placeholder="My Primary Card">
                        </div>
                    </div>
                    
                    <div id="upi_details" style="display: none;">
                        <div class="form-group">
                            <label for="upi_id">UPI ID *</label>
                            <input type="text" class="form-control" id="upi_id" name="upi_id" 
                                   placeholder="username@upi">
                            <div class="error-message">Please enter a valid UPI ID</div>
                        </div>
                    </div>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="terms" required>
                    <label for="terms" style="margin: 0; cursor: pointer;">
                        I agree to the terms and authorize this payment
                    </label>
                </div>
                
                <button type="submit" class="btn-submit" id="payBtn">
                    <i class="fas fa-lock"></i>
                    Pay LKR <?= number_format($totalAmount, 2) ?>
                </button>
                
                <a href="<?= BASE_URL ?>faculty/fines" class="btn-cancel">
                    <i class="fas fa-times"></i> Cancel
                </a>
                
                <div class="security-notice">
                    <i class="fas fa-shield-alt" style="font-size: 1.5rem;"></i>
                    <span>Your payment is secure and encrypted. We never store your complete card details.</span>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/form-validation.js"></script>
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
    const saveCardCheckbox = document.getElementById('save_card');
    const cardNicknameGroup = document.getElementById('card_nickname_group');
    const newCardSection = document.getElementById('newCardSection');
    const useNewCardBtn = document.getElementById('useNewCard');
    
    // Show/hide card nickname field
    if (saveCardCheckbox) {
        saveCardCheckbox.addEventListener('change', function() {
            cardNicknameGroup.style.display = this.checked ? 'block' : 'none';
        });
    }
    
    // Use new card button
    if (useNewCardBtn) {
        useNewCardBtn.addEventListener('click', function() {
            newCardSection.style.display = 'block';
            document.querySelectorAll('input[name="saved_card"]').forEach(r => r.checked = false);
        });
    }
    
    // Payment method selection
    document.querySelectorAll('input[name="payment_method_selector"]').forEach(radio => {
        radio.addEventListener('change', function() {
            paymentMethodInput.value = this.value;
            
            if (this.value === 'upi') {
                cardDetails.style.display = 'none';
                upiDetails.style.display = 'block';
                toggleRequired(false);
                upiIdInput.required = true;
            } else {
                cardDetails.style.display = 'block';
                upiDetails.style.display = 'none';
                toggleRequired(true);
                upiIdInput.required = false;
            }
        });
    });
    
    function toggleRequired(required) {
        cardNumberInput.required = required;
        expiryDateInput.required = required;
        cvvInput.required = required;
        cardNameInput.required = required;
    }
    
    // Format card number
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/\D/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });
        
        cardNumberInput.addEventListener('blur', function() {
            const value = this.value.replace(/\s+/g, '');
            if (value && !validateCardNumber(value)) {
                showError(this, 'Invalid card number (fails Luhn check)');
            } else {
                clearError(this);
            }
        });
    }
    
    // Format expiry date
    if (expiryDateInput) {
        expiryDateInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2, 4);
            }
            e.target.value = value;
        });
        
        expiryDateInput.addEventListener('blur', function() {
            if (this.value && !validateExpiryDate(this.value)) {
                showError(this, 'Card has expired or invalid date format');
            } else {
                clearError(this);
            }
        });
    }
    
    // CVV validation
    if (cvvInput) {
        cvvInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
        
        cvvInput.addEventListener('blur', function() {
            if (this.value && !/^\d{3,4}$/.test(this.value)) {
                showError(this, 'CVV must be 3-4 digits');
            } else {
                clearError(this);
            }
        });
    }
    
    // Card name validation
    if (cardNameInput) {
        cardNameInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^a-zA-Z\s]/g, '').toUpperCase();
        });
        
        cardNameInput.addEventListener('blur', function() {
            if (this.value && this.value.trim().length < 3) {
                showError(this, 'Cardholder name must be at least 3 characters');
            } else {
                clearError(this);
            }
        });
    }
    
    // UPI ID validation
    if (upiIdInput) {
        upiIdInput.addEventListener('blur', function() {
            if (this.value && !/^[a-zA-Z0-9._-]+@[a-zA-Z0-9]+$/.test(this.value)) {
                showError(this, 'Invalid UPI ID format (e.g., username@upi)');
            } else {
                clearError(this);
            }
        });
    }
    
    // Luhn algorithm for card validation
    function validateCardNumber(number) {
        number = number.replace(/\s+/g, '');
        
        if (!/^\d{13,19}$/.test(number)) return false;
        
        let sum = 0;
        let isEven = false;
        
        for (let i = number.length - 1; i >= 0; i--) {
            let digit = parseInt(number.charAt(i), 10);
            
            if (isEven) {
                digit *= 2;
                if (digit > 9) digit -= 9;
            }
            
            sum += digit;
            isEven = !isEven;
        }
        
        return (sum % 10) === 0;
    }
    
    // Validate expiry date
    function validateExpiryDate(expiryDate) {
        if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiryDate)) return false;
        
        const [month, year] = expiryDate.split('/');
        const expiry = new Date(2000 + parseInt(year), parseInt(month) - 1);
        const now = new Date();
        
        return expiry >= now;
    }
    
    // Form validation
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        let isValid = true;
        
        // Clear all errors
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
            el.style.borderColor = '';
        });
        document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');
        
        // Check if using saved card
        const usingSavedCard = document.querySelector('input[name="saved_card"]:checked');
        
        if (!usingSavedCard && paymentMethodInput.value !== 'upi') {
            // Validate card name
            if (!cardNameInput.value.trim() || cardNameInput.value.trim().length < 3) {
                showError(cardNameInput, 'Cardholder name must be at least 3 characters');
                isValid = false;
            }
            
            // Validate card number
            const cardNumber = cardNumberInput.value.replace(/\s+/g, '');
            if (!cardNumber || !validateCardNumber(cardNumber)) {
                showError(cardNumberInput, 'Invalid card number (fails Luhn check)');
                isValid = false;
            }
            
            // Validate expiry date
            if (!expiryDateInput.value || !validateExpiryDate(expiryDateInput.value)) {
                showError(expiryDateInput, 'Card has expired or invalid date format');
                isValid = false;
            }
            
            // Validate CVV
            if (!cvvInput.value || !/^\d{3,4}$/.test(cvvInput.value)) {
                showError(cvvInput, 'CVV must be 3-4 digits');
                isValid = false;
            }
        } else if (!usingSavedCard && paymentMethodInput.value === 'upi') {
            // Validate UPI ID
            if (!upiIdInput.value || !/^[a-zA-Z0-9._-]+@[a-zA-Z0-9]+$/.test(upiIdInput.value)) {
                showError(upiIdInput, 'Invalid UPI ID format (e.g., username@upi)');
                isValid = false;
            }
        }
        
        // Check terms checkbox
        const termsCheckbox = document.getElementById('terms');
        if (!termsCheckbox.checked) {
            alert('⚠️ You must agree to the terms and conditions');
            termsCheckbox.focus();
            isValid = false;
        }
        
        if (isValid) {
            const payBtn = document.getElementById('payBtn');
            payBtn.disabled = true;
            payBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing Payment...';
            form.submit();
        } else {
            // Scroll to first error
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    });
});
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
