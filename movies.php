<?php
session_start();


include('dbconnection.php');
$checkmovieslist = "SELECT title, movie_id, title, image, startdate, enddate FROM movies";
$stmt = $conn->prepare($checkmovieslist);
$stmt->execute();
$result = $stmt->get_result();

$movies = [];
while ($row = $result->fetch_assoc()) {
    $movies[] = $row;
}

$num_movies = count($movies);

function displayMoviesInGroups($movies)
{
    $groups = array_chunk($movies, 4);
    foreach ($groups as $group) {
        echo '<div class="movies">';
        displayMovies($group);
        echo '</div>';
    }
}

function displayMovies($movies)
{
    foreach ($movies as $movie) {
        echo '<div class="card" onclick="location.href=\'booknow.php?movie=' . urlencode($movie['movie_id']) . '\'">';
        echo '<img src="assets/movie_images/' . $movie['image'] . '" alt="' . $movie['title'] . '">';
        echo '<h2>' . $movie['title'] . '</h2>';
        echo '</div>';
    }
}

$availablemovies = [];

foreach ($movies as $movie) {
    $today = date('Y-m-d');
    if (($today >= $movie['startdate'] && $today <= $movie['enddate']) || ($today < $movie['startdate'])) {
        $availablemovies[] = $movie;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-Theater - Movies</title>
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
                    echo '<li><a href="profile.php"><button class="btn-primary-nav">Profile</button></a></li>';
                }
                ?>
            </ul>
        </nav>
    </header>



    <div class="main-content">
        <section class="parallax">
            <h1>Welcome to M-Theater</h1>
        </section>

        <section class="movies-carousel" id="nowshowing">
            <center>
                <h1>Available Movies</h1>
            </center>
            <?php
            if (!empty($availablemovies)) {
                displayMoviesInGroups($availablemovies);
            } else {
                echo '<p>No movies</p>';
            }
            ?>
        </section>

    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const cards = document.querySelectorAll('.card h2');

            cards.forEach(card => {
                const textLength = card.textContent.length;
                let fontSize = 18;
                card.style.fontSize = fontSize + 'px';

                while (fontSize * textLength >= parseInt(card.style.width.replace('px', ''))) {
                    fontSize -= 2;
                    card.style.fontSize = fontSize + 'px';
                }

            });
        });
    </script>
    <footer>
        <p>Contact us <a href="mailto:support@mtheater.lk">support@mtheater.lk</a></p>
        <p>&copy; 2024 mtheater.lk</p>
    </footer>
</body>

</html>

<?php
$stmt->close();
?>