<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../database.php'; // Update path if needed to include your database connection

function checkAccess($requiredRole = 'Student') {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== $requiredRole) {
        header("Location: /SMS/index.php");
        exit;
    }
}


// Fetch and set student-specific session information if not already set
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Student') {
    if (!isset($_SESSION['department']) || !isset($_SESSION['year_level'])) {
        $studentData = fetchStudentData($_SESSION['user_id'], $conn);
        $_SESSION['department'] = $studentData['department'];
        $_SESSION['year_level'] = $studentData['year_level'];
    }

    if (!isset($_SESSION['semester'])) {
        $_SESSION['semester'] = getCurrentActiveSemester($conn);
    }
}

/**
 * Function to fetch the student's department and year level
 */
function fetchStudentData($userId, $conn) {
    $stmt = $conn->prepare("SELECT d.department_name AS department, s.year_level
                            FROM sms3_students AS s
                            JOIN sms3_departments AS d ON s.department_id = d.id
                            WHERE s.id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $studentData = $result->fetch_assoc();
    $stmt->close();

    return [
        'department' => $studentData['department'],
        'year_level' => $studentData['year_level']
    ];
}

/**
 * Function to get the current active semester from sms3_semesters
 */
function getCurrentActiveSemester($conn) {
    $stmt = $conn->prepare("SELECT name FROM sms3_semesters WHERE status = 'Active' LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $semester = $result->fetch_assoc()['name'];
    $stmt->close();

    return $semester;
}