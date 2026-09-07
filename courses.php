<?php
session_start();

require_once "config/database.php";

/* =========================
   LOGIN CHECK
========================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$error = "";

/* =========================
   ADD COURSE
========================= */

if (isset($_POST['add_course'])) {

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if ($title == "") {

        $error = "Course Title is required.";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO courses (title, description)
             VALUES (?, ?)"
        );

        $stmt->bind_param(
            "ss",
            $title,
            $description
        );

        if ($stmt->execute()) {

            $message = "Course added successfully.";

        } else {

            $error = "Unable to add course.";

        }

        $stmt->close();
    }
}


/* =========================
   DELETE COURSE
========================= */

if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $stmt = $conn->prepare(
        "DELETE FROM courses WHERE id = ?"
    );

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {

        $message = "Course deleted successfully.";

    } else {

        $error = "Unable to delete course.";

    }

    $stmt->close();
}


/* =========================
   UPDATE COURSE
========================= */

if (isset($_POST['update_course'])) {

    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if ($title == "") {

        $error = "Course Title is required.";

    } else {

        $stmt = $conn->prepare(
            "UPDATE courses
             SET title = ?, description = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "ssi",
            $title,
            $description,
            $id
        );

        if ($stmt->execute()) {

            $message = "Course updated successfully.";

        } else {

            $error = "Unable to update course.";

        }

        $stmt->close();
    }
}


/* =========================
   EDIT COURSE
========================= */

$edit_course = null;

if (isset($_GET['edit'])) {

    $id = intval($_GET['edit']);

    $stmt = $conn->prepare(
        "SELECT * FROM courses WHERE id = ?"
    );

    $stmt->bind_param("i", $id);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $edit_course = $result->fetch_assoc();

    }

    $stmt->close();
}


/* =========================
   TOTAL COURSES
========================= */

$count_result = $conn->query(
    "SELECT COUNT(*) AS total FROM courses"
);

$count_row = $count_result->fetch_assoc();

$total_courses = $count_row['total'];


/* =========================
   COURSE LIST
========================= */

$courses_result = $conn->query(
    "SELECT * FROM courses ORDER BY id DESC"
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Courses - LMS</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #222;
        }

        .header {
            background: #2864e6;
            color: white;
            padding: 25px;
            text-align: center;
        }

        .header h1 {
            margin: 0 0 10px 0;
        }

        .container {
            width: 90%;
            max-width: 1100px;
            margin: 30px auto;
        }

        .back-button {
            display: inline-block;
            background: #555;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .count-box {
            background: #eef4ff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 22px;
            font-weight: bold;
        }

        .count-number {
            color: #2864e6;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .card h2 {
            color: #2864e6;
            margin-top: 0;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input,
        textarea {
            width: 100%;
            padding: 13px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        button {
            background: #2864e6;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }

        button:hover {
            background: #174dbb;
        }

        .success {
            background: #d9f7df;
            color: #176b2c;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .error {
            background: #ffe0e0;
            color: #a00000;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background: #2864e6;
            color: white;
            padding: 14px;
            text-align: left;
        }

        td {
            padding: 13px;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background: #f5f8ff;
        }

        .edit-btn {
            background: #f0a000;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
        }

        .delete-btn {
            background: #e22;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
            margin-left: 5px;
        }

        .cancel-btn {
            display: inline-block;
            background: #666;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 6px;
            margin-left: 8px;
        }

        @media (max-width: 700px) {

            .container {
                width: 95%;
            }

            table {
                font-size: 13px;
            }

            th,
            td {
                padding: 8px;
            }
        }

    </style>

</head>


<body>


<div class="header">

    <h1>Learning Management System</h1>

    <p>
        Logged in as:
        <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?>
    </p>

</div>


<div class="container">


    <a href="dashboard.php" class="back-button">
        ← Dashboard
    </a>


    <?php if ($message != ""): ?>

        <div class="success">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>


    <?php if ($error != ""): ?>

        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <div class="count-box">

        Total Courses:

        <span class="count-number">
            <?php echo $total_courses; ?>
        </span>

    </div>


    <?php if ($edit_course): ?>


        <!-- EDIT COURSE -->

        <div class="card">

            <h2>Edit Course</h2>

            <form method="POST">

                <input
                    type="hidden"
                    name="id"
                    value="<?php echo $edit_course['id']; ?>"
                >

                <label>Course Title *</label>

                <input
                    type="text"
                    name="title"
                    value="<?php echo htmlspecialchars($edit_course['title']); ?>"
                    required
                >


                <label>Description</label>

                <textarea
                    name="description"
                ><?php echo htmlspecialchars($edit_course['description'] ?? ''); ?></textarea>


                <button type="submit" name="update_course">
                    Update Course
                </button>


                <a href="courses.php" class="cancel-btn">
                    Cancel
                </a>

            </form>

        </div>


    <?php else: ?>


        <!-- ADD COURSE -->

        <div class="card">

            <h2>Add New Course</h2>

            <form method="POST">


                <label>Course Title *</label>

                <input
                    type="text"
                    name="title"
                    placeholder="Enter course title"
                    required
                >


                <label>Description</label>

                <textarea
                    name="description"
                    placeholder="Enter course description"
                ></textarea>


                <button type="submit" name="add_course">
                    Add Course
                </button>

            </form>

        </div>


    <?php endif; ?>


    <!-- COURSE RECORDS -->

    <div class="card">

        <h2>Course Records</h2>


        <?php if ($courses_result->num_rows > 0): ?>


            <div style="overflow-x:auto;">

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Course Title</th>

                            <th>Description</th>

                            <th>Status</th>

                            <th>Created</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while ($course = $courses_result->fetch_assoc()): ?>


                        <tr>


                            <td>
                                <?php echo $course['id']; ?>
                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $course['title']
                                );
                                ?>
                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $course['description'] ?? ''
                                );
                                ?>
                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $course['status'] ?? ''
                                );
                                ?>
                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $course['created_at']
                                );
                                ?>
                            </td>


                            <td>

                                <a
                                    href="courses.php?edit=<?php echo $course['id']; ?>"
                                    class="edit-btn"
                                >
                                    Edit
                                </a>


                                <a
                                    href="courses.php?delete=<?php echo $course['id']; ?>"
                                    class="delete-btn"
                                    onclick="return confirm('Are you sure you want to delete this course?');"
                                >
                                    Delete
                                </a>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                    </tbody>

                </table>

            </div>


        <?php else: ?>


            <p>No courses found.</p>


        <?php endif; ?>


    </div>


</div>


</body>

</html>