<?php
require_once '../includes/db_connect.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');

    // Validate input
    if (empty($username) || empty($password) || empty($email) || empty($full_name)) {
        $error = "All fields are required";
    } else {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Username or email already exists";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $hashed_password, $email, $full_name]);
                
                $success = "Registration successful! You can now login.";
                // Redirect to login page after 2 seconds
                header("refresh:2;url=login.php");
            }
        } catch (PDOException $e) {
            $error = "Registration failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Cinema</title>
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>
    <?php include '../includes/cinema_header.php'; ?>

    <div class="auth-container">
        <div class="auth-box">
            <h1>Create Account</h1>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="region">Region</label>
                    <select id="region" name="region" class="form-control" required>
                        <option selected disabled>Select Region</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="province">Province</label>
                    <select id="province" name="province" class="form-control" required>
                        <option selected disabled>Select Province</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="city">City/Municipality</label>
                    <select id="city" name="city" class="form-control" required>
                        <option selected disabled>Select City/Municipality</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="barangay">Barangay</label>
                    <select id="barangay" name="barangay" class="form-control" required>
                        <option selected disabled>Select Barangay</option>
                    </select>
                </div>

                <button type="submit" class="auth-button">Sign Up</button>
            </form>

            <p class="auth-link">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </div>

    <?php include '../includes/cinema_footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(function() {
        // Populate regions from SQL
        $.getJSON('location_api.php?type=region', function(data) {
            data.forEach(function(r) {
                $('#region').append(`<option value="${r.code}">${r.name}</option>`);
            });
        });

        // On region change, load provinces
        $('#region').on('change', function() {
            const code = $(this).val();
            $('#province').html('<option selected disabled>Select Province</option>');
            $('#city').html('<option selected disabled>Select City/Municipality</option>');
            $('#barangay').html('<option selected disabled>Select Barangay</option>');
            if (code) {
                $.getJSON('location_api.php?type=province&parent=' + code, function(data) {
                    data.forEach(function(p) {
                        $('#province').append(`<option value="${p.code}">${p.name}</option>`);
                    });
                });
            }
        });

        // On province change, load cities/municipalities
        $('#province').on('change', function() {
            const code = $(this).val();
            $('#city').html('<option selected disabled>Select City/Municipality</option>');
            $('#barangay').html('<option selected disabled>Select Barangay</option>');
            if (code) {
                $.getJSON('location_api.php?type=city&parent=' + code, function(data) {
                    data.forEach(function(c) {
                        $('#city').append(`<option value="${c.code}">${c.name}</option>`);
                    });
                });
            }
        });

        // On city change, load barangays
        $('#city').on('change', function() {
            const code = $(this).val();
            $('#barangay').html('<option selected disabled>Select Barangay</option>');
            if (code) {
                $.getJSON('location_api.php?type=barangay&parent=' + code, function(data) {
                    data.forEach(function(b) {
                        $('#barangay').append(`<option value="${b.code}">${b.name}</option>`);
                    });
                });
            }
        });
    });
    </script>
</body>
</html>