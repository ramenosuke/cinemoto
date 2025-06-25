<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';

session_start();

// Get movie and showtime from URL
$movie_id = $_GET['movie'] ?? null;
$showtime = $_GET['time'] ?? null;

// Fetch movie info from DB
$movieStmt = $pdo->prepare('SELECT * FROM movies WHERE movie_id = ?');
$movieStmt->execute([$movie_id]);
$movieRow = $movieStmt->fetch();
if (!$movieRow) {
    header("Location: movies.php");
    exit;
}
$movie = [
    'title' => $movieRow['title'],
    'image' => $movieRow['image_path'],
    'price' => $movieRow['price'],
    'synopsis' => '' // Add if you have it in DB
];

// Get schedule_id for this movie and showtime (no showdate column)
$scheduleStmt = $pdo->prepare('SELECT * FROM schedules WHERE movie_id = ? AND showtime = ?');
$scheduleStmt->execute([$movie_id, date('H:i:s', strtotime($showtime))]);
$schedule = $scheduleStmt->fetch();
if (!$schedule) {
    header("Location: movies.php");
    exit;
}
$schedule_id = $schedule['schedule_id'];

// Fetch already booked seats for this schedule
$bookedSeatsStmt = $pdo->prepare('SELECT seat_number FROM booked_seats WHERE schedule_id = ?');
$bookedSeatsStmt->execute([$schedule_id]);
$bookedSeats = $bookedSeatsStmt->fetchAll(PDO::FETCH_COLUMN);

// Generate seat layout
$rows = range('A', 'J');
$seats_per_row = 10;
$selected_seats = [];

// Handle seat selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_seats'])) {
    $selected_seats = $_POST['selected_seats'];
    // Prevent double booking: check if any selected seat is already booked
    $conflict = false;
    foreach ($selected_seats as $seat) {
        if (in_array($seat, $bookedSeats)) {
            $conflict = true;
            break;
        }
    }
    if ($conflict) {
        $error = "One or more selected seats are already booked. Please choose different seats.";
    } else {
        // Add to cart (or book directly as needed)
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $_SESSION['cart'][] = [
            'movie' => $movie['title'],
            'image' => $movie['image'],
            'date' => date('Y-m-d'),
            'time' => $showtime,
            'schedule_id' => $schedule_id,
            'seats' => implode(', ', $selected_seats),
            'seat_array' => $selected_seats,
            'price' => $movie['price'],
            'quantity' => count($selected_seats)
        ];
        header("Location: cart.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Seats | Cinema</title>
    <link rel="stylesheet" href="../css/seats.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/cinema_header.php'; ?>

    <div class="seats-container">
        <div class="movie-info">
            <img src="<?php echo htmlspecialchars($movie['image']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
            <div>
                <h1><?php echo htmlspecialchars($movie['title']); ?></h1>
                <p class="showtime">Showtime: <?php echo htmlspecialchars($showtime); ?></p>
                <p class="synopsis"><strong>Synopsis:</strong> <?php echo htmlspecialchars($movie['synopsis']); ?></p>
                <p class="price">Price per seat: ₱<?php echo number_format($movie['price'], 2); ?></p>
            </div>
        </div>

        <div class="screen">
            <div class="screen-text">SCREEN</div>
        </div>

        <form method="post" class="seats-form">
            <div class="seats-grid">
                <?php foreach ($rows as $row): ?>
                    <div class="row">
                        <div class="row-label"><?php echo $row; ?></div>
                        <div class="seats">
                            <?php for ($i = 1; $i <= $seats_per_row; $i++): 
                                $seatVal = $row . $i;
                                $isOccupied = in_array($seatVal, $bookedSeats);
                            ?>
                                <label class="seat <?php echo $isOccupied ? 'occupied' : ''; ?>">
                                    <input type="checkbox" name="selected_seats[]" value="<?php echo $seatVal; ?>" <?php echo $isOccupied ? 'disabled' : ''; ?>>
                                    <span class="seat-number"><?php echo $i; ?></span>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="seat-legend">
                <div class="legend-item">
                    <div class="seat-sample available"></div>
                    <span>Available</span>
                </div>
                <div class="legend-item">
                    <div class="seat-sample selected"></div>
                    <span>Selected</span>
                </div>
                <div class="legend-item">
                    <div class="seat-sample occupied"></div>
                    <span>Occupied</span>
                </div>
            </div>

            <div class="selection-summary">
                <div class="selected-seats">
                    <h3>Selected Seats:</h3>
                    <div id="selected-seats-list">None</div>
                </div>
                <div class="total-price">
                    <h3>Total Price:</h3>
                    <div id="total-price">₱0.00</div>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-message" style="color:red; text-align:center; margin:10px 0;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="proceed-btn" disabled>Proceed to Checkout</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            const selectedSeatsList = document.getElementById('selected-seats-list');
            const totalPrice = document.getElementById('total-price');
            const proceedBtn = document.querySelector('.proceed-btn');
            const pricePerSeat = <?php echo $movie['price']; ?>;
            
            function updateSelection() {
                const selectedSeats = Array.from(checkboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.value);
                
                if (selectedSeats.length > 0) {
                    selectedSeatsList.textContent = selectedSeats.join(', ');
                    totalPrice.textContent = '₱' + (selectedSeats.length * pricePerSeat).toFixed(2);
                    proceedBtn.disabled = false;
                } else {
                    selectedSeatsList.textContent = 'None';
                    totalPrice.textContent = '₱0.00';
                    proceedBtn.disabled = true;
                }
            }
            
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelection);
            });
        });
    </script>

    <?php include '../includes/cinema_footer.php'; ?>
</body>
</html>