
<?php
require_once 'config.php';
requireAdmin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$student = null;

if ($id) {
    $stmt = $pdo->prepare(
        "SELECT * FROM students WHERE id = ? AND role = 'student'"
    );
    $stmt->execute([$id]);
    $student = $stmt->fetch();

    if (!$student) {
        exit("Student not found.");
    }
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['full_name'] ?? '');
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $class = trim($_POST['class_name'] ?? '');
    $password = $_POST['password'] ?? '';

    $validDob = DateTime::createFromFormat('Y-m-d', $dob);
    $validDob = $validDob && $validDob->format('Y-m-d') === $dob
        && $dob <= date('Y-m-d');

    if (
        !$name || !$validDob || !$email || !$phone ||
        !$course || !$class ||
        !in_array($gender, ['Male', 'Female'], true) ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        $message = "Please enter valid details.";

    } elseif ($id && $password === '') {

        try {
            $stmt = $pdo->prepare(
                "UPDATE students SET
                full_name=?, dob=?, gender=?, email=?,
                phone=?, course=?, class_name=?
                WHERE id=? AND role='student'"
            );

            $stmt->execute([
                $name, $dob, $gender, $email,
                $phone, $course, $class, $id
            ]);

            header("Location: dashboard.php");
            exit;

        } catch (PDOException $e) {
            $message = "Unable to update. Check whether the email is already used.";
        }

    } elseif (strlen($password) < 8 || strlen($password) > 72) {
        $message = "Enter a password of 8 to 72 characters.";

    } else {

        try {
            if ($id) {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare(
                    "UPDATE students SET
                    full_name=?, dob=?, gender=?, email=?,
                    phone=?, course=?, class_name=?, password_hash=?
                    WHERE id=? AND role='student'"
                );

                $stmt->execute([
                    $name, $dob, $gender, $email,
                    $phone, $course, $class, $hash, $id
                ]);

            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare(
                    "INSERT INTO students
                    (full_name, dob, gender, email, phone,
                     course, class_name, registration_date,
                     password_hash, role)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'student')"
                );

                $stmt->execute([
                    $name, $dob, $gender, $email,
                    $phone, $course, $class,
                    date('Y-m-d'), $hash
                ]);
            }

            header("Location: dashboard.php");
            exit;

        } catch (PDOException $e) {
            $message = "Unable to save. Check whether the email is already used.";
        }
    }
}

$values = $student ?: [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Student</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="form-container">

    <h2><?= $id ? 'Edit Student' : 'Add Student' ?></h2>

    <?php if ($message): ?>
        <p class="message"><?= e($message) ?></p>
    <?php endif; ?>

    <form method="POST">

        <label>Full Name</label>
        <input name="full_name" required maxlength="150"
               value="<?= e($values['full_name'] ?? '') ?>">

        <label>Date of Birth</label>
        <input type="date" name="dob" required
               max="<?= date('Y-m-d') ?>"
               value="<?= e($values['dob'] ?? '') ?>">

        <label>Gender</label>
        <select name="gender" required>
            <option value="">Select Gender</option>
            <option value="Male"
                <?= ($values['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>
                Male
            </option>
            <option value="Female"
                <?= ($values['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>
                Female
            </option>
        </select>

        <label>Email</label>
        <input type="email" name="email" required maxlength="150"
               value="<?= e($values['email'] ?? '') ?>">

        <label>Phone</label>
        <input name="phone" required maxlength="20"
               value="<?= e($values['phone'] ?? '') ?>">

        <label>Course</label>
        <select name="course" required>
            <option value="">Select Course</option>
            <?php
            $courses = [
                'Electrical Installation',
                'Computer Studies',
                'Mechanical Engineering',
                'Fashion Design',
                'Building Technology'
            ];

            foreach ($courses as $course):
            ?>
                <option value="<?= e($course) ?>"
                    <?= ($values['course'] ?? '') === $course
                        ? 'selected' : '' ?>>
                    <?= e($course) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Class</label>
        <select name="class_name" required>
            <option value="">Select Class</option>
            <?php
            $classes = [
                'Form 1 East',
                'Form 2 West',
                'Form 3 North',
                'Form 4 South'
            ];

            foreach ($classes as $class):
            ?>
                <option value="<?= e($class) ?>"
                    <?= ($values['class_name'] ?? '') === $class
                        ? 'selected' : '' ?>>
                    <?= e($class) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>
            <?= $id ? 'New Password (leave blank to keep current)' : 'Password' ?>
        </label>

        <input type="password" name="password"
               <?= $id ? '' : 'required' ?>
               minlength="8" maxlength="72">

        <button type="submit">
            <?= $id ? 'Update Student' : 'Save Student' ?>
        </button>
    </form>

    <p><a href="dashboard.php">Back to Dashboard</a></p>

</div>
</body>
</html>
