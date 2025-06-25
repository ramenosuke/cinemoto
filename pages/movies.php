<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';

// Fetch movies from the database
$movies = [];
$movieStmt = $pdo->query('SELECT * FROM movies');
$moviesData = array_slice($movieStmt->fetchAll(), 0, 3); // Only show the first 3 movies

foreach ($moviesData as $movieRow) {
    $movie = [
        'id' => $movieRow['movie_id'],
        'title' => $movieRow['title'],
        'image' => $movieRow['image_path'],
        'description' => '', // Add description if you have it in DB
        'duration' => '',    // Add duration if you have it in DB
        'rating' => '',      // Add rating if you have it in DB
        'price' => $movieRow['price'],
        'showtimes' => []
    ];
    // Fetch showtimes for this movie (no showdate column)
    $showtimeStmt = $pdo->prepare('SELECT * FROM schedules WHERE movie_id = ? ORDER BY showtime');
    $showtimeStmt->execute([$movieRow['movie_id']]);
    $showtimes = $showtimeStmt->fetchAll();
    foreach ($showtimes as $showtimeRow) {
        // Count booked seats for this schedule
        $seatCountStmt = $pdo->prepare('SELECT COUNT(*) as booked FROM booked_seats WHERE schedule_id = ?');
        $seatCountStmt->execute([$showtimeRow['schedule_id']]);
        $booked = $seatCountStmt->fetch();
        $totalSeats = 100; // Set your total seats per showtime here
        $availableSeats = $totalSeats - $booked['booked'];
        $movie['showtimes'][] = [
            'schedule_id' => $showtimeRow['schedule_id'],
            'time' => date('g:i A',  strtotime($showtimeRow['showtime'])),
            'seats' => $availableSeats
        ];
    }
    $movies[] = $movie;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Now Showing | Cinema</title>
    <link rel="stylesheet" href="../css/movies.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <?php include '../includes/cinema_header.php'; ?>

    <div class="movies-container">
        <h1>Now Showing</h1>

        <div class="movies-grid">
            <?php foreach ($movies as $movie): ?>
                <div class="movie-card">
                    <div class="movie-poster">
                        <img src="<?php echo htmlspecialchars($movie['image']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                        <div class="movie-rating"><?php echo htmlspecialchars($movie['rating']); ?></div>
                    </div>
                    <div class="movie-info">
                        <h2><?php echo htmlspecialchars($movie['title']); ?></h2>
                        <p class="movie-duration"><?php echo htmlspecialchars($movie['duration']); ?></p>
                        <p class="movie-description"><?php echo htmlspecialchars($movie['description']); ?></p>

                        <div class="showtimes">
                            <h3>Showtimes</h3>
                            <div class="time-slots" id="time-slots-<?php echo $movie['id']; ?>">
                                <?php foreach ($movie['showtimes'] as $showtime): ?>
                                    <a href="javascript:void(0)"
                                        class="time-slot <?php echo $showtime['seats'] < 10 ? 'low-seats' : ''; ?>"
                                        data-movie="<?php echo $movie['id']; ?>"
                                        data-time="<?php echo htmlspecialchars($showtime['time']); ?>">
                                        <?php echo htmlspecialchars($showtime['time']); ?>
                                        <span class="seats-left"><?php echo $showtime['seats']; ?> seats left</span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="movie-price">
                            <span>₱<?php echo number_format($movie['price'], 2); ?></span>
                            <a href="#"
                                class="book-btn"
                                id="book-btn-<?php echo $movie['id']; ?>"
                                data-movie="<?php echo $movie['id']; ?>"
                                disabled
                                style="pointer-events: none; opacity: 0.5;">Book Now</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include '../includes/cinema_footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.time-slots').forEach(function(slotGroup) {
                slotGroup.addEventListener('click', function(e) {
                    if (e.target.classList.contains('time-slot')) {
                        e.preventDefault();
                        // Remove active from all
                        slotGroup.querySelectorAll('.time-slot').forEach(function(ts) {
                            ts.classList.remove('active');
                        });
                        // Add active to clicked
                        e.target.classList.add('active');
                        // Enable Book Now button
                        var movieId = e.target.getAttribute('data-movie');
                        var time = e.target.getAttribute('data-time');
                        var bookBtn = document.getElementById('book-btn-' + movieId);
                        bookBtn.href = 'seats.php?movie=' + movieId + '&time=' + encodeURIComponent(time);
                        bookBtn.style.pointerEvents = 'auto';
                        bookBtn.style.opacity = '1';
                        bookBtn.removeAttribute('disabled');
                    }
                });
            });
        });
    </script>
</body>

</html>