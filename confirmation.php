<?php
session_start();

include('dbconnection.php');

$error = '';

if (isset($_SESSION['booking'])) {
    // Extract the booking details from the session variable
    parse_str($_SESSION['booking'], $bookingDetails);

    // Ensure all required details are extracted
    if (isset($bookingDetails['movie'], $bookingDetails['bookingDate'], $bookingDetails['showtime'], $bookingDetails['seats'])) {
        $movieId = $bookingDetails['movie'];
        $bookingDate = $bookingDetails['bookingDate'];
        $showtime = $bookingDetails['showtime'];
        $seats = $bookingDetails['seats'];

        // Check if username is set in session
        if (!isset($_SESSION['username'])) {
            // Redirect to signin.php with movie parameter
            header('Location: signin.php?movie=' . $movieId);
            exit();
        }

        // Query database for movie details
        $sql = "SELECT * FROM movies WHERE movie_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $error = 'There is some error in your booking! Please try again later.';
        } else {
            $bindResult = $stmt->bind_param('s', $movieId);
            if ($bindResult === false) {
                $error = 'There is some error in your booking! Please try again later.';
            } else {
                $executeResult = $stmt->execute();
                if ($executeResult === false) {
                    $error = 'There is some error in your booking! Please try again later.';
                } else {
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $movieDetails = $result->fetch_assoc();
                        unset($_SESSION['booking']); //remove booking from session to prevent reload issues
                    } else {
                        $error = 'Movie not found! Please try another movie.';
                    }
                }
            }
        }
    } else {
        $error = 'Booking details are incomplete.';
    }
} else {
    if (!isset($_SESSION['username'])) {
        // Redirect to signin.php with movie parameter
        header('Location: signin.php');
        exit();
    } else {

        $error = 'No booking details found in session.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-Theater - Book Now</title>
    <link rel="stylesheet" type="text/css" href="./assets/styles.css">
    <link rel="icon" href="./assets/images/logo.ico" type="image/x-icon">
    <style>
        .main-content {
            position: relative;
            overflow: hidden;
        }

        .main-content::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url(<?php echo isset($movieDetails['image']) ? 'assets/movie_images/' . $movieDetails['image'] : 'assets/images/alertbg.jpg'; ?>);
            background-attachment: fixed;
            background-size: cover;
            background-position: center center;
            /* filter: blur(5px); */
            z-index: -1;
        }
    </style>
</head>

<body>
    <header>
        <div class="logo">

            <span class="logo-text" onclick="location.href='dashboard.php';"><b>M-Theater</b></span>
        </div>
        <nav>
            <ul>
                <?php
                if (isset($_SESSION['usertype']) && $_SESSION['usertype'] == 'admin') {
                    echo '<li><a href="admin.php"><button class="btn-primary-nav">Admin</button></a></li>';
                } elseif (isset($_SESSION['usertype']) && $_SESSION['usertype'] == 'staff') {
                    echo '<li><a href="staff.php"><button class="btn-primary-nav">Staff</button></a></li>';
                }
                ?>
                <li><a href="dashboard.php"><button class="btn-primary-nav">Home</button></a></li>
                <li><a href="movies.php"><button class="btn-primary-nav">Movies</button></a></li>
                <li><a href="about.php"><button class="btn-primary-nav">About</button></a></li>
                <?php
                if (!isset($_SESSION['username'])) {
                    echo '<li><a href="signin.php"><button class="btn-primary-nav">Signin</button></a></li>';
                } else {
                    echo '<li><a href="profile.php"><button class="btn-primary-nav">Profile</button></a></li>';
                }
                ?>
            </ul>
        </nav>
    </header>
    <div class="main-content">
        <section class="confirmation">
            <div class="confirmation-card">
                <h1>Booking Confirmation</h1>
                <?php
                if ($error) {
                    echo "<p>{$error}</p>";
                    echo '<a href="dashboard.php"><button type="submit">Home</button></a>';
                } else {
                    echo '<p>Thank you for your booking!</p>';
                    echo '<p>You have successfully booked ' . htmlspecialchars($seats) . ' ' . (($seats > 1) ? 'seats' : 'seat') . ' for ' . htmlspecialchars($bookingDate) . ' at ' . htmlspecialchars($showtime) . '.</p>';
                    echo '<p>Movie: ' . htmlspecialchars($movieDetails['title']) . '</p>';
                    echo '<p>Enjoy your movie experience!</p>';
                }
                ?>
            </div>
        </section>
    </div>
    <footer>
        <p>Contact us <a href="mailto:support@mtheater.lk">support@mtheater.lk</a></p>
        <p>&copy; 2024 mtheater.lk</p>
    </footer>
</body>

</html>