<?php
session_start();
include('dbconnection.php'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Assuming password is sent in plaintext


    // Check if email already exists
    $checkEmailQuery = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($checkEmailQuery);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email already registered. Please use another email.";
        header('Location: signup.php');
        exit();
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user into database
        $insertQuery = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param('sss', $username, $email, $hashed_password);

        if ($stmt->execute()) {
            // Registration successful
            $_SESSION['message'] = 'message=Registration successful.&status=success';
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['usertype'] = 'user';
            header('Location: ' . (isset($_GET['movie']) ? 'booknow.php' : 'dashboard.php') . (!empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''));

            exit();
        } else {
            // Error inserting user into database
            $_SESSION['error'] = "Something went wrong.";
            header('Location: signup.php' . (!empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''));
            exit();
        }
    }
} else {
    // Handle cases where the request method is not POST (optional)
    $_SESSION['error'] = "Invalid request method.";
    header('Location: signup.php' . (!empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''));
    exit();
}
?>
