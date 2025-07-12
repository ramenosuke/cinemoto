<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>

<style>
    :root {
        --background-color: #1a1a1a;
        --primary-color: #e50914;
        --text-color: #ffffff;
    }

    header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 6rem;
        background-color: var(--background-color);
        z-index: 1000;
        padding: 0 9%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }

    .header-content {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .logo {
        font-size: 2.5rem;
        color: var(--primary-color);
        font-weight: bold;
        text-decoration: none;
    }

    .nav-menu {
        display: flex;
        align-items: center;
        gap: 2rem;
    }

    .nav-menu a {
        color: var(--text-color);
        text-decoration: none;
        font-size: 1.1rem;
        transition: color 0.3s ease;
    }

    .nav-menu a:hover {
        color: var(--primary-color);
    }

    .user-menu {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .user-menu span {
        color: var(--text-color);
        font-size: 1rem;
    }

    .login-btn, .signup-btn {
        padding: 0.5rem 1.5rem;
        border-radius: 4px;
        font-weight: bold;
    }

    .login-btn {
        color: var(--text-color);
        border: 1px solid var(--text-color);
    }

    .signup-btn {
        background: var(--primary-color);
        color: var(--text-color);
    }

    .signup-btn:hover {
        background: #ff0f1a;
    }

    .logout-btn {
        color: var(--primary-color);
        font-weight: bold;
    }

    @media (max-width: 768px) {
        header {
            padding: 0 2rem;
        }

        .nav-menu {
            gap: 1rem;
        }

        .user-menu span {
            display: none;
        }
    }
</style>

<header>
    <div class="header-content">
        <a href="movies.php" class="logo">Cinemoto</a>
        <nav class="nav-menu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="movies.php">Movies</a>
                <a href="cart.php">Cart</a>
                <div class="user-menu">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            <?php else: ?>
                <a href="login.php" class="login-btn">Login</a>
                <a href="signup.php" class="signup-btn">Sign Up</a>
            <?php endif; ?>
        </nav>
    </div>
</header> 