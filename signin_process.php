<?php
session_start();
include('dbconnection.php'); // Assuming this file establishes $conn

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = $_POST['usernameoremail'];
    $password = $_POST['password'];

    // Query to check if the username or email exists in the database
    $checkUserQuery = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($checkUserQuery);
    $stmt->bind_param('ss', $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // User found, verify the password
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Password is correct, start a session
            if($user['status'] == 'active'){
                
            $_SESSION['message'] = 'message=Login successful.&status=success';
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['usertype'] = $user['usertype'];

            $url = 'dashboard.php';

if (isset($_SESSION['usertype'])) {
    if ($_SESSION['usertype'] == 'admin') {
        $url = 'admin.php';
    } elseif ($_SESSION['usertype'] == 'staff') {
        $url = 'staff.php';
    }
} 

if (isset($_GET['movie'])) {
    $url = 'booknow.php';
}

$queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : '';

header('Location: ' . $url . $queryString);
//header('Location: ' . (isset($_GET['movie']) ? 'booknow.php' : 'dashboard.php') . (!empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''));
 // Redirect to dashboard or any other authenticated page
            exit();
        }else{
            echo "<h2> Account Suspended by Owner!! </h2>";
        }

        } else {
            $_SESSION['error'] = "Invalid password."; // Store error message in session
            header('Location: signin.php' . (!empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''));
            exit();
        }
    } else {
        $_SESSION['error'] = "Invalid username or email."; // Store error message in session
        header('Location: signin.php' . (!empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''));
        exit();
    }
}
?>
