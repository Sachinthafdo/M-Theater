<?php
session_start();
include('dbconnection.php');

if (!isset($_SESSION['username'])) {
    header('Location: signin.php');
    exit();
}
if ($_SESSION['usertype'] && $_SESSION['usertype'] != 'admin') {
    echo '<p>Page not Found.</p>';
    exit();
}

function executeMessage($message, $status)
{
    $_SESSION['message'] = 'message=' . $message . '&status=' . $status;
    header('Location: admin.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_movie'])) {

    $movie_id = $_POST['movieid'];
    $title = $_POST['title'];
    $image = $_FILES['image']['name'];
    $banner = $_FILES['banner']['name'];
    $startdate = $_POST['startdate'];
    $enddate = $_POST['enddate'];
    $showtimes = $_POST['showtimes'];

    $target_dir = __DIR__ . "/assets/movie_images/";

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . basename($image));
    move_uploaded_file($_FILES['banner']['tmp_name'], $target_dir . basename($banner));

    $sql = "INSERT INTO movies (movie_id, title,  image, banner, startdate, enddate,  showtimes) 
            VALUES (?,  ?,  ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssss', $movie_id,  $title,  $image, $banner, $startdate, $enddate,  $showtimes);
    if ($stmt->execute()) {
        executeMessage("Movie added successfully.", "success");
    } else {
        executeMessage("Failed to add movie.", "error");
    }
}

// Handle delete movie
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_movie'])) {
    $movie_id = $_POST['movie_id'];
    $sql = "DELETE FROM movies WHERE movie_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $movie_id);
    if ($stmt->execute()) {
        executeMessage("Movie deleted successfully.", "success");
    } else {
        executeMessage("Failed to delete movie.", "error");
    }
}

// Handle update movie
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_movie'])) {
    $movie_id = $_POST['movie_id'];
    $title = $_POST['title'];
    $startdate = $_POST['startdate'];
    $enddate = $_POST['enddate'];
    $showtimes = $_POST['showtimes'];

    $sql = "UPDATE movies SET title = ?, startdate = ?, enddate = ?, showtimes = ? WHERE movie_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssss', $title, $startdate, $enddate, $showtimes, $movie_id);
    if ($stmt->execute()) {
        executeMessage("Movie updated successfully.", "success");
    } else {
        executeMessage("Failed to update movie.", "error");
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' &&  isset($_POST['create_new_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $usertype = $_POST['usertype'];

    // Check if email already exists
    $checkEmailQuery = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($checkEmailQuery);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        executeMessage("Email already registered. Please use another email.", "error");
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user into database
        $insertQuery = "INSERT INTO users (username, email, password,usertype) VALUES (?, ?, ?,?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param('ssss', $username, $email, $hashed_password, $usertype);

        if ($stmt->execute()) {
            executeMessage("User added successfully.", "success");
        } else {
            executeMessage("Failed to add new user.", "error");
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' &&  isset($_POST['toggle_user_status'])) {
    $user_id = $_POST['user_id'];
    $status = $_POST['status'];
    $new_status = ($status == 'active') ? 'deactive' : 'active';

    $sql = "UPDATE users SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $new_status, $user_id);
    if ($stmt->execute()) {
        executeMessage("User status updated successfully.", "success");
    } else {
        executeMessage("Failed to update user status.", "error");
    }
}

// Handle delete booking
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_booking'])) {
    $booking_id = $_POST['booking_id'];
    $sql = "DELETE FROM bookings WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $booking_id);
    if ($stmt->execute()) {
        executeMessage("Booking deleted successfully.", "success");
    } else {
        executeMessage("Failed to delete booking.", "error");
    }
}


// Fetch all movies
$sql = "SELECT * FROM movies";
$result = $conn->query($sql);
$movies = $result->fetch_all(MYSQLI_ASSOC);

// Fetch all users
$sql = "SELECT * FROM users";
$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);

$normal_users = array_filter($users, function ($user) {
    return $user['usertype'] == 'user';
});
$staff_members = array_filter($users, function ($user) {
    return $user['usertype'] == 'staff';
});


// Fetch all bookings
$sql = "SELECT b.id, m.title, b.date, b.username, b.time, b.seats, b.car_park, b.booking_at 
        FROM bookings b 
        JOIN movies m ON b.movie_id = m.movie_id";
