<?php
require_once '../includes/auth_check.php';

session_start();

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Clear cart logic
if (isset($_GET['clear_cart'])) {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit;
}

// Remove single item logic
if (isset($_GET['remove_item'])) {
    $index = $_GET['remove_item'];
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
        header("Location: cart.php");
        exit;
    }
}

$total = 0;
foreach ($_SESSION['cart'] as $ticket) {
    $total += (float)$ticket['price'] * (int)($ticket['quantity'] ?? 1);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart | Cinema</title>
    <link rel="stylesheet" href="../css/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <?php include '../includes/cinema_header.php'; ?>

    <div class="cart-container">
        <h1>Your Tickets</h1>

        <?php if (empty($_SESSION['cart'])): ?>
            <p>No tickets in cart 🎬</p>
        <?php else: ?>
            <div class="cart-content">
                <div class="left">
                    <?php foreach ($_SESSION['cart'] as $index => $ticket): ?>
                        <div class="cart-item">
                            <div class="cart-item-details">
                                <img src="<?= $ticket['image'] ?>" alt="<?= $ticket['movie'] ?>">
                                <div>
                                    <div class="cart-item-name"><?= $ticket['movie'] ?></div>
                                    <div class="ticket-details">
                                        <span>Date: <?= $ticket['date'] ?></span>
                                        <span>Time: <?= $ticket['time'] ?></span>
                                        <span>Seats: <?= $ticket['seats'] ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="cart-item-actions">
                                <div class="cart-item-price">₱<?= number_format((float)$ticket['price'] * ($ticket['quantity'] ?? 1), 2) ?></div>
                                <a href="?remove_item=<?= $index ?>" class="remove-btn" onclick="return confirm('Are you sure you want to remove this ticket?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="right">
                    <h1>Checkout</h1>
                    <form method="post" action="receipt.php" class="checkout-form">
                        <div class="form">
                            <div class="group">
                                <label for="name">Full Name</label>
                                <input type="text" name="name" id="name" required>
                            </div>
                            <div class="group">
                                <label for="email">Email Address</label>
                                <input type="email" name="email" id="email" placeholder="example@email.com" autocapitalize="off" required>
                            </div>
                            <div class="group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" name="phone" id="phone" pattern="[0-9]{7,15}" inputmode="numeric" maxlength="13" placeholder="09XXXXXXXXX" required>
                            </div>
                            <div class="group">
                                <label for="payment_method">Payment Method</label>
                                <select name="payment_method" id="payment_method" required onchange="togglePaymentFields()">
                                    <option value="">Choose..</option>
                                    <option value="gcash">GCash</option>
                                    <option value="card">Card Payment</option>
                                </select>
                            </div>
                            <div id="gcash-fields" style="display:none;">
                                <div class="group">
                                    <label for="gcash_number">GCash Number</label>
                                    <input type="text" name="gcash_number" id="gcash_number" pattern="[0-9]{7,15}" inputmode="numeric" maxlength="13" placeholder="09XXXXXXXXX" required>
                                </div>
                            </div>
                            <div id="card-fields" style="display:none;">
                                <div class="group">
                                    <label for="card_name">Cardholder Name</label>
                                    <input type="text" name="card_name" id="card_name">
                                </div>
                                <div class="group">
                                    <label for="card_number">Card Number</label>
                                    <input type="text" name="card_number" id="card_number" maxlength="16" pattern="\d{16}" inputmode="numeric" placeholder="1234567890123456">
                                </div>
                                <div class="group">
                                    <label for="expiry">Expiration Date</label>
                                    <input type="month" name="expiry" id="expiry">
                                </div>
                                <div class="group">
                                    <label for="cvv">CVV</label>
                                    <input type="text" name="cvv" id="cvv" maxlength="4" pattern="\d{3,4}" inputmode="numeric" placeholder="123">
                                </div>
                                <div class="group">
                                    <label for="address_card">Address</label>
                                    <input type="text" name="address_card" id="address_card">
                                </div>
                            </div>
                        </div>
                        <div class="return"></div>
                        <div class="row">
                            <div>Total Price</div>
                            <div class="totalPrice"><strong>₱<?= $total ?></strong></div>
                        </div>
                        <div class="cart-buttons">
                            <button type="submit" name="checkout" class="checkout-btn">Purchase Tickets</button>
                            <button type="button" onclick="clearCart()" class="clear-btn">Clear Cart</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function clearCart() {
            if(confirm('Are you sure you want to clear your cart?')) {
                window.location.href = 'cart.php?clear_cart=1';
            }
        }

        function togglePaymentFields() {
            var method = document.getElementById('payment_method').value;
            var gcashFields = document.getElementById('gcash-fields');
            var cardFields = document.getElementById('card-fields');
            if (method === 'gcash') {
                gcashFields.style.display = 'block';
                cardFields.style.display = 'none';
                document.getElementById('gcash_number').required = true;
                document.getElementById('card_name').required = false;
                document.getElementById('card_number').required = false;
                document.getElementById('expiry').required = false;
                document.getElementById('cvv').required = false;
                document.getElementById('address_card').required = false;
            } else if (method === 'card') {
                gcashFields.style.display = 'none';
                cardFields.style.display = 'block';
                document.getElementById('gcash_number').required = false;
                document.getElementById('card_name').required = true;
                document.getElementById('card_number').required = true;
                document.getElementById('expiry').required = true;
                document.getElementById('cvv').required = true;
                document.getElementById('address_card').required = true;
            } else {
                gcashFields.style.display = 'none';
                cardFields.style.display = 'none';
                document.getElementById('gcash_number').required = false;
                document.getElementById('card_name').required = false;
                document.getElementById('card_number').required = false;
                document.getElementById('expiry').required = false;
                document.getElementById('cvv').required = false;
                document.getElementById('address_card').required = false;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            var phoneInput = document.getElementById('phone');
            if (phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            }
            var cardInput = document.getElementById('card_number');
            if (cardInput) {
                cardInput.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            }
        });
    </script>

    <?php include '../includes/cinema_footer.php'; ?>

</body>

</html>