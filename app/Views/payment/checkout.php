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
                                          style="width:100%;height:100%;object-fit:cover;"
                                          onerror="this.style.display='none'; this.parentElement.innerHTML='<div style=\"width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:2rem;\">📖</div>';">
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
                                    <input type="number" id="quantity-input-field" class="form-control" style="max-width:80px;text-align:center;" min="1" max="<?= (int)($book['available'] ?? 10) ?>" value="<?= (int)$quantity ?>" onchange="updateQuantity()">
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
    // Could update UI based on selected method (e.g., show card fields for card, etc.)
}

document.getElementById('payment-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    const termsCheck = document.getElementById('terms-check').checked;
    if (!termsCheck) {
        App.Toast.show('Please agree to the terms and conditions', 'error');
        return;
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
</script>

