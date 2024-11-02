<?php
session_start();
include('dbconnection.php');

if (!isset($_SESSION['username'])) {
    header('Location: signin.php');
    exit();
}

function executeMessage($message, $status)
{
    $_SESSION['message'] = 'message=' . $message . '&status=' . $status;
    header('Location: profile.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $username = $_SESSION['username'];

    $sql = "SELECT password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET password = ? WHERE username = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param('ss', $hashed_password, $username);

            if ($update_stmt->execute()) {
                executeMessage("Password changed successfully.", "success");
            } else {
                executeMessage("Failed to change password.", "error");
            }
        } else {
            executeMessage("New passwords do not match.", "error");
        }
    } else {
        executeMessage("Current password is incorrect.", "error");
    }
}

// Fetch user's bookings
$username = $_SESSION['username'];
$sql = "SELECT b.id, m.title, b.date, b.time, b.seats, b.car_park, b.booking_at 
        FROM bookings b 
        JOIN movies m ON b.movie_id = m.movie_id
        WHERE b.username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-Theater - Profile</title>
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
            background-image: url(./assets/images/bg.jpg);
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
                    echo '<li><a href="logout.php"><button class="btn-primary-nav">Logout</button></a></li>';
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
        <section class="addmovie">
            <div class="addmovie-card">
                <h1>User Profile</h1>
                <form method="POST">
                    <div class="form-row">
                        <label for="username">Username:</label>
                        <input type="text" name="username" id="username" value="<?= $_SESSION['username'] ?>" readonly>
                    </div>
                    <div class="form-row">
                        <label for="current_password">Current Password:</label>
                        <input type="password" name="current_password" id="current_password" required>
                    </div>
                    <div class="form-row">
                        <label for="new_password">New Password:</label>
                        <input type="password" name="new_password" id="new_password" required>
                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" name="confirm_password" id="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password">Change Password</button>
                </form>
            </div>
        </section>

        <section class="moviestable">
            <div class="moviestable-card">
                <h1>Your Bookings</h1>
                <table class="movies-table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Movie Title</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Seats</th>
                            <th>Car Park</th>
                            <th>Booking At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?= $booking['id'] ?></td>
                                <td><?= $booking['title'] ?></td>
                                <td><?= $booking['date'] ?></td>
                                <td><?= $booking['time'] ?></td>
                                <td><?= $booking['seats'] ?></td>
                                <td><?= $booking['car_park'] ?></td>
                                <td><?= $booking['booking_at'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <footer>
        <p>Contact us <a href="mailto:support@mtheater.lk">support@mtheater.lk</a></p>
        <p>&copy; 2024 mtheater.lk</p>
    </footer>
</body>

</html>