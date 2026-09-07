<?php
session_start();

require_once "config/database.php";

/* Login check */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION['name'] ?? "User";
$user_role = $_SESSION['role'] ?? "user";

$message = "";
$message_type = "";


/* =========================
   ADD STUDENT
========================= */

if (isset($_POST['add_student'])) {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $course = trim($_POST['course']);

    if ($name == "" || $email == "") {

        $message = "Name and Email are required.";
        $message_type = "error";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO students (name, email, phone, course)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssss",
            $name,
            $email,
            $phone,
            $course
        );

        if ($stmt->execute()) {

            $message = "Student added successfully.";
            $message_type = "success";

        } else {

            $message = "Error adding student: " . $conn->error;
            $message_type = "error";
        }

        $stmt->close();
    }
}


/* =========================
   DELETE STUDENT
========================= */

if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    if ($id > 0) {

        $stmt = $conn->prepare(
            "DELETE FROM students WHERE id = ?"
        );

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {

            $message = "Student deleted successfully.";
            $message_type = "success";

        } else {

            $message = "Error deleting student.";
            $message_type = "error";
        }

        $stmt->close();
    }
}


/* =========================
   UPDATE STUDENT
========================= */

if (isset($_POST['update_student'])) {

    $id = intval($_POST['id']);

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $course = trim($_POST['course']);

    if ($name == "" || $email == "") {

        $message = "Name and Email are required.";
        $message_type = "error";

    } else {

        $stmt = $conn->prepare(
            "UPDATE students
             SET name = ?, email = ?, phone = ?, course = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "ssssi",
            $name,
            $email,
            $phone,
            $course,
            $id
        );

        if ($stmt->execute()) {

            $message = "Student updated successfully.";
            $message_type = "success";

        } else {

            $message = "Error updating student.";
            $message_type = "error";
        }

        $stmt->close();
    }
}


/* =========================
   EDIT DATA
========================= */

$edit_student = null;

if (isset($_GET['edit'])) {

    $id = intval($_GET['edit']);

    if ($id > 0) {

        $stmt = $conn->prepare(
            "SELECT * FROM students WHERE id = ?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $edit_student = $result->fetch_assoc();
        }

        $stmt->close();
    }
}


/* =========================
   GET ALL STUDENTS
========================= */

$students = $conn->query(
    "SELECT * FROM students ORDER BY id DESC"
);


/* =========================
   TOTAL STUDENTS
========================= */

$count_result = $conn->query(
    "SELECT COUNT(*) AS total FROM students"
);

$count_row = $count_result->fetch_assoc();

