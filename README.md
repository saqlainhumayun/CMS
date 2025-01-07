<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'edusphere_cms');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details for personalized dashboard
$username = $_SESSION['username'];
$user_query = $conn->prepare("SELECT id, first_name, last_name FROM users WHERE username = ?");
$user_query->bind_param("s", $username);
$user_query->execute();
$user_query->bind_result($user_id, $first_name, $last_name);
$user_query->fetch();
$user_query->close();

// Handle CRUD actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register_course'])) {
        $course_name = trim($_POST['course_name']);
        $stmt = $conn->prepare("INSERT INTO courses (course_name, status) VALUES (?, 'pending')");
        $stmt->bind_param("s", $course_name);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['approve_course'])) {
        $course_id = intval($_POST['course_id']);
        $stmt = $conn->prepare("UPDATE courses SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['delete_course'])) {
        $course_id = intval($_POST['course_id']);
        $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['delete_faq'])) {
        $faq_id = intval($_POST['faq_id']);
        $stmt = $conn->prepare("DELETE FROM faq WHERE id = ?");
        $stmt->bind_param("i", $faq_id);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['add_faq'])) {
        $question = trim($_POST['question']);
        $answer = trim($_POST['answer']);
        $stmt = $conn->prepare("INSERT INTO faq (question, answer) VALUES (?, ?)");
        $stmt->bind_param("ss", $question, $answer);
        $stmt->execute();
        $stmt->close();
    }
}

