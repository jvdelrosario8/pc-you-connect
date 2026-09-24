<?php

require_once 'db.php';
require_once 'auth.php';

start_app_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$login_type = $_POST['login_type'] ?? '';

/* =========================
   STUDENT LOGIN
   ========================= */

if ($login_type === 'student') {

    $studentID = trim($_POST['studentID'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!ctype_digit($studentID) || $password === '') {
        header("Location: index.php?error=student_invalid");
        exit;
    }

    $sql = "SELECT * FROM StudentLoginCredentials WHERE StudentID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $studentID);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $student = $result->fetch_assoc();

        $storedPassword = (string) $student['Password'];
        $validPassword = password_verify($password, $storedPassword);

        // Upgrade the sample/legacy plaintext password after a successful login.
        if (!$validPassword && hash_equals($storedPassword, $password)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $update = $conn->prepare(
                'UPDATE studentlogincredentials SET Password = ? WHERE StudentID = ?'
            );
            $update->bind_param('si', $newHash, $student['StudentID']);
            $update->execute();
            $update->close();
            $validPassword = true;
        }

        if ($validPassword) {

            login_user(
                (int) $student['StudentID'],
                'student',
                $student['FirstName'] ?? '',
                $student['LastName'] ?? ''
            );

            header("Location: reserve.php");
            exit;

        } else {

            header("Location: index.php?error=student_password");
            exit;

        }

    } else {

        header("Location: index.php?error=student_not_found");
        exit;

    }
}


/* =========================
   ADMIN LOGIN
   ========================= */

elseif ($login_type === 'admin') {

    $adminID = trim($_POST['userID'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!ctype_digit($adminID) || $password === '') {
        header("Location: index.php?error=admin_invalid");
        exit;
    }

    $sql = "SELECT * FROM adminlogincredentials WHERE AdminID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $adminID);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $admin = $result->fetch_assoc();

        $storedPassword = (string) $admin['Password'];
        $validPassword = password_verify($password, $storedPassword);

        // Upgrade old plaintext admin passwords after a successful login.
        if (!$validPassword && hash_equals($storedPassword, $password)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $update = $conn->prepare(
                'UPDATE adminlogincredentials SET Password = ? WHERE AdminID = ?'
            );
            $update->bind_param('si', $newHash, $admin['AdminID']);
            $update->execute();
            $update->close();
            $validPassword = true;
        }

        if ($validPassword) {
            login_user(
                (int) $admin['AdminID'],
                'admin',
                $admin['FirstName'] ?? '',
                $admin['LastName'] ?? ''
            );

            header("Location: admin_dashboard.php");
            exit;

        } else {

            header("Location: index.php?error=admin_password");
            exit;

        }

    } else {

        header("Location: index.php?error=admin_not_found");
        exit;

    }
}


/* =========================
   INVALID LOGIN TYPE
   ========================= */

else {

    header("Location: index.php?error=invalid_login");
    exit;

}

?>