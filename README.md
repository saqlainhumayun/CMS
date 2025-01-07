<?php
// Initialize error messages
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize inputs
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);

    // Validate first and last name
    if (!preg_match("/^[a-zA-Z]+$/", $first_name)) {
        $errors[] = "First name should contain alphabets only.";
    }
    if (!preg_match("/^[a-zA-Z]+$/", $last_name)) {
        $errors[] = "Last name should contain alphabets only.";
    }

    // Validate username
    if (!preg_match("/^[a-zA-Z0-9]+$/", $username)) {
        $errors[] = "Username must contain both alphabets and numbers.";
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Validate password
    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/", $password)) {
        $errors[] = "Password must include at least one uppercase letter, one lowercase letter, one number, and one special character.";
    }

    // If no errors, insert into database
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Connect to the database
        $conn = new mysqli('localhost', 'root', '', 'edusphere_cms');

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name) VALUES (?, ?, ?, ?, ?)");

        if (!$stmt) {
            die("Preparation failed: " . $conn->error);
        }

        $stmt->bind_param("sssss", $username, $email, $password_hash, $first_name, $last_name);

        if ($stmt->execute()) {
            echo "<p style='color: green; text-align: center;'>Signup successful! Redirecting to login...</p>";
            $stmt->close(); // Close the statement after execution
            $conn->close(); // Close the database connection
            header("refresh:3; url=login.php"); // Redirect to login page
            exit;
        } else {
            echo "<p style='color: red; text-align: center;'>Error: " . htmlspecialchars($stmt->error) . "</p>";
            $stmt->close();
            $conn->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - EduSphere CMS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .signup-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #4a90e2;
        }
        form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        form input:focus {
            border-color: #4a90e2;
            outline: none;
        }
        button {
            background-color: #4a90e2;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #357abd;
        }
        .error-messages {
            color: red;
            margin-bottom: 20px;
        }
        .error-messages ul {
            padding: 0;
            list-style-type: none;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>Create an Account</h2>
        <?php if (!empty($errors)) : ?>
            <div class="error-messages">
                <ul>
                    <?php foreach ($errors as $error) : ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="first_name">First Name:</label>
            <input type="text" name="first_name" id="first_name" required>

            <label for="last_name">Last Name:</label>
            <input type="text" name="last_name" id="last_name" required>

            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Signup</button>
        </form>
    </div>
</body>
</html>
