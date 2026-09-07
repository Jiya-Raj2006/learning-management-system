<?php
session_start();

require_once "config/database.php";

/* =========================
   ADMIN ACCESS CHECK
========================= */

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    die("Access denied. Only admin can manage users.");
}

/* =========================
   CREATE NEW USER
========================= */

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['create_user'])) {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';

    if ($name === '' || $email === '' || $password === '') {

        $message = "Please fill all required fields.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } else {

        /* Check duplicate email */
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {

            $message = "A user with this email already exists.";
            $message_type = "error";

        } else {

            /* Hash password */
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssss",
                $name,
                $email,
                $hashed_password,
                $role
            );

            if ($stmt->execute()) {

                $message = "User created successfully!";
                $message_type = "success";

            } else {

                $message = "Failed to create user.";
                $message_type = "error";
            }

            $stmt->close();
        }

        $check->close();
    }
}


/* =========================
   DELETE USER
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_user'])) {

    $delete_id = intval($_POST['delete_id'] ?? 0);
    $admin_password = $_POST['admin_password'] ?? '';

    if ($delete_id <= 0 || $admin_password === '') {

        $message = "Please enter the admin password.";
        $message_type = "error";

    } elseif ($delete_id == $_SESSION['user_id']) {

        $message = "The admin account cannot be deleted.";
        $message_type = "error";

    } else {

        /*
         * Get the current admin account.
         * Password is stored using password_hash().
         */
        $admin_stmt = $conn->prepare(
            "SELECT password FROM users WHERE id = ? AND role = 'admin'"
        );

        $admin_stmt->bind_param("i", $_SESSION['user_id']);
        $admin_stmt->execute();

        $admin_result = $admin_stmt->get_result();

        if ($admin_result->num_rows === 0) {

            $message = "Admin account could not be verified.";
            $message_type = "error";

        } else {

            $admin = $admin_result->fetch_assoc();

            /* Verify admin password */

            if (!password_verify($admin_password, $admin['password'])) {

                $message = "Incorrect admin password.";
                $message_type = "error";

            } else {

                /*
                 * Make sure the selected account is NOT an admin.
                 */
                $user_stmt = $conn->prepare(
                    "SELECT id, role FROM users WHERE id = ?"
                );

                $user_stmt->bind_param("i", $delete_id);
                $user_stmt->execute();

                $user_result = $user_stmt->get_result();

                if ($user_result->num_rows === 0) {

                    $message = "User not found.";
                    $message_type = "error";

                } else {

                    $user = $user_result->fetch_assoc();

                    if ($user['role'] === 'admin') {

                        $message = "Admin accounts are protected and cannot be deleted.";
                        $message_type = "error";

                    } else {

                        /* Delete normal user */

                        $delete_stmt = $conn->prepare(
                            "DELETE FROM users WHERE id = ? AND role != 'admin'"
                        );

                        $delete_stmt->bind_param("i", $delete_id);

                        if ($delete_stmt->execute()) {

                            $message = "User deleted successfully.";
                            $message_type = "success";

                        } else {

                            $message = "Failed to delete user.";
                            $message_type = "error";
                        }

                        $delete_stmt->close();
                    }
                }

                $user_stmt->close();
            }
        }

        $admin_stmt->close();
    }
}


/* =========================
   GET ALL USERS
========================= */

