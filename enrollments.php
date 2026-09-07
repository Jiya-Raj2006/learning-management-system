<?php
session_start();

require_once "config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION['name'] ?? "User";

$message = "";
$message_type = "";

/* =========================================
   DELETE ENROLLMENT
========================================= */

if (isset($_GET['delete'])) {

    $enrollment_id = intval($_GET['delete']);

    if ($enrollment_id > 0) {

        $stmt = $conn->prepare(
            "DELETE FROM enrollments WHERE id = ?"
        );

        $stmt->bind_param("i", $enrollment_id);

        if ($stmt->execute()) {
            $message = "Enrollment deleted successfully.";
            $message_type = "success";
        } else {
            $message = "Failed to delete enrollment.";
            $message_type = "error";
        }

        $stmt->close();
    }
}


/* =========================================
   ADD ENROLLMENT
========================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = intval($_POST['student_id'] ?? 0);
    $course_id  = intval($_POST['course_id'] ?? 0);

    if ($student_id <= 0 || $course_id <= 0) {

        $message = "Please select both student and course.";
        $message_type = "error";

    } else {

        /* Check duplicate enrollment */

        $check = $conn->prepare(
            "SELECT id
             FROM enrollments
             WHERE student_id = ?
             AND course_id = ?"
        );

        $check->bind_param(
            "ii",
            $student_id,
            $course_id
        );

        $check->execute();

        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {

            $message =
                "This student is already enrolled in this course.";

            $message_type = "error";

        } else {

            /* Insert enrollment */

            $stmt = $conn->prepare(
                "INSERT INTO enrollments
                (student_id, course_id)
                VALUES (?, ?)"
            );

            $stmt->bind_param(
                "ii",
                $student_id,
                $course_id
            );

            if ($stmt->execute()) {

                $message =
                    "Student enrolled successfully.";

                $message_type = "success";

            } else {

                $message =
                    "Failed to enroll student.";

                $message_type = "error";
            }

            $stmt->close();
        }

        $check->close();
    }
}


/* =========================================
   GET STUDENTS
========================================= */

$students = [];

$student_result = $conn->query(
    "SELECT id, name
     FROM students
     ORDER BY name ASC"
);

if ($student_result) {

    while ($row = $student_result->fetch_assoc()) {
        $students[] = $row;
    }
}


/* =========================================
   GET COURSES
========================================= */

$courses = [];

$course_result = $conn->query(
    "SELECT id, title
     FROM courses
     ORDER BY title ASC"
);

if ($course_result) {

    while ($row = $course_result->fetch_assoc()) {
        $courses[] = $row;
    }
}


/* =========================================
   GET ENROLLMENT RECORDS
========================================= */

$enrollments = [];

$enrollment_result = $conn->query(
    "SELECT
        e.id,
        s.name AS student_name,
        c.title AS course_title,
        e.enrolled_at

     FROM enrollments e

     INNER JOIN students s
        ON e.student_id = s.id

     INNER JOIN courses c
        ON e.course_id = c.id

     ORDER BY e.id DESC"
);

if ($enrollment_result) {

    while ($row = $enrollment_result->fetch_assoc()) {
        $enrollments[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Enrollments - LMS</title>

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

        .container {
            width: 92%;
            max-width: 1500px;
            margin: 0 auto;
            padding: 35px 0;
        }

        .back {
            display: inline-block;
            margin-bottom: 25px;
            color: #2563eb;
            text-decoration: none;
            font-size: 20px;
            font-weight: bold;
        }

        .back:hover {
            text-decoration: underline;
        }

        .card {
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 35px;
        }

        h1 {
            color: #2563eb;
            margin-bottom: 30px;
            font-size: 36px;
        }

        h2 {
            color: #2563eb;
            margin-bottom: 25px;
            font-size: 32px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 35px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        select {
            width: 100%;
            padding: 16px;
            font-size: 18px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: white;
        }

        select:focus {
            outline: none;
            border-color: #2563eb;
        }

        button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 15px 25px;
            font-size: 20px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        .message {
            padding: 18px 22px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 18px;
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

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #2563eb;
            color: white;
            padding: 18px;
            text-align: left;
            font-size: 18px;
        }

        td {
            padding: 18px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 17px;
        }

        tr:hover td {
            background: #f8fafc;
        }

        .delete {
            display: inline-block;
            background: #dc2626;
            color: white;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 7px;
            font-weight: bold;
        }

        .delete:hover {
            background: #b91c1c;
        }

        .no-records {
            padding: 20px 0;
            font-size: 18px;
            color: #555;
        }

        @media (max-width: 800px) {

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .container {
                width: 94%;
            }

            .card {
                padding: 25px;
            }

            h1 {
                font-size: 30px;
            }

            h2 {
                font-size: 27px;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <a
        href="dashboard.php"
        class="back"
    >
        ← Back to Dashboard
    </a>


    <!-- =====================================
         ENROLL STUDENT
    ====================================== -->

    <div class="card">

        <h1>
            Enroll Student in Course
        </h1>


        <?php if ($message !== ""): ?>

            <div class="message <?php echo $message_type; ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            action="enrollments.php"
        >

            <div class="form-row">

                <!-- STUDENT -->

                <div class="form-group">

                    <label for="student_id">
                        Select Student *
                    </label>

                    <select
                        name="student_id"
                        id="student_id"
                        required
                    >

                        <option value="">
                            -- Select Student --
                        </option>

                        <?php foreach ($students as $student): ?>

                            <option
                                value="<?php echo $student['id']; ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $student['name']
                                );
                                ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- COURSE -->

                <div class="form-group">

                    <label for="course_id">
                        Select Course *
                    </label>

                    <select
                        name="course_id"
                        id="course_id"
                        required
                    >

                        <option value="">
                            -- Select Course --
                        </option>

                        <?php foreach ($courses as $course): ?>

                            <option
                                value="<?php echo $course['id']; ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $course['title']
                                );
                                ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

            </div>


            <button type="submit">
                Enroll Student
            </button>

        </form>

    </div>


    <!-- =====================================
         ENROLLMENT RECORDS
    ====================================== -->

    <div class="card">

        <h2>
            Enrollment Records
        </h2>


        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Student</th>

                        <th>Course</th>

                        <th>Enrolled At</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>

                <?php if (count($enrollments) > 0): ?>

                    <?php foreach ($enrollments as $enrollment): ?>

                        <tr>

                            <td>
                                <?php
                                echo $enrollment['id'];
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $enrollment['student_name']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $enrollment['course_title']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $enrollment['enrolled_at']
                                );
                                ?>
                            </td>

                            <td>

                                <a
                                    class="delete"
                                    href="enrollments.php?delete=<?php echo $enrollment['id']; ?>"
                                    onclick="return confirm('Are you sure you want to delete this enrollment?');"
                                >
                                    Delete
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="5">

                            <div class="no-records">
                                No enrollments found.
                            </div>

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