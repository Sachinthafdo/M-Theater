<?php
session_start();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-Theater - About</title>
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
        <section class="features">
            <h2>Available Features</h2>
            <ul>
                <li>Gourmet Cafeteria with a Wide Selection of Snacks and Beverages</li>
                <li>Immersive 3D Viewing Experience</li>
                <li>State-of-the-Art High Quality Surround Sound Systems</li>
                <li>Impressive Large Movie Wall for Superior Viewing</li>
                <li>Fully Air-Conditioned, Climate-Controlled Environment</li>
                <li>Friendly and Attentive Staff Dedicated to Customer Satisfaction</li>
            </ul>
        </section>
        <section class="achievements">
            <h2>Our Achievements</h2>
            <ul>
                <li>Recognized as the best cinema in the region for three consecutive years and have hosted numerous film festivals and premieres.</li>
                <li>Recognized for having the best sound and picture quality by the National Theater Association.</li>
                <li>Received the Customer Service Excellence Award for providing outstanding service to moviegoers.</li>
                <li>Implemented eco-friendly practices and achieved a Green Cinema Certification.</li>
                <li>Awarded for diverse programming, showcasing a wide range of international and independent films.</li>
            </ul>
        </section>
        <section class="comparison">
            <h2>Why Choose Us Over Other Movie Theaters</h2>
            <ul>
                <li>At M-Theater, we provide a premium experience with state-of-the-art technology, superior comfort, and exceptional service.</li>
                <li>We offer a diverse selection of films, including exclusive premieres and independent movies that you won't find anywhere else.</li>
                <li>Our luxurious seating and spacious auditoriums ensure that every guest enjoys a comfortable and immersive viewing experience.</li>
            </ul>
        </section>
        <section class="why-special">
            <h2>Why Watching Movies at M-Theater is Special</h2>
            <ul>
                <li>Experience movies like never before with our immersive screens, surround sound, and a vibrant atmosphere that makes every visit unforgettable.</li>
                <li>Our expansive screens provide a level of detail and clarity that small devices like TVs, mobiles, and PC screens can't match.</li>
                <li>Enjoy powerful, high-quality sound that brings every scene to life, far surpassing the audio capabilities of personal devices.</li>
                <li>Escape from everyday distractions and fully immerse yourself in the cinematic experience without interruptions from notifications or smaller screens.</li>
            </ul>
        </section>
    </div>
    <footer>
        <p>Contact us <a href="mailto:support@mtheater.lk">support@mtheater.lk</a></p>
        <p>&copy; 2024 mtheater.lk</p>
    </footer>
</body>

</html>