$users = $conn->query(
    "SELECT id, name, email, role, created_at
     FROM users
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Users Management</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f9;
            color: #111;
        }

        .header {
            background: #2864e8;
            color: white;
            padding: 18px 40px;
            text-align: center;
            font-size: 22px;
            font-weight: bold;
        }

        .container {
            width: 90%;
            max-width: 1450px;
            margin: 38px auto;
        }

        .back-btn {
            display: inline-block;
            background: #555;
            color: white;
            text-decoration: none;
            padding: 14px 20px;
            border-radius: 6px;
            font-size: 18px;
            margin-bottom: 28px;
        }

        .back-btn:hover {
            background: #333;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 32px;
            margin-bottom: 32px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .card h2 {
            margin-top: 0;
            margin-bottom: 28px;
            font-size: 30px;
        }

        .add-title {
            font-size: 30px;
            margin-bottom: 25px;
        }

        .add-title span {
            color: #7654d6;
            font-size: 40px;
            vertical-align: middle;
            margin-right: 8px;
        }

        label {
            display: block;
            font-size: 20px;
            margin-bottom: 9px;
        }

        input,
        select {
            width: 100%;
            padding: 14px;
            border: 1px solid #d1d1d1;
            border-radius: 7px;
            font-size: 18px;
            margin-bottom: 20px;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #2864e8;
        }

        .create-btn {
            background: #2864e8;
            color: white;
            border: none;
            padding: 14px 27px;
            border-radius: 6px;
            font-size: 18px;
            cursor: pointer;
        }

        .create-btn:hover {
            background: #1751d0;
        }

        .message {
            padding: 15px;
            border-radius: 7px;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        th {
            background: #2864e8;
            color: white;
            padding: 18px 15px;
            text-align: left;
            font-size: 18px;
        }

        td {
            padding: 18px 15px;
            border-bottom: 1px solid #ddd;
            font-size: 18px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .delete-area {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .admin-password {
            width: 235px;
            margin: 0;
            padding: 11px 12px;
            font-size: 16px;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 6px;
            font-size: 17px;
            cursor: pointer;
        }

        .delete-btn:hover {
            background: #c82333;
        }

        .protected {
            font-weight: bold;
            color: #555;
        }

        .role-admin {
            font-weight: bold;
            color: #2864e8;
        }

        @media (max-width: 800px) {

            .container {
                width: 95%;
            }

            .card {
                padding: 20px;
            }

            .delete-area {
                flex-direction: column;
                align-items: flex-start;
            }

            .admin-password {
                width: 100%;
            }

        }

    </style>

</head>

<body>

    <div class="header">
        Users Management
    </div>

    <div class="container">

        <a href="dashboard.php" class="back-btn">
            ← Back to Dashboard
        </a>


        <!-- =========================
             ADD NEW USER
        ========================== -->

        <div class="card">

            <h2 class="add-title">
                <span>+</span> Add New User
            </h2>

            <?php if ($message !== ""): ?>

                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php endif; ?>


            <form method="POST">

                <label>Name</label>

                <input
                    type="text"
                    name="name"
                    placeholder="Enter full name"
                    required
                >


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


                <label>Role</label>

                <select name="role">

                    <option value="user">User</option>

                    <option value="admin">Admin</option>

                </select>


                <button
                    type="submit"
                    name="create_user"
                    class="create-btn"
                >
                    Create User
                </button>

            </form>

        </div>


        <!-- =========================
             ALL USERS
        ========================== -->

        <div class="card">

            <h2>All Users</h2>

            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Name</th>

                            <th>Email</th>

                            <th>Role</th>

                            <th>Created At</th>

                            <th>Action</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if ($users && $users->num_rows > 0): ?>

                        <?php while ($user = $users->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?php echo htmlspecialchars($user['id']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($user['name']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </td>

                                <td>

                                    <?php if ($user['role'] === 'admin'): ?>

                                        <span class="role-admin">
                                            admin
                                        </span>

                                    <?php else: ?>

                                        user

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <?php echo htmlspecialchars($user['created_at']); ?>
                                </td>

                                <td>

                                    <?php if ($user['role'] === 'admin'): ?>

                                        <span class="protected">
                                            Protected
                                        </span>

                                    <?php else: ?>

                                        <form
                                            method="POST"
                                            class="delete-area"
                                            onsubmit="return confirm('Are you sure you want to delete this user?');"
                                        >

                                            <input
                                                type="password"
                                                name="admin_password"
                                                class="admin-password"
                                                placeholder="Admin password"
                                                required
                                            >

                                            <input
                                                type="hidden"
                                                name="delete_id"
                                                value="<?php echo $user['id']; ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="delete_user"
                                                class="delete-btn"
                                            >
                                                Delete
                                            </button>

                                        </form>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="6" style="text-align:center;">
                                No users found.
                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</body>

</html>