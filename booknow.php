<?php
session_start();

include('dbconnection.php');

if (isset($_GET['movie'])) {
    $movieId = htmlspecialchars($_GET['movie']);

    if (!isset($_SESSION['username'])) {
        header('Location: signin.php?movie=' . $_GET['movie']);
        exit();
    }

    $sql = "SELECT * FROM movies WHERE movie_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        exit;
    }

    $bindResult = $stmt->bind_param('s', $movieId);
    if ($bindResult === false) {
        echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        exit;
    }

    $executeResult = $stmt->execute();
    if ($executeResult === false) {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        exit;
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $movieDetails = $result->fetch_assoc();
    } else {
        echo '<p>Movie not found.</p>';
        exit;
    }
} else {
    echo '<p>No movie selected.</p>';
    exit;
}

$seatCount = 120; // initial seat count
$seatPrice = 700; // Seat price in LKR

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['booking-date'], $_POST['showtime'], $_POST['seats'])) {
        $username = $_SESSION['username'];
        $bookingDate = htmlspecialchars($_POST['booking-date']);
        $showtime = htmlspecialchars($_POST['showtime']);
        $seats = intval($_POST['seats']);
        $carPark = isset($_POST['car_park']) && $_POST['car_park'] === 'yes' ? 'yes' : 'no';
        $price = $seatPrice * $seats; // Calculate the price

        // Check the number of booked seats for the selected movie, date, and showtime
        $sql = "SELECT SUM(seats) AS booked_seats FROM bookings WHERE movie_id = ? AND date = ? AND time = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $movieId, $bookingDate, $showtime);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $bookedSeats = $row['booked_seats'] ?? 0;
        $availableSeats = $seatCount - $bookedSeats;

        if ($seats > $availableSeats) {
            $error = 'Not enough seats available.';
        } else {
            // Insert the booking into the bookings table
            $sql = "INSERT INTO bookings (movie_id, date, username, time, seats, car_park, price, booking_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssisi', $movieId, $bookingDate, $username, $showtime, $seats, $carPark, $price);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $_SESSION['booking'] = 'movie=' . $movieId . '&bookingDate=' . $bookingDate . '&showtime=' . $showtime . '&seats=' . $seats;
                header('Location: confirmation.php');
                exit();
            } else {
                $error = 'Booking failed. Please try again.';
            }
        }
    } else {
        $error = 'Invalid form data.';
    }
}

// Fetch booked seats count for updating max seats
if (isset($_POST['booking-date'], $_POST['showtime'])) {
    $bookingDate = htmlspecialchars($_POST['booking-date']);
    $showtime = htmlspecialchars($_POST['showtime']);

    $sql = "SELECT SUM(seats) AS booked_seats FROM bookings WHERE movie_id = ? AND date = ? AND time = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $movieId, $bookingDate, $showtime);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $bookedSeats = $row['booked_seats'] ?? 0;
    $availableSeats = $seatCount - $bookedSeats;
} else {
    $availableSeats = $seatCount;
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
            <?php $backgroundImage = '';

            if (isset($movieDetails['banner']) && $movieDetails['banner']) {
                $backgroundImage = 'url("assets/movie_images/' . $movieDetails['banner'] . '")';
            } elseif (isset($movieDetails['image']) && $movieDetails['image']) {
                $backgroundImage = 'url("assets/movie_images/' . $movieDetails['image'] . '")';
            }

            ?>background-image: <?php echo $backgroundImage;
                            ?>;
            background-attachment: fixed;
            background-size: cover;
            background-position: center center;
            z-index: -1;
        }
    </style>
    <script>
        function updatePrice() {
            var seatPrice = <?php echo $seatPrice; ?>;
            var seats = document.getElementById('seats').value;
            var price = seatPrice * seats;
            document.getElementById('price').textContent = 'Total LKR. ' + price;
        }
    </script>
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
    <?php
    if (isset($_SESSION['message'])) {
        parse_str($_SESSION['message'], $messageDetails);
        if ($messageDetails['status'] == "success") {
            echo '<center><p style="color: green;">' . $messageDetails['message'] . '</p></center>';
        } else {
            echo '<center><p style="color: red;">' . $messageDetails['message'] . '</p></center>';
        }
        unset($_SESSION['message']);
    }
    ?>
    <div class="main-content">
        <section class="booknow">
            <div class="booknow-card">
                <h1>Book Now</h1>
                <form action="booknow.php?<?php echo htmlspecialchars($_SERVER['QUERY_STRING']); ?>" method="POST">
                    <label for="booking-date">Select Booking Date:</label>
                    <?php
                    $today = date('Y-m-d');
                    if ($today >= $movieDetails['startdate'] && $today <= $movieDetails['enddate']) {
                        $start = $today;
                        $end = $movieDetails['enddate'];
                    } elseif ($today < $movieDetails['startdate']) {
                        $start = $movieDetails['startdate'];
                        $end = $movieDetails['enddate'];
                    }
                    ?>
                    <input type="date" id="booking-date" name="booking-date" min="<?php echo $start; ?>"
                        max="<?php echo $end; ?>" required>
                    <label for="showtime">Show Time:</label>
                    <select id="showtime" name="showtime" required>
                        <?php
                        $showtimes = explode('|', $movieDetails['showtimes']);
                        foreach ($showtimes as $time) {
                            echo '<option value="' . htmlspecialchars($time) . '">' . htmlspecialchars($time) . '</option>';
                        }
                        ?>
                    </select>
                    <label for="seats">How many seats:</label>
                    <input type="number" id="seats" name="seats" min="1" max="<?php echo $availableSeats; ?>" required
                        oninput="updatePrice()">
                    <p id="price">Total LKR. 0</p>
                    <label for="car_park">
                        <input type="checkbox" id="car_park" name="car_park" value="yes">
                        <span>I will use car park</span>
                    </label>
                    <button type="submit">Book Now</button>
                </form>
                <?php if (!empty($error)): ?>
                    <div class="error-message" style="padding: 1rem; color: red; font-size: 14px;"><?php echo $error; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <footer>
        <p>Contact us <a href="mailto:support@mtheater.lk">support@mtheater.lk</a></p>
        <p>&copy; 2024 mtheater.lk</p>
    </footer>
</body>

</html>