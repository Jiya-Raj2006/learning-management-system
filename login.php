<?php
session_start();

require_once "config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Find user by email only
    $sql = "SELECT * FROM users WHERE email = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        // Verify hashed password
        if (password_verify($password, $user["password"])) {

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["name"] = $user["name"];
            $_SESSION["role"] = $user["role"];

            // Login successful
            header("Location: dashboard.php");
            exit();

        } else {

            $message = "Invalid email or password!";

        }

    } else {

        $message = "Invalid email or password!";

    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>LMS Login</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
        }

        .header {
            background-color: #2864e8;
            color: white;
            padding: 30px;
            text-align: center;
        }

        .container {
            width: 400px;
            margin: 50px auto;
        }

        .card {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        input {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            background-color: #2864e8;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #174bb5;
        }

        .error {
            color: red;
            background-color: #f8d7da;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

    </style>

</head>

<body>

<div class="header">

    <h1>Learning Management System</h1>

    <p>Login</p>

</div>


<div class="container">

    <div class="card">

        <h2>Login</h2>

        <?php if ($message != ""): ?>

            <div class="error">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <label>Email</label>

            <input
                type="email"
                name="email"
                placeholder="Enter email"
                required
            >


            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Enter password"
                required
            >


            <button type="submit">
                Login
            </button>

        </form>

    </div>

</div>

</body>

</html>