// Determine which section to display
$active_section = isset($_GET['section']) ? $_GET['section'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - EduSphere CMS</title>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        header {
            background-color: #4a90e2;
            color: white;
            width: 100%;
            padding: 20px 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        header h1 {
            font-size: 1.8em;
        }

        .container {
            display: flex;
            width: 90%;
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .sidebar {
            width: 25%;
            background: #fff;
            padding: 20px;
            border-right: 2px solid #ddd;
        }

        .sidebar h2 {
            font-size: 1.5em;
            margin-bottom: 15px;
            color: #4a90e2;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            margin: 10px 0;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #333;
            font-size: 1em;
            padding: 10px 15px;
            display: block;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .sidebar ul li a:hover {
            background-color: #4a90e2;
            color: white;
        }

        .main-content {
            width: 75%;
            padding: 20px;
        }

        .main-content section {
            display: none;
            margin-bottom: 20px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .main-content section.active {
            display: block;
        }

        .main-content h2 {
            font-size: 1.5em;
            color: #4a90e2;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th,
        table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        table th {
            background-color: #4a90e2;
            color: white;
        }

        button {
            background-color: #4a90e2;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #357abd;
        }
    </style>
</head>
<body>
    
<header>
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center;">
            <!-- AI-generated Picture -->
            <img 
                src="https://robohash.org/<?php echo htmlspecialchars($username); ?>?set=set5" 
                alt="Profile Picture" 
                style="width: 50px; height: 50px; border-radius: 50%; margin-right: 15px;">
            <!-- User's Name -->
            <h1>Welcome, <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></h1>
        </div>
        <!-- Logout Button -->
        <a href="logout.php" 
           style="text-decoration: none; background-color: #4a90e2; color: white; padding: 10px 15px; border-radius: 5px; font-size: 1em; transition: background-color 0.3s ease;">
            Logout
        </a>
    </div>
</header>


    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Navigation</h2>
            <ul>
                <li><a href="?section=dashboard">Dashboard</a></li>
                <li><a href="?section=course-registration">Course Registration</a></li>
                <li><a href="?section=approved-courses">Approved Courses</a></li>
                <li><a href="?section=result-card">Result Card</a></li>
                <li><a href="?section=attendance">Attendance</a></li>
                <li><a href="?section=fee">Fee Details</a></li>
                <li><a href="?section=faq">FAQ Section</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Dashboard Section -->
            <section id="dashboard" class="<?php echo $active_section === 'dashboard' ? 'active' : ''; ?>">
                <h2>Dashboard</h2>
                <p>Welcome to the EduSphere CMS. Use the navigation menu to explore various sections.</p>
            </section>

            <!-- Course Registration Section -->
            <section id="course-registration" class="<?php echo $active_section === 'course-registration' ? 'active' : ''; ?>">
                <h2>Course Registration</h2>
                <form method="POST">
                    <label for="course_name">Course Name:</label>
                    <input type="text" id="course_name" name="course_name" required>
                    <button type="submit" name="register_course">Register Course</button>
                </form>
                <?php
                $result = $conn->query("SELECT id, course_name FROM courses WHERE status = 'pending'");
                if ($result->num_rows > 0) {
                    echo "<table>
                            <tr>
                                <th>Course Name</th>
                                <th>Actions</th>
                            </tr>";
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['course_name']) . "</td>
                                <td>
                                    <form method='POST' style='display:inline;'>
                                        <input type='hidden' name='course_id' value='" . $row['id'] . "'>
                                        <button type='submit' name='approve_course'>Approve</button>
                                    </form>
                                    <form method='POST' style='display:inline;'>
                                        <input type='hidden' name='course_id' value='" . $row['id'] . "'>
                                        <button type='submit' name='delete_course'>Delete</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No pending courses available.</p>";
                }
                ?>
            </section>

            <!-- Approved Courses Section -->
            <!-- Approved Courses Section -->
<section id="approved-courses" class="<?php echo $active_section === 'approved-courses' ? 'active' : ''; ?>">
    <h2>Approved Courses</h2>
    <table>
        <tr>
            <th>Course ID</th>
            <th>Course Name</th>
            <th>Actions</th>
        </tr>
        <?php
        $result = $conn->query("SELECT id, course_name FROM courses WHERE status = 'approved'");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['id']) . "</td>
                        <td>" . htmlspecialchars($row['course_name']) . "</td>
                        <td>
                            <form method='POST' style='display:inline;'>
                                <input type='hidden' name='course_id' value='" . $row['id'] . "'>
                                <button type='submit' name='edit_course'>Edit</button>
                            </form>
                            <form method='POST' style='display:inline;'>
                                <input type='hidden' name='course_id' value='" . $row['id'] . "'>
                                <button type='submit' name='delete_course'>Delete</button>
                            </form>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No approved courses available.</td></tr>";
        }
        ?>
    </table>

    <?php
    // Handle Edit Course
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_course'])) {
        $course_id_to_edit = intval($_POST['course_id']);
        $edit_query = $conn->prepare("SELECT course_name FROM courses WHERE id = ?");
        $edit_query->bind_param("i", $course_id_to_edit);
        $edit_query->execute();
        $edit_query->bind_result($edit_course_name);
        $edit_query->fetch();
        $edit_query->close();
        ?>

        <h3>Edit Course</h3>
        <form method="POST">
            <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course_id_to_edit); ?>">
            <label for="course_name">Course Name:</label>
            <input type="text" name="course_name" value="<?php echo htmlspecialchars($edit_course_name); ?>" required>
            <button type="submit" name="update_course">Update Course</button>
        </form>

        <?php
    }

    // Handle Update Course
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_course'])) {
        $updated_course_id = intval($_POST['course_id']);
        $updated_course_name = trim($_POST['course_name']);

        $update_query = $conn->prepare("UPDATE courses SET course_name = ? WHERE id = ?");
        $update_query->bind_param("si", $updated_course_name, $updated_course_id);
        $update_query->execute();
        $update_query->close();

        echo "<p>Course updated successfully!</p>";
    }

    // Handle Delete Course
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_course'])) {
        $course_id_to_delete = intval($_POST['course_id']);
        $delete_query = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $delete_query->bind_param("i", $course_id_to_delete);
        $delete_query->execute();
        $delete_query->close();

        echo "<p>Course deleted successfully!</p>";
    }
    ?>
