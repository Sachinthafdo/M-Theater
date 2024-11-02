
# M-Theater Movie Booking System

This project is a PHP-based movie booking web application where users can view movies, book tickets, and manage profiles. 
It connects to a MySQL database to store information about users, movies, and bookings.

## Features

- **User Authentication**: Users can sign in to their accounts or create new ones.
- **Movie Display**: Movies are shown in a carousel format on the dashboard.
- **Booking Management**: Users can book tickets for available movies.
- **Admin Dashboard**: Admins can manage movies, users, and bookings.

## Technologies Used

- **Front-end**: HTML, CSS
- **Back-end**: PHP
- **Database**: MySQL (phpMyAdmin used for database management)

## Project Setup

1. Clone the repository or download the project files.
2. Import the `mtheater` database (or create it automatically if it doesn't exist) in your phpMyAdmin.
3. Configure the database credentials in `dbconnection.php` to match your environment (default: `localhost`, `root`, and no password).
4. Start your local server (using XAMPP, WAMP, or a similar tool) to run the application.

## File Structure

- **about.php**: Contains information about the application.
- **admin.php**: Dashboard for managing movies, users, and bookings.
- **booknow.php**: Handles ticket bookings for selected movies.
- **confirmation.php**: Confirms bookings and provides details.
- **dashboard.php**: Displays the main user interface with a movie carousel.
- **dbconnection.php**: Manages database connections.
- **signin.php**: User login interface.
- **profile.php**: Displays user profile information.
- **logout.php**: Handles user logouts.

## Usage

1. **Sign In**: Access the `signin.php` page and log in with your account.
2. **View Movies**: The dashboard displays movies in a carousel format.
3. **Book Tickets**: Select a movie and proceed to `booknow.php` to book tickets.
4. **Admin Access**: If you have admin privileges, access `admin.php` to manage system data.

## Purpose

This project was created to gain fundamental knowledge in web development, particularly in:

- **HTML, CSS, and PHP**
- **phpMyAdmin for database management**

## Contact

Developed by Sachintha Rashan Fernando.
