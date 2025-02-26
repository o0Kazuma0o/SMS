<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable exception for errors

$environment = 'production';

if ($environment === 'local') {
    $conn = new mysqli('localhost', 'root', '', 'bcp-sms_admission');
} else {
    $conn = new mysqli('localhost', 'admi_caps', 're^AKBzarIgoqxka', 'admi_bcp_sms3_admission');
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


/**
 * Given a subject code, returns the corresponding subject_id from the database.
 * @param string $subject_code The subject code to search for
 * @return int The subject_id, or 0 if not found
 */
function getSubjectIdByCode($subject_code) {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM sms3_subjects WHERE subject_code = ?");
    $stmt->bind_param("s", $subject_code);
    $stmt->execute();
    $stmt->bind_result($subject_id);
    $stmt->fetch();
    $stmt->close();
    return $subject_id;
}


/**
 * Given a room name, returns the corresponding room_id from the database.
 * @param string $room_name The room name to search for
 * @return int The room_id, or 0 if not found
 */
function getRoomIdByName($room_name) {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM sms3_rooms WHERE room_name = ?");
    $stmt->bind_param("s", $room_name);
    $stmt->execute();
    $stmt->bind_result($room_id);
    $stmt->fetch();
    $stmt->close();
    return $room_id;
}

// Function to add an academic year
function addAcademicYear($academicYear, $setCurrent) {
    global $conn;
    try {
        $conn->begin_transaction();

        // If the new academic year should be set as current, reset all others to 0
        if ($setCurrent) {
            $resetCurrent = "UPDATE sms3_academic_years SET is_current = 0 WHERE is_current = 1";
            $conn->query($resetCurrent);
        }

        // Insert the new academic year
        $stmt = $conn->prepare("INSERT INTO sms3_academic_years (academic_year, is_current) VALUES (?, ?)");
        $stmt->bind_param("si", $academicYear, $setCurrent);
        $stmt->execute();

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// Function to get all academic years
function getAcademicYears() {
    global $conn;
    $query = "SELECT * FROM sms3_academic_years ORDER BY id DESC";
    return $conn->query($query);
}

// Function to set an academic year as current
function setCurrentAcademicYear($id) {
    global $conn;
    try {
        $conn->begin_transaction();

        // Reset all others to 0
        $resetCurrent = "UPDATE sms3_academic_years SET is_current = 0 WHERE is_current = 1";
        $conn->query($resetCurrent);

        // Set the specified academic year as current
        $setCurrent = "UPDATE sms3_academic_years SET is_current = 1 WHERE id = ?";
        $stmt = $conn->prepare($setCurrent);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// Function to delete an academic year
function deleteAcademicYear($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM sms3_academic_years WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Fetch the active semester from the database
$activeSemesterResult = $conn->query("SELECT * FROM sms3_semesters WHERE status = 'Active' LIMIT 1");
$activeSemester = $activeSemesterResult->fetch_assoc();

// Set the active semester in the session
if ($activeSemester) {
    $_SESSION['active_semester_id'] = $activeSemester['id'];
    $_SESSION['active_semester_name'] = $activeSemester['name'];
} else {
    $_SESSION['active_semester_id'] = null;
    $_SESSION['active_semester_name'] = "No Active Semester";
}