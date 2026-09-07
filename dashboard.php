<?php
session_start();

require_once "config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION['name'] ?? "User";
$user_role = $_SESSION['role'] ?? "user";

/* Count Students */
$student_result = $conn->query("SELECT COUNT(*) AS total FROM students");
$total_students = 0;

if ($student_result) {
    $student_row = $student_result->fetch_assoc();
    $total_students = $student_row['total'];
}

/* Count Courses */
$course_result = $conn->query("SELECT COUNT(*) AS total FROM courses");
$total_courses = 0;

if ($course_result) {
    $course_row = $course_result->fetch_assoc();
    $total_courses = $course_row['total'];
}

/* Count Users */
$user_result = $conn->query("SELECT COUNT(*) AS total FROM users");
$total_users = 0;

if ($user_result) {
    $user_row = $user_result->fetch_assoc();
    $total_users = $user_row['total'];
}

/* Count Enrollments */
$enrollment_result = $conn->query("SELECT COUNT(*) AS total FROM enrollments");
$total_enrollments = 0;

if ($enrollment_result) {
    $enrollment_row = $enrollment_result->fetch_assoc();
    $total_enrollments = $enrollment_row['total'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LMS Dashboard</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6f9;
        }

        .header {
            background: #2563eb;
            color: white;
            padding: 18px 30px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
        }

        .user-info {
            text-align: right;
        }

        .user-info small {
            display: block;
            margin-bottom: 8px;
        }

        .logout {
            color: white;
            text-decoration: none;
            background: #dc2626;

            padding: 8px 14px;
            border-radius: 5px;
        }

        .container {
            padding: 30px;
        }

        .welcome {
            margin-bottom: 25px;
        }

        .welcome h2 {
            margin-bottom: 8px;
            color: #222;
        }

        .cards {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 20px;
        }

        .card {
            background: white;
            padding: 25px;

            border-radius: 10px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);
        }

        .card h3 {
            color: #555;
            margin-bottom: 15px;
        }

        .number {
            font-size: 32px;
            font-weight: bold;
            color: #2563eb;
        }

        .menu {
            margin-top: 30px;

            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 20px;
        }

        .menu a {
            background: white;

            padding: 20px;

            text-decoration: none;

            color: #222;

            border-radius: 10px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);

            transition: 0.2s;
        }

        .menu a:hover {
            background: #eef4ff;
            transform: translateY(-2px);
        }

        .menu h3 {
            color: #2563eb;
            margin-bottom: 8px;
        }

        @media (max-width: 1000px) {

            .cards,
            .menu {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

        }

        @media (max-width: 600px) {

            .cards,
            .menu {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .user-info {
                text-align: center;
            }

        }

    </style>

</head>

<body>

    <div class="header">

        <h1>Learning Management System</h1>

        <div class="user-info">

            <small>
                Logged in as:
                <?php echo htmlspecialchars($user_name); ?>
            </small>

            <a class="logout" href="logout.php">
                Logout
            </a>

        </div>

    </div>


    <div class="container">

        <div class="welcome">

            <h2>Dashboard</h2>

            <p>
                Welcome,
                <strong>
                    <?php echo htmlspecialchars($user_name); ?>
                </strong>
            </p>

        </div>


        <!-- DASHBOARD COUNTS -->

        <div class="cards">

            <div class="card">

                <h3>Total Students</h3>

                <div class="number">
                    <?php echo $total_students; ?>
                </div>

            </div>


            <div class="card">

                <h3>Total Courses</h3>

                <div class="number">
                    <?php echo $total_courses; ?>
                </div>

            </div>


            <div class="card">

                <h3>Total Users</h3>

                <div class="number">
                    <?php echo $total_users; ?>
                </div>

            </div>


            <div class="card">

                <h3>Total Enrollments</h3>

                <div class="number">
                    <?php echo $total_enrollments; ?>
                </div>

            </div>

        </div>


        <!-- MENU -->

        <div class="menu">

            <a href="students.php">

                <h3>Students</h3>

                <p>
                    Manage student records
                </p>

            </a>


            <a href="courses.php">

                <h3>Courses</h3>

                <p>
                    Manage courses and subjects
                </p>

            </a>


            <a href="enrollments.php">

                <h3>Enrollments</h3>

                <p>
                    Enroll students in courses
                </p>

            </a>


            <a href="users.php">

                <h3>Users</h3>

                <p>
                    Manage system users
                </p>

            </a>

        </div>

    </div>

</body>

</html>