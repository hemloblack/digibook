<?php include 'includes/header.php'; ?>

<div class="cart-container">
    <h2>🛒 سبد خرید شما</h2>
    <div id="cart-items"></div>
    <div id="cart-total"></div>
</div>

<script>
function displayCart() {
    let cart = getCart();
    let container = document.getElementById('cart-items');
    let totalDiv = document.getElementById('cart-total');

    if (!container || !totalDiv) return;

    container.innerHTML = '';

    if (cart.length === 0) {
        container.innerHTML = '<div class="empty-state">' +
            '<div class="empty-state-icon">🛒</div>' +
            '<h3>سبد خرید شما خالی است</h3>' +
            '<p>برای افزودن کتاب به سبد خرید، به صفحه اصلی مراجعه کنید</p>' +
            '<a href="/bookstore/index.php" class="btn btn-primary">رفتن به فروشگاه</a>' +
            '</div>';
        totalDiv.innerHTML = '';
        return;
    }

    let total = 0;

    for (let i = 0; i < cart.length; i++) {
        let item = cart[i];
        let price = parseFloat(item.price) || 0;
        let quantity = parseInt(item.quantity) || 1;
        let itemTotal = price * quantity;
        total = total + itemTotal;

        container.innerHTML += '<div class="cart-item">' +
            '<div class="cart-item-details">' +
            '<h5>' + escapeHtml(item.title) + '</h5>' +
            '<span class="unit-price">قیمت واحد: ' + price.toLocaleString('fa-IR') + ' تومان</span>' +
            '</div>' +
            '<div class="cart-item-quantity">' +
            '<button onclick="updateQuantity(' + item.id + ', -1)">−</button>' +
            '<span>' + quantity + '</span>' +
            '<button onclick="updateQuantity(' + item.id + ', 1)">+</button>' +
            '</div>' +
            '<div class="cart-item-price">' + itemTotal.toLocaleString('fa-IR') + ' تومان</div>' +
            '<button class="cart-item-remove" onclick="removeFromCart(' + item.id + ')">🗑️ حذف</button>' +
            '</div>';
    }

    totalDiv.innerHTML = '<div class="cart-total">' +
        '<h3>جمع کل سبد خرید:</h3>' +
        '<span class="total-price">' + total.toLocaleString('fa-IR') + ' تومان</span>' +
        '<a href="/bookstore/checkout.php" class="btn btn-success btn-lg btn-block">✅ نهایی کردن خرید</a>' +
        '</div>';
}

function escapeHtml(text) {
    let div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    displayCart();
});
</script>

<?php include 'includes/footer.php'; ?>