</section>


            <!-- FAQ Section -->
            <section id="faq" class="<?php echo $active_section === 'faq' ? 'active' : ''; ?>">
                <h2>FAQ Section</h2>
                <ul id="faq-list">
                    <?php
                    $result = $conn->query("SELECT id, question, answer FROM faq ORDER BY id DESC");
                    while ($row = $result->fetch_assoc()) {
                        echo "<li>
                                <strong>" . htmlspecialchars($row['question']) . "</strong><br>
                                " . htmlspecialchars($row['answer']) . "
                                <form method='POST' style='display:inline;'>
                                    <input type='hidden' name='faq_id' value='" . $row['id'] . "'>
                                    <button type='submit' name='delete_faq'>Delete</button>
                                </form>
                              </li>";
                    }
                    ?>
                </ul>

                <h3>Add New FAQ</h3>
                <form method="POST">
                    <input type="text" name="question" placeholder="Question" required>
                    <textarea name="answer" placeholder="Answer" required></textarea>
                    <button type="submit" name="add_faq">Add FAQ</button>
                </form>
            </section>
            <section id="result-card" class="<?php echo $active_section === 'result-card' ? 'active' : ''; ?>">
    <h2>Result Card</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>User ID</th>
            <th>Semester</th>
            <th>Subject</th>
            <th>Grade</th>
            <th>GPA</th>
            <th>Actions</th>
        </tr>
        <?php
        // Fetch result details for the logged-in user
        $result_query = $conn->prepare("
            SELECT id, user_id, semester, subject_name, grade, gpa 
            FROM results 
            WHERE user_id = ? 
            ORDER BY semester ASC, subject_name ASC
        ");
        $result_query->bind_param("i", $user_id);
        $result_query->execute();
        $result_query->bind_result($result_id, $user_id, $semester, $subject_name, $grade, $gpa);

        $has_results = false;

        while ($result_query->fetch()) {
            $has_results = true;
            echo "
                <tr>
                    <td>" . htmlspecialchars($result_id) . "</td>
                    <td>" . htmlspecialchars($user_id) . "</td>
                    <td>Semester " . htmlspecialchars($semester) . "</td>
                    <td>" . htmlspecialchars($subject_name) . "</td>
                    <td>" . htmlspecialchars($grade) . "</td>
                    <td>" . htmlspecialchars($gpa) . "</td>
                    <td>
                        <form method='POST' style='display:inline;'>
                            <input type='hidden' name='result_id' value='" . $result_id . "'>
                            <button type='submit' name='edit_result'>Edit</button>
                        </form>
                        <form method='POST' style='display:inline;'>
                            <input type='hidden' name='result_id' value='" . $result_id . "'>
                            <button type='submit' name='delete_result'>Delete</button>
                        </form>
                    </td>
                </tr>
            ";
        }

        $result_query->close();

        if (!$has_results) {
            echo "<tr><td colspan='7'>No results available.</td></tr>";
        }
        ?>
    </table>

    <h3>Add New Semester Result</h3>
    <form method="POST">
        <label for="semester">Semester:</label>
        <input type="number" name="semester" min="1" required>
        <label for="subject_name">Subject Name:</label>
        <input type="text" name="subject_name" required>
        <label for="grade">Grade:</label>
        <input type="text" name="grade" required>
        <label for="gpa">GPA:</label>
        <input type="number" step="0.01" name="gpa" required>
        <button type="submit" name="add_result">Add Result</button>
    </form>

    <?php
    // Handle Add Result
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_result'])) {
        $semester = intval($_POST['semester']);
        $subject_name = trim($_POST['subject_name']);
        $grade = trim($_POST['grade']);
        $gpa = floatval($_POST['gpa']);

        $insert_query = $conn->prepare("
            INSERT INTO results (user_id, semester, subject_name, grade, gpa) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $insert_query->bind_param("iissd", $user_id, $semester, $subject_name, $grade, $gpa);
        $insert_query->execute();
        $insert_query->close();

        echo "<p>Result added successfully!</p>";
    }

    // Handle Edit Result
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_result'])) {
        $result_id_to_edit = intval($_POST['result_id']);
        $edit_query = $conn->prepare("
            SELECT semester, subject_name, grade, gpa 
            FROM results 
            WHERE id = ?
        ");
        $edit_query->bind_param("i", $result_id_to_edit);
        $edit_query->execute();
        $edit_query->bind_result($edit_semester, $edit_subject_name, $edit_grade, $edit_gpa);
        $edit_query->fetch();
        $edit_query->close();
        ?>

        <h3>Edit Result</h3>
        <form method="POST">
            <input type="hidden" name="result_id" value="<?php echo htmlspecialchars($result_id_to_edit); ?>">
            <label for="edit_semester">Semester:</label>
            <input type="number" name="edit_semester" value="<?php echo htmlspecialchars($edit_semester); ?>" required>
            <label for="edit_subject_name">Subject Name:</label>
            <input type="text" name="edit_subject_name" value="<?php echo htmlspecialchars($edit_subject_name); ?>" required>
            <label for="edit_grade">Grade:</label>
            <input type="text" name="edit_grade" value="<?php echo htmlspecialchars($edit_grade); ?>" required>
            <label for="edit_gpa">GPA:</label>
            <input type="number" step="0.01" name="edit_gpa" value="<?php echo htmlspecialchars($edit_gpa); ?>" required>
            <button type="submit" name="update_result">Update Result</button>
        </form>

        <?php
    }

    // Handle Update Result
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_result'])) {
        $updated_result_id = intval($_POST['result_id']);
        $updated_semester = intval($_POST['edit_semester']);
        $updated_subject_name = trim($_POST['edit_subject_name']);
        $updated_grade = trim($_POST['edit_grade']);
        $updated_gpa = floatval($_POST['edit_gpa']);

        $update_query = $conn->prepare("
            UPDATE results 
            SET semester = ?, subject_name = ?, grade = ?, gpa = ? 
            WHERE id = ?
        ");
        $update_query->bind_param(
            "issdi",
            $updated_semester,
            $updated_subject_name,
            $updated_grade,
            $updated_gpa,
            $updated_result_id
        );
        $update_query->execute();
        $update_query->close();

        echo "<p>Result record updated successfully!</p>";
    }

    // Handle Delete Result
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_result'])) {
        $result_id_to_delete = intval($_POST['result_id']);
        $delete_query = $conn->prepare("DELETE FROM results WHERE id = ?");
        $delete_query->bind_param("i", $result_id_to_delete);
        $delete_query->execute();
        $delete_query->close();

        echo "<p>Result record deleted successfully!</p>";
    }
    ?>
