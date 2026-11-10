
<?php
require_once 'config.php';

// Prevent accidentally creating duplicate admin accounts
$stmt = $pdo->query(
    "SELECT COUNT(*) FROM students WHERE role = 'admin'"
);

if ($stmt->fetchColumn() > 0) {
    exit("An administrator account already exists.");
}

$name = "System Administrator";
$email = "admin@college.com";
$password = "Admin@12345";

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    "INSERT INTO students
    (full_name, dob, gender, email, phone,
     course, class_name, registration_date,
     password_hash, role)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'admin')"
);

$stmt->execute([
    $name,
    '1990-01-01',
    'Male',
    $email,
    '0700000000',
    'Administration',
    'Admin',
    date('Y-m-d'),
    $hash
]);

echo "Administrator created successfully.";
echo "<br>Email: admin@college.com";
echo "<br>Password: Admin@12345";
?>