$result = $conn->query($sql);
$bookings = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-Theater - Admin</title>
    <link rel="stylesheet" type="text/css" href="./assets/styles.css">
    <link rel="icon" href="./assets/images/logo.ico" type="image/x-icon">
    <style>
        .admin-content-section {
            display: none;
        }

        .admin-content-section.active {
            display: block;
        }

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
        <section class="parallax">
            <h1>Hello My Boss!!</h1>
            <p>new movie on the way?</p>

        </section>

        <section class="admin">
            <section id="movies" class="admin-actions card">
                <h2>Movies</h2>
            </section>
            <section id="users" class="admin-actions card">
                <h2>Users</h2>
            </section>
            <section id="staffmembers" class="admin-actions card">
                <h2>StaffMembers</h2>
            </section>
            <section id="bookings" class="admin-actions card">
                <h2>Bookings</h2>
            </section>
        </section>

        <div id="content-movies" class="admin-content-section">
            <section class="addmovie">
                <div class="addmovie-card">
                    <h1>Add New Movie</h1>
                    <form method="POST" enctype="multipart/form-data" onsubmit="combineShowtimes(event)">
                        <div class="form-row">
                            <label for="movieid">Movie Id:</label>
                            <input type="text" name="movieid" id="movieid" required>
                            <label for="title">Title:</label>
                            <input type="text" name="title" id="title" required>
                        </div>
                        <div class="form-row">
                            <label for="image">Image:</label>
                            <input type="file" name="image" id="image" required>
                            <label for="banner">Banner:</label>
                            <input type="file" name="banner" id="banner" required>
                        </div>
                        <div class="form-row">
                            <label for="startdate">Start Date:</label>
                            <input type="date" name="startdate" id="startdate" required>
                            <label for="enddate">End Date:</label>
                            <input type="date" name="enddate" id="enddate" required>
                        </div>
                        <div class="form-row" id="showtimes-container">
                            <label for="showtimes">Showtimes:</label>
                            <input type="time" name="showtime" class="showtime-input" required>
                        </div>
                        <div class="form-row">
                            <button id="addnewtime" type="button" onclick="addShowtimeInput()" style="width:200px;margin:2rem;">Add New Showtime</button>
                        </div>
                        <input type="hidden" name="showtimes" id="showtimes">
                        <input type="hidden" name="add_movie" id="add_movie" value="true">
                        <button type="submit" name="add_movie" style="width:250px;">Add Movie</button>
                    </form>
                </div>
            </section>

            <script>
                function addShowtimeInput() {
                    const container = document.getElementById('showtimes-container');
                    const input = document.createElement('input');
                    input.type = 'time';
                    input.name = 'showtime';
                    input.classList.add('showtime-input');
                    input.required = true;
                    container.appendChild(input);
                }

                function combineShowtimes(event) {
                    event.preventDefault();
                    const showtimeInputs = document.querySelectorAll('.showtime-input');
                    const showtimes = Array.from(showtimeInputs)
                        .map(input => input.value)
                        .filter(value => value !== '') // Filter out empty values
                        .join(' | ');
                    document.getElementById('showtimes').value = showtimes;
                    event.target.submit();
                }
            </script>


            <section class="moviestable">
                <div class="moviestable-card">
                    <h1>Available Movies</h1>
                    <table class="movies-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Showtimes</th>
                                <!-- <th>Image</th>
                    <th>Banner</th> -->
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movies as $movie): ?>
                                <tr data-movie-id="<?= $movie['movie_id'] ?>">
                                    <td><input type="text" input="title" value="<?= $movie['title'] ?>" readonly required></td>
                                    <td><input type="date" input="startdate" value="<?= $movie['startdate'] ?>" readonly required></td>
                                    <td><input type="date" input="enddate" value="<?= $movie['enddate'] ?>" readonly required></td>
                                    <td><textarea readonly input="showtimes" required><?= $movie['showtimes'] ?></textarea></td>
                                    <td>
                                        <button class="edit-btn">Edit</button>
                                        <button class="save-btn" style="display:none;">Save</button>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="movie_id" value="<?= $movie['movie_id'] ?>">
                                            <button type="submit" name="delete_movie">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>
            </section>
        </div>

        <!-- Users Section -->
        <div id="content-users" class="admin-content-section">
            <section class="signup">
                <div class="signup-card">
                    <h1>Add New User</h1>
                    <form method="POST">
                        <input type="hidden" name="create_new_user" value="true">
                        <input type="hidden" name="usertype" value="user">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                        <button type="submit">Add New User</button>

                    </form>
                </div>
            </section>
            <section class="moviestable">
                <div class="moviestable-card">
                    <h1>Users Table</h1>
                    <table class="movies-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($normal_users as $user): ?>
                                <tr data-user-id="<?= $user['id'] ?>" data-user-status="<?= $user['status'] ?>">
                                    <td><?= $user['id'] ?></td>
                                    <td><?= $user['username'] ?></td>
                                    <td><?= $user['email'] ?></td>
                                    <td><?= $user['status'] ?></td>
                                    <td>
                                        <button class="toggle-status-btn"><?= $user['status'] == 'active' ? 'Deactivate' : 'Activate' ?></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Staff Members Section -->
        <div id="content-staffmembers" class="admin-content-section">
            <section class="signup">
                <div class="signup-card">
                    <h1>Add New Staff Member</h1>
                    <form method="POST">
                        <input type="hidden" name="create_new_user" value="true">
                        <input type="hidden" name="usertype" value="staff">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                        <button type="submit">Add New Staff Member</button>

                    </form>
                </div>
            </section>
            <section class="moviestable">
                <div class="moviestable-card">
                    <h1>Staff Members Table</h1>
                    <table class="movies-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($staff_members as $staff): ?>
                                <tr data-user-id="<?= $staff['id'] ?>" data-user-status="<?= $staff['status'] ?>">
                                    <td><?= $staff['id'] ?></td>
                                    <td><?= $staff['username'] ?></td>
                                    <td><?= $staff['email'] ?></td>
                                    <td><?= $staff['status'] ?></td>
                                    <td>
                                        <button class="toggle-status-btn"><?= $staff['status'] == 'active' ? 'Deactivate' : 'Activate' ?></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
        <div id="content-bookings" class="admin-content-section">
            <section class="moviestable">
                <div class="moviestable-card">
                    <h1>Available Bookings</h1>
                    <table class="movies-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Movie Title</th>
                                <th>Date</th>
                                <th>Username</th>
                                <th>Time</th>
                                <th>Seats</th>
                                <th>Car Park</th>
                                <th>Booking At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr data-booking-id="<?= $booking['id'] ?>">
                                    <td><?= $booking['id'] ?></td>
                                    <td><?= $booking['title'] ?></td>
                                    <td><?= $booking['date'] ?></td>
                                    <td><?= $booking['username'] ?></td>
                                    <td><?= $booking['time'] ?></td>
                                    <td><?= $booking['seats'] ?></td>
                                    <td><?= $booking['car_park'] ?></td>
                                    <td><?= $booking['booking_at'] ?></td>
                                    <td>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <button type="submit" name="delete_booking">Cancel</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

    </div>

    <footer>
        <p>Contact us <a href="mailto:support@mtheater.lk">support@mtheater.lk</a></p>
        <p>&copy; 2024 mtheater.lk</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.admin-actions.card');
            const sections = document.querySelectorAll('.admin-content-section');

            cards.forEach(card => {
                card.addEventListener('click', function() {
                    // Hide all sections
                    sections.forEach(section => {
                        section.classList.remove('active');
                    });
                    // Show the clicked section
                    const targetId = 'content-' + this.id;
                    document.getElementById(targetId).classList.add('active');
                });
            });

            // Edit and Save functionality 
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const row = this.closest('tr');
                    row.querySelectorAll('input, textarea').forEach(input => input.removeAttribute('readonly'));
                    row.querySelector('.save-btn').style.display = 'inline';
                    this.style.display = 'none';
                });
            });

            document.querySelectorAll('.save-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const movieId = row.getAttribute('data-movie-id');
                    const title = row.querySelector('input[input="title"]').value;
                    const startdate = row.querySelector('input[input="startdate"]').value;
                    const enddate = row.querySelector('input[input="enddate"]').value;
                    const showtimes = row.querySelector('textarea[input="showtimes"]').value;

                    const formData = new FormData();
                    formData.append('movie_id', movieId);
                    formData.append('title', title);
                    formData.append('startdate', startdate);
                    formData.append('enddate', enddate);
                    formData.append('showtimes', showtimes);
                    formData.append('update_movie', true);


                    fetch('admin.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {
                            location.reload(); // Reload the page to reflect changes
                        })
                        .catch(error => {


                        });
                });
            });

            document.querySelectorAll('.toggle-status-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const userId = row.getAttribute('data-user-id');
                    const currentStatus = row.getAttribute('data-user-status');
                    const newStatus = currentStatus === 'active' ? 'deactive' : 'active';

                    const formData = new FormData();
                    formData.append('user_id', userId);
                    formData.append('status', currentStatus);
                    formData.append('toggle_user_status', true);

                    fetch('admin.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {
                            location.reload();
                        })
                        .catch(error => {});
                });
            });
        });
    </script>
</body>

</html>