<?php
require_once '../includes/auth_check.php';

session_start();

// Sample movie data (in a real application, this would come from a database)
$movies = [
    [
        'id' => 1,
        'title' => 'Lilo & Stitch: The Lost Adventure',
        'image' => '../assets/lilo.jpg',
        'description' => 'The wildly funny and touching story of a lonely Hawaiian girl and the fugitive alien who helps to mend her broken family.',
        'duration' => '1h 48m',
        'rating' => 'PG',
        'price' => 350,
        'showtimes' => [
            ['time' => '10:00 AM', 'seats' => 45],
            ['time' => '1:30 PM', 'seats' => 30],
            ['time' => '4:45 PM', 'seats' => 25],
            ['time' => '8:00 PM', 'seats' => 20]
        ]
    ],
    [
        'id' => 2,
        'title' => 'Thunderbolts',
        'image' => '../assets/thunderbolts.jpeg',
        'description' => 'Ensnared in a death trap, an unconventional team of antiheroes -- Yelena Belova, Bucky Barnes, Red Guardian, Ghost, Taskmaster and John Walker -- embarks on a dangerous mission that forces them to confront the darkest corners of their pasts.',
        'duration' => '2h 6m',
        'rating' => 'PG-13',
        'price' => 350,
        'showtimes' => [
            ['time' => '11:00 AM', 'seats' => 40],
            ['time' => '2:30 PM', 'seats' => 35],
            ['time' => '5:45 PM', 'seats' => 30],
            ['time' => '9:00 PM', 'seats' => 25]
        ]
    ],
    [
        'id' => 3,
        'title' => 'Karate Kid: The Legacy Continues',
        'image' => '../assets/karate.jpg',
        'description' => 'After kung fu prodigy Li Fong relocates to New York City, he attracts unwanted attention from a local karate champion and embarks on a journey to enter the ultimate karate competition with the help of Mr. Han and Daniel LaRusso.',
        'duration' => '1h 34m',
        'rating' => 'PG-13',
        'price' => 350,
        'showtimes' => [
            ['time' => '10:30 AM', 'seats' => 50],
            ['time' => '2:00 PM', 'seats' => 45],
            ['time' => '5:15 PM', 'seats' => 40],
            ['time' => '8:30 PM', 'seats' => 35]
        ]
    ]
];
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