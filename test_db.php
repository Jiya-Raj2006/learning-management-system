<?php

require_once "config/database.php";

$email = "admin@gmail.com";
$password = "admin123";

$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<h2 style='color:red;'>USER NOT FOUND</h2>";
    exit;
}

$user = $result->fetch_assoc();

echo "<h2>User Found</h2>";
echo "Email: " . htmlspecialchars($user["email"]) . "<br>";
echo "Name: " . htmlspecialchars($user["name"]) . "<br>";
echo "Role: " . htmlspecialchars($user["role"]) . "<br>";
echo "Stored Hash: " . htmlspecialchars($user["password"]) . "<br><br>";

if (password_verify($password, $user["password"])) {
    echo "<h1 style='color:green;'>PASSWORD CORRECT</h1>";
} else {
    echo "<h1 style='color:red;'>PASSWORD INCORRECT</h1>";
}

?>