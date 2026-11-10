
<?php
session_start();
require_once 'db.php';

$message = "";
$messageType = "";

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Allowed values
$allowedGenders = [
    'Male',
    'Female'
];

$allowedClasses = [
    'Form 1 East',
    'Form 2 West',
    'Form 3 North',
    'Form 4 South'
];

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['full_name'] ?? '');
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $class = trim($_POST['class_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    // CSRF validation
    if (
        !is_string($csrfToken) ||
        !hash_equals($_SESSION['csrf_token'], $csrfToken)
    ) {

        $message = "Invalid form submission. Please try again.";

    } elseif (
        $name === '' ||
        $dob === '' ||
        $gender === '' ||
        $email === '' ||
        $phone === '' ||
        $class === '' ||
        $password === ''
    ) {

        $message = "Please fill in all fields.";

    } elseif (strlen($name) > 150) {

        $message = "Full name must not exceed 150 characters.";

    } elseif (strlen($email) > 150) {

        $message = "Email address is too long.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    } elseif (strlen($phone) > 20) {

        $message = "Phone number must not exceed 20 characters.";

    } elseif (!in_array($gender, $allowedGenders, true)) {

        $message = "Please select a valid gender.";

    } 

    } elseif (!in_array($class, $allowedClasses, true)) {

        $message = "Please select a valid class.";

    } elseif (
        strlen($password) < 8 ||
        strlen($password) > 72
    ) {

        $message = "Password must be between 8 and 72 characters.";

    } else {

        // Validate date of birth
        $dobDate = DateTime::createFromFormat('!Y-m-d', $dob);

        if (
            !$dobDate ||
            $dobDate->format('Y-m-d') !== $dob ||
            $dob > date('Y-m-d')
        ) {

            $message = "Please enter a valid date of birth.";

        } 

                    // Registration date
                    $registrationDate = date('Y-m-d');

                    // Insert student details
                    $sql = "INSERT INTO students
                    (
                        full_name,
                        dob,
                        gender,
                        email,
                        phone,
                        registration_date,
                        password_hash,
                        role
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'student')";

                    $stmt = $conn->prepare($sql);

                    $stmt->execute([
                        $name,
                        $dob,
                        $gender,
                        $email,
                        $phone,
                        $class,
                        $registrationDate,
                        $hashedPassword
                    ]);

                    $message = "Registration successful! You can now log in.";

                    $messageType = "success";

                    // Generate a new CSRF token
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }

            } 

    // Set message type
    if ($messageType !== "success") {
        $messageType = "error";
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Student Registration</title>

    <link rel="stylesheet" href="assets/style.css">

</head>

<body>

<div class="form-container">

    <h2>Student Registration</h2>

    <?php if (!empty($message)): ?>

        <p class="message <?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </p>

    <?php endif; ?>

    <form method="POST" action="">

        <!-- CSRF Token -->
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
        >

        <!-- Full Name -->
        <label for="full_name">Full Name</label>

        <input
            type="text"
            id="full_name"
            name="full_name"
            required
            maxlength="150"
            value="<?= htmlspecialchars($_POST['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
        >

        <!-- Date of Birth -->
        <label for="dob">Date of Birth</label>

        <input
            type="date"
            id="dob"
            name="dob"
            required
            max="<?= date('Y-m-d') ?>"
            value="<?= htmlspecialchars($_POST['dob'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
        >

        <!-- Gender -->
        <label for="gender">Gender</label>

        <select id="gender" name="gender" required>

            <option value="">Select Gender</option>

            <option value="Male"
                <?= (($_POST['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>
                Male
            </option>

            <option value="Female"
                <?= (($_POST['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>
                Female
            </option>

        </select>

        <!-- Email -->
        <label for="email">Email Address</label>

        <input
            type="email"
            id="email"
            name="email"
            required
            maxlength="150"
            value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
        >

        <!-- Phone -->
        <label for="phone">Phone Number</label>

        <input
            type="tel"
            id="phone"
            name="phone"
            required
            maxlength="20"
            value="<?= htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
        >

        <!-- Course -->
        <label for="course">Course</label>

        <select id="course" name="course" required>

            <option value="">Select Course</option>

            <?php foreach ($allowedCourses as $item): ?>

                <option
                    value="<?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>"
                    <?= (($_POST['course'] ?? '') === $item) ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>
                </option>

            <?php endforeach; ?>

        </select>

        <!-- Class -->
        <label for="class_name">Class</label>

        <select id="class_name" name="class_name" required>

            <option value="">Select Class</option>

            <?php foreach ($allowedClasses as $item): ?>

                <option
                    value="<?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>"
                    <?= (($_POST['class_name'] ?? '') === $item) ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>
                </option>

            <?php endforeach; ?>

        </select>

        <!-- Password -->
        <label for="password">Password</label>

        <input
            type="password"
            id="password"
            name="password"
            required
            minlength="8"
            maxlength="72"
            autocomplete="new-password"
        >

        <!-- Submit -->
        <button type="submit">
            Register Student
        </button>

    </form>

    <p>
        Already registered?
        <a href="login.php">Login here</a>
    </p>

    <p>
        <a href="index.php">Back Home</a>
    </p>

</div>

</body>
</html>

