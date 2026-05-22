<?php
$title = $title ?? 'Checkout';
$book = $book ?? [];
$unitPrice = (float)($unitPrice ?? 0);
$quantity = max(1, (int)($quantity ?? 1));

View::includeLayout('header', ['title' => $title]);
View::includeLayout('navbar');

$coverPath = getBookCover($book);
$totalPrice = $unitPrice * $quantity;
?>

<div class="page-background"></div>
<div class="page-wrapper" style="padding-top: var(--navbar-height);">
    <!-- Floating decorations -->
    <div class="floating-decorations" aria-hidden="true"></div>
    <section class="section">
        <div class="container">
            <div style="max-width:900px;margin:0 auto;">
                <div class="section-header">
                    <h2 class="text-gradient">Checkout</h2>
                    <a href="<?= url('books/' . $book['id']) ?>" class="text-muted text-sm">← Back to book</a>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;align-items:start;">
                    <!-- Order Summary (Left) -->
                    <div class="glass-card" style="padding:24px;border-radius:var(--radius-lg);">
                        <h3 style="margin-bottom:20px;font-size:1.1rem;">Order Summary</h3>

                        <!-- Book Card -->
                        <div style="display:flex;gap:16px;margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid var(--border);">
                            <div style="width:80px;height:120px;border-radius:var(--radius);overflow:hidden;flex-shrink:0;background:var(--surface);">
                                 <?php if ($coverPath): ?>
                                     <img src="<?= e($coverPath) ?>" 
                                          alt="<?= e($book['title']) ?> cover" 
                                          data-isbn="<?= e($book['isbn'] ?? '') ?>"
                                          class="checkout-cover-image"
                                          style="width:100%;height:100%;object-fit:cover;">
                                     <div class="book-cover-fallback" style="width:100%;height:100%;display:none;align-items:center;justify-content:center;font-size:2rem;">📖</div>
                                 <?php else: ?>
                                     <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:2rem;">📖</div>
                                 <?php endif; ?>
                             </div>
                            <div style="flex:1;">
                                <h4 style="margin:0 0 4px 0;"><?= e($book['title']) ?></h4>
                                <p style="margin:0 0 8px 0;font-size:0.9rem;color:var(--text-muted);">by <?= e($book['author']) ?></p>
                                <p style="margin:0;font-size:0.85rem;color:var(--text-muted);"><?= e($category['name'] ?? 'General') ?></p>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;font-size:0.95rem;">
                            <span class="text-muted">Unit Price:</span>
                            <span>$<?= number_format($unitPrice, 2) ?></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;font-size:0.95rem;">
                            <span class="text-muted">Quantity:</span>
                            <span id="quantity-display"><?= (int)$quantity ?></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding-top:12px;border-top:1px solid var(--border);font-weight:600;font-size:1.1rem;color:var(--primary);">
                            <span>Total:</span>
                            <span id="total-price-display">$<?= number_format($totalPrice, 2) ?></span>
                        </div>

                        <!-- Estimated Delivery -->
                        <div style="margin-top:20px;padding:12px;background:rgba(16,185,129,0.1);border-radius:var(--radius);border-left:3px solid var(--success);">
                            <p style="margin:0;font-size:0.85rem;color:var(--success);"><strong>📦 Instant Delivery</strong></p>
                            <p style="margin:4px 0 0 0;font-size:0.8rem;color:var(--text-muted);">Digital access available immediately after payment</p>
                        </div>
                    </div>

                    <!-- Payment Form (Right) -->
                    <div class="glass-card" style="padding:24px;border-radius:var(--radius-lg);">
                        <h3 style="margin-bottom:20px;font-size:1.1rem;">Payment Details</h3>

                        <form id="payment-form" method="POST" action="<?= url('payment/process') ?>">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
                            <input type="hidden" name="quantity" id="quantity-input" value="<?= (int)$quantity ?>">

                            <!-- Quantity -->
                            <div class="form-group" style="margin-bottom:20px;">
                                <label class="form-label">Quantity</label>
                                <div style="display:flex;gap:8px;align-items:center;">
                                    <button type="button" class="btn btn-secondary btn-sm" id="qty-minus" onclick="changeQuantity(-1)">−</button>
                                    <input type="number" id="quantity-input-field" class="form-control" style="max-width:80px;text-align:center;" aria-label="Quantity" min="1" max="<?= (int)($book['available'] ?? 10) ?>" value="<?= (int)$quantity ?>" onchange="updateQuantity()">
                                    <button type="button" class="btn btn-secondary btn-sm" id="qty-plus" onclick="changeQuantity(1)">+</button>
                                    <span class="text-muted text-sm" style="margin-left:auto;">Max: <?= (int)($book['available'] ?? 1) ?></span>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="form-group" style="margin-bottom:20px;">
                                <label class="form-label">Payment Method</label>
                                <div style="display:flex;flex-direction:column;gap:12px;">
                                    <label style="display:flex;align-items:center;gap:12px;padding:12px;border:1px solid var(--border);border-radius:var(--radius);cursor:pointer;transition:var(--transition);" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'">
                                        <input type="radio" name="payment_method" value="card" checked onchange="updatePaymentMethod('card')">
                                        <span>
                                            <span style="display:block;font-weight:500;margin-bottom:2px;">💳 Credit Card</span>
                                            <span style="display:block;font-size:0.8rem;color:var(--text-muted);">Visa, Mastercard, American Express</span>
                                        </span>
                                    </label>
                                    <label style="display:flex;align-items:center;gap:12px;padding:12px;border:1px solid var(--border);border-radius:var(--radius);cursor:pointer;transition:var(--transition);" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'">
                                        <input type="radio" name="payment_method" value="paypal" onchange="updatePaymentMethod('paypal')">
                                        <span>
                                            <span style="display:block;font-weight:500;margin-bottom:2px;">🅿️ PayPal</span>
                                            <span style="display:block;font-size:0.8rem;color:var(--text-muted);">Fast and secure PayPal checkout</span>
                                        </span>
                                    </label>
                                    <label style="display:flex;align-items:center;gap:12px;padding:12px;border:1px solid var(--border);border-radius:var(--radius);cursor:pointer;transition:var(--transition);" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'">
                                        <input type="radio" name="payment_method" value="transfer" onchange="updatePaymentMethod('transfer')">
                                        <span>
                                            <span style="display:block;font-weight:500;margin-bottom:2px;">🏦 Bank Transfer</span>
                                            <span style="display:block;font-size:0.8rem;color:var(--text-muted);">Direct bank transfer (may require verification)</span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Card Details -->
                            <div class="glass-card" id="card-details" style="margin-bottom:20px;padding:16px;border:1px solid var(--border);">
                                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px;flex-wrap:wrap;">
                                    <label class="form-label" style="margin:0;">Card Details</label>
                                    <span class="text-muted text-sm">Required for credit card payments</span>
                                </div>

                                <div class="form-group" style="margin-bottom:12px;">
                                    <label class="form-label" for="card_name">Name on Card</label>
                                    <input type="text" id="card_name" name="card_name" class="form-control" placeholder="Jane Doe" autocomplete="cc-name">
                                </div>

                                <div class="form-group" style="margin-bottom:12px;">
                                    <label class="form-label" for="card_number">Card Number</label>
                                    <input type="text" id="card_number" name="card_number" class="form-control" placeholder="1234 5678 9012 3456" inputmode="numeric" autocomplete="cc-number" maxlength="19">
                                </div>

                                <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;">
                                    <div class="form-group">
                                        <label class="form-label" for="card_expiry">Expiry</label>
                                        <input type="text" id="card_expiry" name="card_expiry" class="form-control" placeholder="MM/YY" autocomplete="cc-exp" maxlength="5">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="card_cvv">CVV</label>
                                        <input type="password" id="card_cvv" name="card_cvv" class="form-control" placeholder="123" inputmode="numeric" autocomplete="cc-csc" maxlength="4">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="card_zip">ZIP / Postal</label>
                                        <input type="text" id="card_zip" name="card_zip" class="form-control" placeholder="10001" autocomplete="billing postal-code">
                                    </div>
                                </div>
                                <p id="card-feedback" class="text-muted text-sm" style="margin:12px 0 0 0;"></p>
                            </div>

                            <!-- Terms -->
                            <div style="margin-bottom:20px;padding:12px;background:var(--surface);border-radius:var(--radius);">
                                <label style="display:flex;align-items:flex-start;gap:8px;font-size:0.9rem;cursor:pointer;">
                                    <input type="checkbox" id="terms-check" style="margin-top:2px;" required>
                                    <span>I agree to the <a href="#" style="color:var(--primary);text-decoration:none;border-bottom:1px solid var(--primary);">Terms of Service</a> and <a href="#" style="color:var(--primary);text-decoration:none;border-bottom:1px solid var(--primary);">Privacy Policy</a></span>
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-primary w-full btn-lg" id="pay-button" style="font-weight:600;">
                                Complete Payment - $<?= number_format($totalPrice, 2) ?>
                            </button>

                            <!-- Loading State Will Be Shown Here -->
                            <p id="payment-feedback" class="text-muted text-sm" style="margin-top:12px;text-align:center;display:none;"></p>
                        </form>

                        <!-- Security Info -->
                        <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);display:flex;justify-content:center;gap:16px;font-size:0.8rem;color:var(--text-muted);">
                            <span>🔒 Secure Payment</span>
                            <span>✓ SSL Encrypted</span>
                            <span>✓ PCI Compliant</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php View::includeLayout('footer'); ?>