$total_students = $count_row['total'];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Students - LMS</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6f9;
            color: #222;
        }


        /* HEADER */

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
            margin-bottom: 7px;
        }

        .logout {
            color: white;
            text-decoration: none;

            background: #dc2626;

            padding: 8px 14px;

            border-radius: 5px;
        }


        /* CONTAINER */

        .container {
            padding: 30px;
            max-width: 1200px;
            margin: auto;
        }


        /* TOP */

        .top-section {

            display: flex;
            justify-content: space-between;
            align-items: center;

            margin-bottom: 25px;
        }

        .top-section h2 {
            color: #222;
            margin-bottom: 5px;
        }

        .back-btn {

            text-decoration: none;

            background: #555;
            color: white;

            padding: 10px 16px;

            border-radius: 6px;
        }


        /* MESSAGE */

        .message {

            padding: 12px 15px;

            border-radius: 6px;

            margin-bottom: 20px;

            font-weight: bold;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }


        /* CARD */

        .card {

            background: white;

            padding: 25px;

            border-radius: 10px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);

            margin-bottom: 25px;
        }

        .card h3 {

            color: #2563eb;

            margin-bottom: 20px;
        }


        /* FORM */

        .form-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 15px;
        }

        .form-group label {

            display: block;

            margin-bottom: 6px;

            font-weight: bold;
        }

        .form-group input {

            width: 100%;

            padding: 11px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 15px;
        }

        .form-group input:focus {

            outline: none;

            border-color: #2563eb;
        }


        /* BUTTON */

        .btn {

            border: none;

            padding: 11px 18px;

            border-radius: 6px;

            cursor: pointer;

            font-size: 15px;

            margin-top: 18px;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-secondary {

            background: #6b7280;
            color: white;

            text-decoration: none;

            display: inline-block;

            margin-left: 8px;
        }


        /* TABLE */

        .table-container {
            overflow-x: auto;
        }

        table {

            width: 100%;

            border-collapse: collapse;

            margin-top: 10px;
        }

        th {

            background: #2563eb;

            color: white;

            padding: 13px;

            text-align: left;
        }

        td {

            padding: 12px;

            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background: #f8fafc;
        }


        /* ACTION BUTTONS */

        .edit-btn {

            background: #f59e0b;

            color: white;

            padding: 7px 11px;

            border-radius: 5px;

            text-decoration: none;
        }

        .delete-btn {

            background: #dc2626;

            color: white;

            padding: 7px 11px;

            border-radius: 5px;

            text-decoration: none;

            margin-left: 5px;
        }


        /* TOTAL */

        .total-box {

            background: #eef4ff;

            padding: 15px;

            border-radius: 7px;

            margin-bottom: 20px;

            font-size: 18px;
        }

        .total-box strong {
            color: #2563eb;
        }


        /* MOBILE */

        @media (max-width: 700px) {

            .header {

                flex-direction: column;

                gap: 15px;

                text-align: center;
            }

            .user-info {
                text-align: center;
            }

            .top-section {

                flex-direction: column;

                gap: 15px;

                align-items: flex-start;
            }

            .form-grid {

                grid-template-columns: 1fr;
            }

            .container {
                padding: 15px;
            }

        }

    </style>

</head>


<body>


<!-- HEADER -->

<div class="header">

    <h1>
        Learning Management System
    </h1>

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


<!-- MAIN -->

<div class="container">


    <div class="top-section">

        <div>

            <h2>
                Student Management
            </h2>

            <p>
                Add and manage student records
            </p>

        </div>


        <a class="back-btn"
           href="dashboard.php">

            ← Dashboard

        </a>

    </div>


    <!-- MESSAGE -->

    <?php if ($message != ""): ?>

        <div class="message <?php echo $message_type; ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <!-- TOTAL -->

    <div class="total-box">

        Total Students:
        <strong>
            <?php echo $total_students; ?>
        </strong>

    </div>


    <!-- ADD / EDIT FORM -->

    <div class="card">

        <?php if ($edit_student): ?>

            <h3>
                Edit Student
            </h3>

        <?php else: ?>

            <h3>
                Add New Student
            </h3>

        <?php endif; ?>


        <form method="POST">


            <?php if ($edit_student): ?>

                <input
                    type="hidden"
                    name="id"
                    value="<?php echo $edit_student['id']; ?>"
                >

            <?php endif; ?>


            <div class="form-grid">


                <!-- NAME -->

                <div class="form-group">

                    <label>
                        Student Name *
                    </label>

                    <input
                        type="text"
                        name="name"
                        required
                        placeholder="Enter student name"
                        value="<?php
                            echo $edit_student
                                ? htmlspecialchars($edit_student['name'])
                                : '';
                        ?>"
                    >

                </div>


                <!-- EMAIL -->

                <div class="form-group">

                    <label>
                        Email *
                    </label>

                    <input
                        type="email"
                        name="email"
                        required
                        placeholder="Enter email"
                        value="<?php
                            echo $edit_student
                                ? htmlspecialchars($edit_student['email'])
                                : '';
                        ?>"
                    >

                </div>


                <!-- PHONE -->

                <div class="form-group">

                    <label>
                        Phone
                    </label>

                    <input
                        type="text"
                        name="phone"
                        placeholder="Enter phone number"
                        value="<?php
                            echo $edit_student
                                ? htmlspecialchars($edit_student['phone'])
                                : '';
                        ?>"
                    >

                </div>


                <!-- COURSE -->

                <div class="form-group">

                    <label>
                        Course
                    </label>

                    <input
                        type="text"
                        name="course"
                        placeholder="Enter course"
                        value="<?php
                            echo $edit_student
                                ? htmlspecialchars($edit_student['course'])
                                : '';
                        ?>"
                    >

                </div>


            </div>


            <?php if ($edit_student): ?>

                <button
                    type="submit"
                    name="update_student"
                    class="btn btn-primary">

                    Update Student

                </button>

                <a
                    href="students.php"
                    class="btn btn-secondary">

                    Cancel

                </a>

            <?php else: ?>

                <button
                    type="submit"
                    name="add_student"
                    class="btn btn-primary">

                    Add Student

                </button>

            <?php endif; ?>


        </form>

    </div>


    <!-- STUDENT LIST -->

    <div class="card">

        <h3>
            Student Records
        </h3>


        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Name</th>

                        <th>Email</th>

                        <th>Phone</th>

                        <th>Course</th>

                        <th>Created</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>


                <?php if ($students && $students->num_rows > 0): ?>


                    <?php while ($student = $students->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?php echo $student['id']; ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $student['name']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $student['email']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $student['phone']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $student['course']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $student['created_at']
                                );
                                ?>
                            </td>

                            <td>

                                <a
                                    class="edit-btn"
                                    href="students.php?edit=<?php
                                        echo $student['id'];
                                    ?>">

                                    Edit

                                </a>


                                <a
                                    class="delete-btn"
                                    href="students.php?delete=<?php
                                        echo $student['id'];
                                    ?>"
                                    onclick="return confirm(
                                        'Are you sure you want to delete this student?'
                                    );">

                                    Delete

                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>


                <?php else: ?>

                    <tr>

                        <td
                            colspan="7"
                            style="text-align:center; padding:25px;">

                            No students found.

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