<?php

session_start();

require_once "db.php";

$login_type = $_POST['login_type'] ?? '';

/* =========================
   STUDENT LOGIN
   ========================= */

if ($login_type === 'student') {

    $studentID = $_POST['studentID'] ?? '';
    $password = $_POST['password'] ?? '';

    $sql = "SELECT * FROM StudentLoginCredentials WHERE StudentID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $studentID);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $student = $result->fetch_assoc();

        if (password_verify($password, $student['Password'])) {

            $_SESSION['user_id'] = $student['StudentID'];
            $_SESSION['role'] = 'student';

            $_SESSION['StudentID'] = $student['StudentID'];
            $_SESSION['FirstName'] = $student['FirstName'];
            $_SESSION['LastName'] = $student['LastName'];

            header("Location: dashboard.php");
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

    $adminID = $_POST['userID'] ?? '';
    $password = $_POST['password'] ?? '';

    $sql = "SELECT * FROM adminlogincredentials WHERE AdminID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $adminID);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $admin = $result->fetch_assoc();

        if ($password === $admin['Password']) {

            $_SESSION['user_id'] = $admin['AdminID'];
            $_SESSION['role'] = 'admin';

            $_SESSION['AdminID'] = $admin['AdminID'];
            $_SESSION['FirstName'] = $admin['FirstName'];
            $_SESSION['LastName'] = $admin['LastName'];

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