<script>
let currentQuantity = <?= (int)$quantity ?>;
const maxQuantity = <?= (int)($book['available'] ?? 1) ?>;
const basePrice = <?= number_format($unitPrice, 2) ?>;
const cardDetails = document.getElementById('card-details');
const cardFeedback = document.getElementById('card-feedback');
const cardFields = ['card_name', 'card_number', 'card_expiry', 'card_cvv', 'card_zip'];

document.querySelectorAll('.checkout-cover-image').forEach((img) => {
    img.addEventListener('error', () => {
        img.remove();
        img.parentElement.querySelector('.book-cover-fallback')?.style && (img.parentElement.querySelector('.book-cover-fallback').style.display = 'flex');
    }, { once: true });
});

function getSelectedPaymentMethod() {
    return document.querySelector('input[name="payment_method"]:checked')?.value || 'card';
}

function setCardRequired(isRequired) {
    cardFields.forEach((fieldId) => {
        const input = document.getElementById(fieldId);
        if (!input) return;
        input.required = isRequired;
        if (!isRequired) {
            input.setCustomValidity('');
        }
    });
    if (cardDetails) {
        cardDetails.style.display = isRequired ? 'block' : 'none';
    }
}

function validateCardDetails() {
    const name = document.getElementById('card_name')?.value.trim() || '';
    const number = document.getElementById('card_number')?.value.replace(/\s+/g, '') || '';
    const expiry = document.getElementById('card_expiry')?.value.trim() || '';
    const cvv = document.getElementById('card_cvv')?.value.trim() || '';
    const zip = document.getElementById('card_zip')?.value.trim() || '';

    if (!name || !number || !expiry || !cvv || !zip) {
        return 'Please fill in all card details.';
    }

    if (!/^\d{13,19}$/.test(number)) {
        return 'Card number must contain 13 to 19 digits.';
    }

    if (!/^(0[1-9]|1[0-2])\/(\d{2})$/.test(expiry)) {
        return 'Expiry must be in MM/YY format.';
    }

    if (!/^\d{3,4}$/.test(cvv)) {
        return 'CVV must be 3 or 4 digits.';
    }

    return '';
}

