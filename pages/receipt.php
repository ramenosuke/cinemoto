<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// Get form data from POST
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$payment_method = $_POST['payment_method'] ?? '';
$user_id = $_SESSION['user_id'] ?? null;

if ($payment_method === 'gcash') {
    $gcash_number = $_POST['gcash_number'] ?? '';
} elseif ($payment_method === 'card') {
    $address = $_POST['address_card'] ?? '';
    $card_name = $_POST['card_name'] ?? '';
    $card_number = $_POST['card_number'] ?? '';
    $expiry = $_POST['expiry'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
}

// Store cart data before clearing it
$tickets = $_SESSION['cart'];

// Calculate total
$total = 0;
foreach ($tickets as $ticket) {
    $total += (float)$ticket['price'] * (int)($ticket['quantity'] ?? 1);
}

// Generate ticket number
$ticket_number = 'TIX-' . strtoupper(substr(uniqid(), -6));

// --- BOOKING LOGIC ---
if ($user_id) {
    foreach ($tickets as $ticket) {
        // Insert booking
        $stmt = $pdo->prepare('INSERT INTO bookings (user_id, schedule_id, total_price, status) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            $user_id,
            $ticket['schedule_id'],
            $ticket['price'] * $ticket['quantity'],
            'confirmed'
        ]);
        $booking_id = $pdo->lastInsertId();
        // Insert booked seats
        foreach ($ticket['seat_array'] as $seat) {
            $stmt2 = $pdo->prepare('INSERT IGNORE INTO booked_seats (booking_id, schedule_id, seat_number) VALUES (?, ?, ?)');
            $stmt2->execute([$booking_id, $ticket['schedule_id'], $seat]);
        }
    }
}
// --- END BOOKING LOGIC ---

// Clear the cart after successful checkout
$_SESSION['cart'] = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Tickets | Cinema</title>
    <!-- Removed Plantitos global CSS for this page -->
    <link rel="stylesheet" href="../css/receipt.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/cinema_header.php'; ?>

    <div class="receipt-container">
        <div class="receipt">
            <div class="receipt-header">
                <h1>Movie Tickets</h1>
                <div class="ticket-number">Ticket #: <?php echo $ticket_number; ?></div>
                <div class="date">Date: <?php echo date('F d, Y'); ?></div>
            </div>

            <div class="customer-info">
                <h2>Customer Information</h2>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($phone); ?></p>
                <p><strong>Payment Method:</strong> <?php echo $payment_method === 'gcash' ? 'GCash' : 'Card Payment'; ?></p>
                <?php if ($payment_method === 'gcash'): ?>
                    <p><strong>GCash Number:</strong> <?php echo htmlspecialchars($gcash_number); ?></p>
                <?php elseif ($payment_method === 'card'): ?>
                    <p><strong>Cardholder Name:</strong> <?php echo htmlspecialchars($card_name); ?></p>
                    <p><strong>Card Number:</strong> **** **** **** <?php echo htmlspecialchars(substr($card_number, -4)); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($address); ?></p>
                <?php endif; ?>
            </div>

            <div class="tickets">
                <h2>Your Tickets</h2>
                <?php foreach ($tickets as $ticket): ?>
                    <div class="ticket">
                        <div class="ticket-image">
                            <img src="<?php echo htmlspecialchars($ticket['image']); ?>" alt="<?php echo htmlspecialchars($ticket['movie']); ?>">
                        </div>
                        <div class="ticket-details">
                            <h3><?php echo htmlspecialchars($ticket['movie']); ?></h3>
                            <p><strong>Date:</strong> <?php echo htmlspecialchars($ticket['date']); ?></p>
                            <p><strong>Time:</strong> <?php echo htmlspecialchars($ticket['time']); ?></p>
                            <p><strong>Seats:</strong> <?php echo htmlspecialchars($ticket['seats']); ?></p>
                            <p><strong>Quantity:</strong> <?php echo $ticket['quantity'] ?? 1; ?></p>
                            <p><strong>Price:</strong> ₱<?php echo number_format((float)$ticket['price'] * ($ticket['quantity'] ?? 1), 2); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="receipt-footer">
                <div class="total">
                    <strong>Total Amount:</strong> ₱<?php echo number_format($total, 2); ?>
                </div>
                <p>Thank you for your purchase!</p>
                <p>Please arrive at least 30 minutes before the showtime.</p>
                <div class="buttons">
                    <a href="movies.php" class="btn-home">Back to Home</a>
                    <button onclick="window.print()" class="btn-print">Print Tickets</button>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/cinema_footer.html'; ?>
</body>
</html>