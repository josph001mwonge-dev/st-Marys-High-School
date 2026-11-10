
<?php
require_once 'config.php';
requireLogin();

$isAdmin = $_SESSION['role'] === 'admin';

if ($isAdmin) {
    $stmt = $pdo->query(
        "SELECT * FROM students WHERE role = 'student' ORDER BY id DESC"
    );
    $students = $stmt->fetchAll();

} else {
    $stmt = $pdo->prepare(
        "SELECT * FROM students WHERE id = ?"
    );
    $stmt->execute([$_SESSION['student_id']]);
    $students = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<nav class="navbar">
    <h2>College Dashboard</h2>
    <div>
        <a href="index.php">Home</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="dashboard">

    <h2>Welcome, <?= e($_SESSION['student_name']) ?></h2>

    <p>
        Account Type:
        <strong><?= e(ucfirst($_SESSION['role'])) ?></strong>
    </p>

    <?php if ($isAdmin): ?>

        <h3>Student Management</h3>

        <a href="student_form.php" class="btn">Add New Student</a>

        <input type="text" id="search"
               placeholder="Search students..."
               class="search-box">

    <?php else: ?>

        <h3>My Registration Details</h3>

    <?php endif; ?>

    <div class="table-container">
        <table id="studentTable">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>DOB</th>
                    <th>Gender</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Course</th>
                    <th>Class</th>
                    <th>Registration Date</th>

                    <?php if ($isAdmin): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>

            <tbody>

            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?= e($student['id']) ?></td>
                    <td><?= e($student['full_name']) ?></td>
                    <td><?= e($student['dob']) ?></td>
                    <td><?= e($student['gender']) ?></td>
                    <td><?= e($student['email']) ?></td>
                    <td><?= e($student['phone']) ?></td>
                    <td><?= e($student['course']) ?></td>
                    <td><?= e($student['class_name']) ?></td>
                    <td><?= e($student['registration_date']) ?></td>

                    <?php if ($isAdmin): ?>
                        <td>
                            <a href="student_form.php?id=<?= e($student['id']) ?>">
                                Edit
                            </a>

                            <form action="delete_student.php" method="POST"
                                  style="display:inline"
                                  onsubmit="return confirm('Delete this student?');">
                                <input type="hidden" name="id"
                                       value="<?= e($student['id']) ?>">
                                <button type="submit" class="delete-btn">
                                    Delete
                                </button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>

            <?php if (count($students) === 0): ?>
                <tr>
                    <td colspan="<?= $isAdmin ? 10 : 9 ?>">
                        No student records found.
                    </td>
                </tr>
            <?php endif; ?>

            </tbody>
        </table>
    </div>
</div>

<script>
const search = document.getElementById('search');

if (search) {
    search.addEventListener('input', function () {
        const value = this.value.toLowerCase();

        document.querySelectorAll('#studentTable tbody tr')
            .forEach(row => {
                row.style.display =
                    row.textContent.toLowerCase().includes(value)
                    ? ''
                    : 'none';
            });
    });
}
</script>

</body>
</html>
