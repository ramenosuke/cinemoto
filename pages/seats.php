<?php
require_once '../includes/auth_check.php';

session_start();

// Get movie and showtime from URL
$movie_id = $_GET['movie'] ?? null;
$showtime = $_GET['time'] ?? null;

// Sample movie data (in a real application, this would come from a database)
$movies = [
    1 => [
        'title' => 'Lilo And Stitch',
        'image' => '../assets/lilo.jpg',
        'price' => 350,
        'synopsis' => 'The wildly funny and touching story of a lonely Hawaiian girl and the fugitive alien who helps to mend her broken family.'
    ],
    2 => [
        'title' => 'Thunderbolts',
        'image' => '../assets/thunderbolts.jpeg',
        'price' => 350,
        'synopsis' => 'Ensnared in a death trap, an unconventional team of antiheroes -- Yelena Belova, Bucky Barnes, Red Guardian, Ghost, Taskmaster and John Walker -- embarks on a dangerous mission that forces them to confront the darkest corners of their pasts.'
    ],
    3 => [
        'title' => 'Karate Kid',
        'image' => '../assets/karate.jpg',
        'price' => 350,
        'synopsis' => 'After kung fu prodigy Li Fong relocates to New York City, he attracts unwanted attention from a local karate champion and embarks on a journey to enter the ultimate karate competition with the help of Mr. Han and Daniel LaRusso.'
    ]
];

// Validate movie ID
if (!isset($movies[$movie_id])) {
    header("Location: movies.php");
    exit;
}

$movie = $movies[$movie_id];

// Generate seat layout
$rows = range('A', 'J');
$seats_per_row = 10;
$selected_seats = [];

// Handle seat selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_seats'])) {
    $selected_seats = $_POST['selected_seats'];
    
    // Add to cart
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $_SESSION['cart'][] = [
        'movie' => $movie['title'],
        'image' => $movie['image'],
        'date' => date('Y-m-d'),
        'time' => $showtime,
        'seats' => implode(', ', $selected_seats),
        'price' => $movie['price'],
        'quantity' => count($selected_seats)
    ];
    
    header("Location: cart.php");
    exit;
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
                            <?php for ($i = 1; $i <= $seats_per_row; $i++): ?>
                                <label class="seat">
                                    <input type="checkbox" name="selected_seats[]" value="<?php echo $row . $i; ?>">
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