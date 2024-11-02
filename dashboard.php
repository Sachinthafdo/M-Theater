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

function displayMoviesInCarousel($movies)
{
    echo '<div class="carousel-container">';
    echo '<div class="carousel">';
    foreach ($movies as $movie) {
        echo '<div class="carousel-item card">';
        echo '<img src="assets/movie_images/' . $movie['image'] . '" alt="' . $movie['title'] . '">';
        echo '<h2>' . $movie['title'] . '</h2>';
        echo '<a href="booknow.php?movie=' . urlencode($movie['movie_id']) . '"><button type="button" class="btn-book-now">Book Now</button></a>';
        echo '</div>';
    }
    echo '</div>';
    echo '<button class="carousel-button left-button" onclick="moveCarousel(-1)">
    <svg width="50" height="50" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg" style="background: none;">
  <circle cx="25" cy="25" r="24" stroke="black" stroke-width="1" fill="white" />
  <polygon points="28,18 18,25 28,32" fill="black" />
</svg>
</button>';
    echo '<button class="carousel-button right-button" onclick="moveCarousel(1)">
    <svg width="50" height="50" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg" style="background: none;">
  <circle cx="25" cy="25" r="24" stroke="black" stroke-width="1" fill="white" />
   <polygon points="22,18 32,25 22,32" fill="black" />
</svg>
</button>';
    echo '</div>';
}

$nowShowing = [];
$upcoming = [];

foreach ($movies as $movie) {
    $today = date('Y-m-d');
    if ($today >= $movie['startdate'] && $today <= $movie['enddate']) {
        $nowShowing[] = $movie;
    } elseif ($today < $movie['startdate']) {
        $upcoming[] = $movie;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-Theater - Dashboard</title>
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
            filter: blur(2px);
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
            <h1>Welcome to M-Theater</h1>
        </section>

        <section class="movies-carousel" id="nowshowing">
            <center>
                <h1>Now Showing</h1>
            </center>
            <?php
            if (!empty($nowShowing)) {
                displayMoviesInCarousel($nowShowing);
            } else {
                echo '<p>No movies currently showing.</p>';
            }
            ?>
        </section>

        <section class="movies-carousel" id="upcoming">
            <center>
                <h1>Upcoming</h1>
            </center>
            <?php
            if (!empty($upcoming)) {
                displayMoviesInCarousel($upcoming);
            } else {
                echo '<p>No upcoming movies.</p>';
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

        let currentSlide = 0;

        function moveCarousel(direction) {
            const carousel = document.querySelector('.carousel');
            const totalItems = carousel.children.length;
            const itemsVisible = 4;
            const itemWidth = carousel.children[0].clientWidth;
            const maxSlide = totalItems - itemsVisible;

            currentSlide += direction;

            if (currentSlide < 0) {
                currentSlide = 0;
            } else if (currentSlide > maxSlide) {
                currentSlide = maxSlide;
            }

            carousel.style.transform = 'translateX(' + (-currentSlide * itemWidth) + 'px)';

            document.querySelector('.left-button').style.display = currentSlide === 0 ? 'none' : 'block';
            document.querySelector('.right-button').style.display = currentSlide === maxSlide ? 'none' : 'block';
        }

        document.addEventListener('DOMContentLoaded', function() {
            moveCarousel(0); // Initialize carousel
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