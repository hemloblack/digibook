/**
 * کتابفروشی من - مدیریت سبد خرید
 * باگ جمع قیمت‌ها رفع شد
 */

function getCart() {
    try {
        var cart = localStorage.getItem('bookstore_cart');
        if (cart) {
            return JSON.parse(cart);
        }
    } catch (e) {}
    return [];
}

function saveCart(cart) {
    localStorage.setItem('bookstore_cart', JSON.stringify(cart));
    updateCartCount();
}

function addToCart(bookId, title, price, image) {
    var cart = getCart();
    var found = false;
    
    // تبدیل قیمت به عدد
    price = parseFloat(price) || 0;
    
    for (var i = 0; i < cart.length; i++) {
        if (cart[i].id == bookId) {
            cart[i].quantity = cart[i].quantity + 1;
            found = true;
            break;
        }
    }
    
    if (!found) {
        cart.push({
            id: bookId,
            title: title,
            price: price,
            image: image || '',
            quantity: 1
        });
    }
    
    saveCart(cart);
    showNotification('کتاب با موفقیت به سبد خرید اضافه شد!');
}

function removeFromCart(bookId) {
    var cart = getCart();
    var newCart = [];
    
    for (var i = 0; i < cart.length; i++) {
        if (cart[i].id != bookId) {
            newCart.push(cart[i]);
        }
    }
    
    saveCart(newCart);
    showNotification('کتاب از سبد خرید حذف شد');
    displayCart();
}

function updateQuantity(bookId, change) {
    var cart = getCart();
    
    for (var i = 0; i < cart.length; i++) {
        if (cart[i].id == bookId) {
            cart[i].quantity = cart[i].quantity + change;
            if (cart[i].quantity <= 0) {
                cart.splice(i, 1);
            }
            break;
        }
    }
    
    saveCart(cart);
    displayCart();
}

function updateCartCount() {
    var countElement = document.getElementById('cart-count');
    if (countElement) {
        var cart = getCart();
        var totalItems = 0;
        
        for (var i = 0; i < cart.length; i++) {
            totalItems = totalItems + cart[i].quantity;
        }
        
        countElement.textContent = totalItems;
        
        countElement.style.transform = 'scale(1.4)';
        setTimeout(function() {
            countElement.style.transform = 'scale(1)';
        }, 200);
    }
}

// نمایش سبد خرید
function displayCart() {
    var cart = getCart();
    var container = document.getElementById('cart-items');
    var totalDiv = document.getElementById('cart-total');
    
    if (!container || !totalDiv) return;
    
    container.innerHTML = '';
    
    if (cart.length === 0) {
        container.innerHTML = '<div class="empty-state">' +
            '<div class="empty-state-icon">&#128722;</div>' +
            '<h3>سبد خرید شما خالی است</h3>' +
            '<p>برای افزودن کتاب به سبد خرید، به صفحه اصلی مراجعه کنید</p>' +
            '<a href="/bookstore/index.php" class="btn btn-primary">رفتن به فروشگاه</a>' +
            '</div>';
        totalDiv.innerHTML = '';
        return;
    }
    
    var total = 0;
    
    for (var i = 0; i < cart.length; i++) {
        var item = cart[i];
        var price = parseFloat(item.price) || 0;
        var quantity = parseInt(item.quantity) || 1;
        var itemTotal = price * quantity;
        total = total + itemTotal;
        
        container.innerHTML += '<div class="cart-item">' +
            '<div class="cart-item-details">' +
            '<h5>' + item.title + '</h5>' +
            '<span class="unit-price">قیمت واحد: ' + price.toLocaleString('fa-IR') + ' تومان</span>' +
            '</div>' +
            '<div class="cart-item-quantity">' +
            '<button onclick="updateQuantity(' + item.id + ', -1)">-</button>' +
            '<span>' + quantity + '</span>' +
            '<button onclick="updateQuantity(' + item.id + ', 1)">+</button>' +
            '</div>' +
            '<div class="cart-item-price">' + itemTotal.toLocaleString('fa-IR') + ' تومان</div>' +
            '<button class="cart-item-remove" onclick="removeFromCart(' + item.id + ')">حذف</button>' +
            '</div>';
    }
    
    totalDiv.innerHTML = '<div class="cart-total">' +
        '<h3>جمع کل سبد خرید:</h3>' +
        '<span class="total-price">' + total.toLocaleString('fa-IR') + ' تومان</span>' +
        '<a href="/bookstore/checkout.php" class="btn btn-success btn-lg btn-block">نهایی کردن خرید</a>' +
        '</div>';
}

// نوتیفیکیشن
function showNotification(message) {
    var oldNotif = document.querySelector('.notification');
    if (oldNotif) {
        oldNotif.parentNode.removeChild(oldNotif);
    }
    
    var notif = document.createElement('div');
    notif.className = 'notification';
    notif.textContent = message;
    document.body.appendChild(notif);
    
    setTimeout(function() {
        if (notif.parentNode) {
            notif.parentNode.removeChild(notif);
        }
    }, 3000);
}

// دکمه اسکرول به بالا
function createScrollTopButton() {
    var btn = document.createElement('button');
    btn.className = 'scroll-top-btn';
    btn.innerHTML = '&#8593;';
    btn.title = 'برو بالا';
    btn.onclick = function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };
    document.body.appendChild(btn);
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 500) {
            btn.classList.add('show');
        } else {
            btn.classList.remove('show');
        }
    });
}

// راه‌اندازی
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    createScrollTopButton();
    
    // نمایش سبد خرید در صفحه cart.php
    if (window.location.pathname.indexOf('cart.php') > -1) {
        displayCart();
    }
    
    // دکمه‌های افزودن به سبد خرید
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.add-to-cart-btn');
        if (btn) {
            e.preventDefault();
            var id = btn.getAttribute('data-id');
            var title = btn.getAttribute('data-title') || 'کتاب';
            var price = btn.getAttribute('data-price') || '0';
            var image = btn.getAttribute('data-image') || '';
            addToCart(id, title, price, image);
        }
    });
});