function changeQuantity(delta) {
    const newQty = Math.max(1, Math.min(maxQuantity, currentQuantity + delta));
    if (newQty !== currentQuantity) {
        currentQuantity = newQty;
        document.getElementById('quantity-input-field').value = newQty;
        updateQuantity();
    }
}

function updateQuantity() {
    const input = document.getElementById('quantity-input-field');
    let qty = parseInt(input.value) || 1;
    qty = Math.max(1, Math.min(maxQuantity, qty));
    currentQuantity = qty;

    document.getElementById('quantity-input').value = qty;
    document.getElementById('quantity-display').textContent = qty;

    const total = (parseFloat('<?= $unitPrice ?>') * qty).toFixed(2);
    document.getElementById('total-price-display').textContent = '$' + total;
    document.getElementById('pay-button').textContent = 'Complete Payment - $' + total;

    input.value = qty;
}

function updatePaymentMethod(method) {
    console.log('Payment method selected:', method);
    setCardRequired(method === 'card');
    if (cardFeedback) {
        cardFeedback.textContent = method === 'card' ? 'Please enter the card details to continue.' : '';
    }
}

document.getElementById('payment-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    const paymentMethod = getSelectedPaymentMethod();
    const termsCheck = document.getElementById('terms-check').checked;
    if (!termsCheck) {
        App.Toast.show('Please agree to the terms and conditions', 'error');
        return;
    }

    if (paymentMethod === 'card') {
        const cardError = validateCardDetails();
        if (cardError) {
            if (cardFeedback) {
                cardFeedback.textContent = cardError;
                cardFeedback.style.color = 'var(--danger)';
            }
            App.Toast.show(cardError, 'error');
            return;
        }
    }

    const payButton = document.getElementById('pay-button');
    const feedback = document.getElementById('payment-feedback');
    const baseText = payButton.textContent;

    payButton.disabled = true;
    payButton.textContent = '⏳ Processing...';
    feedback.style.display = 'block';
    feedback.textContent = 'Processing your payment...';

    const formData = new FormData(e.target);
    const base = document.querySelector('meta[name="base-url"]')?.content || '';

    try {
        const response = await fetch(base + '/payment/process', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            feedback.textContent = 'Payment successful! Redirecting...';
            feedback.style.color = 'var(--success)';
            App.Toast.show(data.message, 'success');
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        } else {
            feedback.textContent = data.message || 'Payment failed. Please try again.';
            feedback.style.color = 'var(--danger)';
            App.Toast.show(data.message, 'error');
            payButton.disabled = false;
            payButton.textContent = baseText;
        }
    } catch (err) {
        feedback.textContent = 'An error occurred. Please try again.';
        feedback.style.color = 'var(--danger)';
        App.Toast.show('Payment error: ' + err.message, 'error');
        payButton.disabled = false;
        payButton.textContent = baseText;
        console.error('Payment error:', err);
    }
});

setCardRequired(getSelectedPaymentMethod() === 'card');
document.querySelectorAll('input[name="payment_method"]').forEach((input) => {
    input.addEventListener('change', () => updatePaymentMethod(input.value));
});
</script>