</section>

<section id="attendance" class="<?php echo $active_section === 'attendance' ? 'active' : ''; ?>">
    <h2>Attendance</h2>
    <table>
        <tr>
            <th>Course Name</th>
            <th>Date</th>
            <th>Attendance (%)</th>
        </tr>
        <?php
        // Query to fetch attendance records for the logged-in user
        $attendance_query = $conn->prepare("
            SELECT c.course_name, a.date, a.attendance_percentage 
            FROM attendance AS a 
            JOIN courses AS c ON a.course_id = c.id 
            WHERE a.user_id = ? 
            ORDER BY a.date DESC
        ");
        $attendance_query->bind_param("i", $user_id);
        $attendance_query->execute();
        $attendance_query->bind_result($course_name, $date, $attendance_percentage);

        $has_attendance = false;

        while ($attendance_query->fetch()) {
            $has_attendance = true;
            // Set red color if attendance is less than 75%
            $row_style = $attendance_percentage < 75 ? "style='color: red;'" : "";
            echo "
                <tr $row_style>
                    <td>" . htmlspecialchars($course_name) . "</td>
                    <td>" . htmlspecialchars($date) . "</td>
                    <td>" . htmlspecialchars($attendance_percentage) . "%</td>
                </tr>
            ";
        }

        $attendance_query->close();

        if (!$has_attendance) {
            echo "<tr><td colspan='3'>No attendance records available.</td></tr>";
        }
        ?>
    </table>

    <h3>Add Attendance Record</h3>
    <form method="POST">
        <label for="course_id">Course:</label>
        <select name="course_id" required>
            <?php
            // Fetch courses for the dropdown
            $courses_query = $conn->query("SELECT id, course_name FROM courses");
            while ($course = $courses_query->fetch_assoc()) {
                echo "<option value='" . $course['id'] . "'>" . htmlspecialchars($course['course_name']) . "</option>";
            }
            ?>
        </select>
        <label for="attendance_date">Date:</label>
        <input type="date" name="attendance_date" required>
        <label for="attendance_percentage">Attendance Percentage:</label>
        <input type="number" name="attendance_percentage" min="0" max="100" required>
        <button type="submit" name="add_attendance">Add Attendance</button>
    </form>

    <?php
    // Handle Add Attendance
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_attendance'])) {
        $course_id = intval($_POST['course_id']);
        $attendance_date = $_POST['attendance_date'];
        $attendance_percentage = floatval($_POST['attendance_percentage']);

        $insert_query = $conn->prepare("
            INSERT INTO attendance (user_id, course_id, date, attendance_percentage) 
            VALUES (?, ?, ?, ?)
        ");
        $insert_query->bind_param("iisd", $user_id, $course_id, $attendance_date, $attendance_percentage);
        $insert_query->execute();
        $insert_query->close();

        echo "<p>Attendance record added successfully!</p>";
    }
    ?>
</section>
<section id="fee" class="<?php echo $active_section === 'fee' ? 'active' : ''; ?>">
    <h2>Fee Details</h2>
    <table>
        <tr>
            <th>Semester</th>
            <th>Tuition Fee</th>
            <th>Other Charges</th>
            <th>Total Fee</th>
            <th>Status</th>
        </tr>
        <?php
        // Query to fetch fee details for the logged-in user
        $fee_query = $conn->prepare("
            SELECT semester, tuition_fee, other_charges, total_fee, status 
            FROM fee_details 
            WHERE user_id = ? 
            ORDER BY semester DESC
        ");
        $fee_query->bind_param("i", $user_id);
        $fee_query->execute();
        $fee_query->bind_result($semester, $tuition_fee, $other_charges, $total_fee, $status);

        $has_fees = false;

        while ($fee_query->fetch()) {
            $has_fees = true;
            echo "
                <tr>
                    <td>Semester " . htmlspecialchars($semester) . "</td>
                    <td>" . htmlspecialchars($tuition_fee) . "</td>
                    <td>" . htmlspecialchars($other_charges) . "</td>
                    <td>" . htmlspecialchars($total_fee) . "</td>
                    <td>" . htmlspecialchars($status) . "</td>
                </tr>
            ";
        }

        $fee_query->close();

        if (!$has_fees) {
            echo "<tr><td colspan='5'>No fee records available.</td></tr>";
        }
        ?>
    </table>

    <h3>Add Current Semester Fee</h3>
    <form method="POST">
        <label for="semester">Semester:</label>
        <input type="number" name="semester" required>
        <label for="tuition_fee">Tuition Fee:</label>
        <input type="number" name="tuition_fee" required>
        <label for="other_charges">Other Charges:</label>
        <input type="number" name="other_charges" required>
        <label for="status">Status:</label>
        <select name="status" required>
            <option value="Pending">Pending</option>
            <option value="Paid">Paid</option>
        </select>
        <button type="submit" name="add_fee">Add Fee</button>
    </form>

    <?php
    // Handle Add Fee
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_fee'])) {
        $semester = intval($_POST['semester']);
        $tuition_fee = floatval($_POST['tuition_fee']);
        $other_charges = floatval($_POST['other_charges']);
        $status = trim($_POST['status']);
        $total_fee = $tuition_fee + $other_charges;

        $insert_query = $conn->prepare("
            INSERT INTO fee_details (user_id, semester, tuition_fee, other_charges, total_fee, status) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $insert_query->bind_param("iiddis", $user_id, $semester, $tuition_fee, $other_charges, $total_fee, $status);
        $insert_query->execute();
        $insert_query->close();

        echo "<p>Fee details added successfully!</p>";
    }
    ?>
</section>

        </div>
    </div>
</body>
</html>
