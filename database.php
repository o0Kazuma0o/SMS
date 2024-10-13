<?php
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable exception for errors
$conn = new mysqli('localhost', 'root', '', 'bcp-sms_admission');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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

// Function to get the room_id based on room_name
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

// Edit department
$edit_department = null;
if (isset($_GET['edit_department_id'])) {
    $edit_department_id = $_GET['edit_department_id'];

    // Fetch the department details to pre-fill the form for editing
    $stmt = $conn->prepare("SELECT * FROM sms3_departments WHERE id = ?");
    $stmt->bind_param("i", $edit_department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_department = $result->fetch_assoc();
    $stmt->close();
}

// Add department
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_department'])) {
    $department_code = $_POST['department_code'];
    $department_name = $_POST['department_name'];

    try{
    // Insert department if department code is unique
    $stmt = $conn->prepare("INSERT INTO sms3_departments (department_code, department_name) VALUES (?, ?)");
    $stmt->bind_param("ss", $department_code, $department_name);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success_message'] = "Department added successfully!";
    // Redirect to manage_departments.php
    header('Location: manage_departments.php');
    exit;
    }
    catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // Duplicate entry error code
            $_SESSION['error_message'] = "Error: Duplicate entry for department code or name.";
        } else {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }
        header('Location: manage_departments.php'); // Redirect to show error
        exit;
    }
}

// Update department
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_department'])) {
    $department_id = $_POST['department_id'];  // The ID of the department being updated
    $department_code = $_POST['department_code'];
    $department_name = $_POST['department_name'];

    try{
    // Update the department in the database
    $stmt = $conn->prepare("UPDATE sms3_departments SET department_code = ?, department_name = ? WHERE id = ?");
    $stmt->bind_param("ssi", $department_code, $department_name, $department_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success_message'] = "Department updated successfully!";

    // Redirect to manage_departments.php after updating
    header('Location: manage_departments.php');
    exit;
    }
    catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // Duplicate entry error code
            $_SESSION['error_message'] = "Error: Duplicate entry for department code or name.";
        } else {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }
        header('Location: manage_departments.php'); // Redirect to show error
        exit;
    }
}

// Delete department
if (isset($_GET['delete_department_id'])) {
    $delete_id = $_GET['delete_department_id'];
    try{
    $stmt = $conn->prepare("DELETE FROM sms3_departments WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success_message'] = "Department deleted successfully!";

    // Redirect to manage_departments.php
    header('Location: manage_departments.php');
    exit;
    }
    catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1451) { // Foreign key constraint error code
            $_SESSION['error_message'] = "Error: This department is still connected to other data.";
        } else {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }
        header('Location: manage_departments.php'); // Redirect to show error
        exit;
    }
}

// Fetch all departments
$departments = $conn->query("SELECT * FROM sms3_departments");

// Edit room
$edit_room = null;
if (isset($_GET['edit_room_id'])) {
    $edit_room_id = $_GET['edit_room_id'];

    // Fetch the room details to pre-fill the form for editing
    $stmt = $conn->prepare("SELECT * FROM sms3_rooms WHERE id = ?");
    $stmt->bind_param("i", $edit_room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_room = $result->fetch_assoc();
    $stmt->close();
}


// Add room
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_room'])) {
    $room_name = $_POST['room_name'];
    $location = $_POST['location'];
    $department_id = $_POST['department_id'];

    try{
    // Insert room
    $stmt = $conn->prepare("INSERT INTO sms3_rooms (room_name, location, department_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $room_name, $location, $department_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success_message'] = "Room added successfully!";
    // Redirect to manage_rooms.php
    header('Location: manage_rooms.php');
    exit;
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // Duplicate entry error code
            $_SESSION['error_message'] = "Error: Duplicate entry for department code or name.";
        } else {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }
        header('Location: manage_rooms.php'); // Redirect to show error
        exit;
    }
}

// Update room
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_room'])) {
    $room_id = $_POST['room_id'];  // The ID of the room being updated
    $room_name = $_POST['room_name'];
    $location = $_POST['location'];
    $department_id = $_POST['department_id'];

    try{
    // Update the room in the database
    $stmt = $conn->prepare("UPDATE sms3_rooms SET room_name = ?, location = ?, department_id = ? WHERE id = ?");
    $stmt->bind_param("ssii", $room_name, $location, $department_id, $room_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success_message'] = "Room updated successfully!";

    // Redirect to manage_rooms.php after updating
    header('Location: manage_rooms.php');
    exit;
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // Duplicate entry error code
            $_SESSION['error_message'] = "Error: Duplicate entry for department code or name.";
        } else {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }
        header('Location: manage_rooms.php'); // Redirect to show error
        exit;
    }
}

// Delete room
if (isset($_GET['delete_room_id'])) {
    $delete_id = $_GET['delete_room_id'];
    try{
    $stmt = $conn->prepare("DELETE FROM sms3_rooms WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success_message'] = "Room deleted successfully!";

    // Redirect to manage_rooms.php
    header('Location: manage_rooms.php');
    exit;
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1451) { // Foreign key constraint error code
            $_SESSION['error_message'] = "Error: This room is still connected to other data.";
        } else {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }
        header('Location: manage_rooms.php'); // Redirect to show error
        exit;
    }
}

// Fetch all rooms
$rooms = $conn->query("SELECT r.*, d.department_code FROM sms3_rooms r JOIN sms3_departments d ON r.department_id = d.id");

// Edit section
$edit_section = null;
if (isset($_GET['edit_section_id'])) {
    $edit_section_id = $_GET['edit_section_id'];

    // Fetch the section details to pre-fill the form for editing
    $stmt = $conn->prepare("SELECT * FROM sms3_sections WHERE id = ?");
    $stmt->bind_param("i", $edit_section_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_section = $result->fetch_assoc();
    $stmt->close();
}

// Add section
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_section'])) {
    $section_number = $_POST['section_number'];
    $year_level = $_POST['year_level'];
    $semester = $_POST['semester'];
    $capacity = $_POST['capacity']; 
    $department_id = $_POST['department_id'];

    try {
        // Insert section
        $stmt = $conn->prepare("INSERT INTO sms3_sections (section_number, year_level, semester, capacity, department_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissi", $section_number, $year_level, $semester, $capacity, $department_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success_message'] = "Section added successfully!";
        // After form submission, redirect to the same page using GET method to prevent form resubmission
        header("Location: manage_sections.php");
        exit;
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // Duplicate entry error
            $_SESSION['error_message'] = "Error: Duplicate entry for section number.";
        } else {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }
        header('Location: manage_sections.php');
        exit;
    }
}

// Update section
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_section'])) {
    $section_id = $_POST['section_id'];
    $section_number = $_POST['section_number'];
    $year_level = $_POST['year_level'];
    $semester = $_POST['semester'];
    $capacity = $_POST['capacity'];
    $department_id = $_POST['department_id'];

    try {
        // Update the section in the database
        $stmt = $conn->prepare("UPDATE sms3_sections SET section_number = ?, year_level = ?, semester = ?, capacity = ?, department_id = ? WHERE id = ?");
        $stmt->bind_param("iissii", $section_number, $year_level, $semester, $capacity, $department_id, $section_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success_message'] = "Section updated successfully!";
        header('Location: manage_sections.php');
        exit;
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // Duplicate entry error
            $_SESSION['error_message'] = "Error: Duplicate entry for section number.";
        } else {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }
        header('Location: manage_sections.php');
        exit;
    }
}

// Delete section
if (isset($_GET['delete_section_id'])) {
    $delete_id = $_GET['delete_section_id'];
    try{
    // Delete the section from the database
    $stmt = $conn->prepare("DELETE FROM sms3_sections WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success_message'] = "Section deleted successfully!";

    // Redirect back to avoid re-submission
    header("Location: manage_sections.php");
    exit;
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1451) { // Foreign key constraint error code
            $_SESSION['error_message'] = "Error: This room is still connected to other data.";
        } else {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }
        header('Location: manage_sections.php'); // Redirect to show error
        exit;
    }
}

// Function to update all sections in DB
function toggleAllSections($conn) {
    // Fetch all sections
    $result = $conn->query("SELECT id, section_number, semester FROM sms3_sections");
    
    while ($section = $result->fetch_assoc()) {
        $current_section_number = $section['section_number'];
        $current_semester = $section['semester'];

        // Toggle the semester and update the section number by 100
        if ($current_semester == '1st') {
            $new_semester = '2nd';
            $new_section_number = $current_section_number + 100; // Increment by 100 for 2nd semester
        } else {
            $new_semester = '1st';
            $new_section_number = $current_section_number - 100; // Decrement by 100 for 1st semester
        }

        // Update the section in the database
        $stmt = $conn->prepare("UPDATE sms3_sections SET section_number = ?, semester = ? WHERE id = ?");
        $stmt->bind_param("isi", $new_section_number, $new_semester, $section['id']);
        $stmt->execute();
        $stmt->close();
    }
}

// Toggle semester using AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_toggle_semester'])) {
    toggleAllSections($conn);
    exit; // Important: Prevent further output for the AJAX response
}

// Fetch all sections
$sections = $conn->query("SELECT s.*, d.department_code FROM sms3_sections s JOIN sms3_departments d ON s.department_id = d.id");

// Edit subject
$edit_subject = null;
if (isset($_GET['edit_subject_id'])) {
    $edit_subject_id = $_GET['edit_subject_id'];

    // Fetch the subject details to pre-fill the form for editing
    $stmt = $conn->prepare("SELECT * FROM sms3_subjects WHERE id = ?");
    $stmt->bind_param("i", $edit_subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_subject = $result->fetch_assoc();
    $stmt->close();
}

// Add subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    $subject_code = $_POST['subject_code'];
    $subject_name = $_POST['subject_name'];
    $department_id = $_POST['department_id'];

    try{
    // Insert subject
    $stmt = $conn->prepare("INSERT INTO sms3_subjects (subject_code, subject_name, department_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $subject_code, $subject_name, $department_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success_message'] = "Subject added successfully!";

    // Redirect to manage_subjects.php
    header('Location: manage_subjects.php');
    exit;
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // Duplicate entry error code
            $_SESSION['error_message'] = "Error: Duplicate entry for subject code or name.";
        } else {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }
        header('Location: manage_subjects.php'); // Redirect to show error
        exit;
    }
}

// Update subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_subject'])) {
    $subject_id = $_POST['subject_id'];  // The ID of the subject being updated
    $subject_code = $_POST['subject_code'];
    $subject_name = $_POST['subject_name'];
    $department_id = $_POST['department_id'];

    try{
    // Update the subject in the database
    $stmt = $conn->prepare("UPDATE sms3_subjects SET subject_code = ?, subject_name = ?, department_id = ? WHERE id = ?");
    $stmt->bind_param("ssii", $subject_code, $subject_name, $department_id, $subject_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success_message'] = "Subject updated successfully!";

    // Redirect to manage_subjects.php after updating
    header('Location: manage_subjects.php');
    exit;
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // Duplicate entry error code
            $_SESSION['error_message'] = "Error: Duplicate entry for subject code or name.";
        } else {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }
        header('Location: manage_subjects.php'); // Redirect to show error
        exit;
    }
}

// Delete subject
if (isset($_GET['delete_subject_id'])) {
    $delete_id = $_GET['delete_subject_id'];
    try{
    $stmt = $conn->prepare("DELETE FROM sms3_subjects WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success_message'] = "Subject deleted successfully!";

    // Redirect to manage_subjects.php
    header('Location: manage_subjects.php');
    exit;
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1451) { // Foreign key constraint error code
            $_SESSION['error_message'] = "Error: This room is still connected to other data.";
        } else {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }
        header('Location: manage_subjects.php'); // Redirect to show error
        exit;
    }
}

// Fetch all subjects
$subjects = $conn->query("SELECT s.*, d.department_code FROM sms3_subjects s JOIN sms3_departments d ON s.department_id = d.id");

// Edit timetable
$edit_timetable = null;
if (isset($_GET['edit_timetable_id'])) {
    $edit_timetable_id = $_GET['edit_timetable_id'];

    // Fetch the timetable details for editing (fetch multiple entries based on timetable id)
    $stmt = $conn->prepare("
        SELECT t.id, t.subject_id, s.subject_code, t.day_of_week, t.start_time, t.end_time 
        FROM sms3_timetable t 
        JOIN sms3_subjects s ON t.subject_id = s.id 
        WHERE t.id = ?");
    $stmt->bind_param("i", $edit_timetable_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_timetable = $result->fetch_all(MYSQLI_ASSOC); // Fetch multiple rows for the timetable
    $stmt->close();
}

// Add timetable with multiple subjects, rooms, and times
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_timetable'])) {
    $section_id = $_POST['section_id'];  // The selected section
    $subjects = $_POST['subjects'];  // Array of subject IDs
    $rooms = $_POST['rooms'];  // Array of room IDs
    $days = $_POST['days'];  // Array of days
    $start_times = $_POST['start_times'];  // Array of start times
    $end_times = $_POST['end_times'];  // Array of end times

    // Ensure all arrays have the same number of entries
    if (count($subjects) == count($rooms) && count($rooms) == count($days) && count($days) == count($start_times) && count($start_times) == count($end_times)) {
        try {
            $conn->begin_transaction();  // Start a transaction

            for ($i = 0; $i < count($subjects); $i++) {
                $subject_id = $subjects[$i];
                $room_id = $rooms[$i];
                $day_of_week = $days[$i];
                $start_time = $start_times[$i];
                $end_time = $end_times[$i];

                // 1. Check if the subject already exists in the same section (ignoring the day)
                $stmt = $conn->prepare("
                    SELECT COUNT(*) 
                    FROM timetable 
                    WHERE section_id = ? 
                    AND subject_id = ?");
                $stmt->bind_param("ii", $section_id, $subject_id);
                $stmt->execute();
                $stmt->bind_result($subject_count);
                $stmt->fetch();
                $stmt->close();

                if ($subject_count > 0) {
                    // Duplicate subject detected in the section
                    $_SESSION['error_message'] = "Error: Duplicate subject in this section.";
                    $conn->rollback();  // Rollback the transaction
                    header('Location: manage_timetable.php');
                    exit;
                }

                // 2. Check for time conflicts within the section on the same day
                $stmt = $conn->prepare("
                    SELECT COUNT(*) 
                    FROM timetable 
                    WHERE section_id = ? 
                    AND day_of_week = ? 
                    AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))
                    AND NOT (end_time = ?)");
                $stmt->bind_param("issssss", $section_id, $day_of_week, $end_time, $start_time, $end_time, $start_time, $start_time);
                $stmt->execute();
                $stmt->bind_result($time_conflict_count);
                $stmt->fetch();
                $stmt->close();

                if ($time_conflict_count > 0) {
                    // Time conflict detected
                    $_SESSION['error_message'] = "Error: Time conflict on {$day_of_week} between {$start_time} and {$end_time}.";
                    $conn->rollback();  // Rollback the transaction
                    header('Location: manage_timetable.php');
                    exit;
                }

                // 3. Insert timetable for each subject, room, day, and time combination
                $stmt = $conn->prepare("
                    INSERT INTO sms3_timetable (subject_id, section_id, room_id, day_of_week, start_time, end_time)
                    VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iiisss", $subject_id, $section_id, $room_id, $day_of_week, $start_time, $end_time);
                $stmt->execute();
            }

            $conn->commit();  // Commit the transaction
            $_SESSION['success_message'] = "Timetable added successfully!";
            header('Location: manage_timetable.php');
            exit;

        } catch (mysqli_sql_exception $e) {
            $conn->rollback();  // Rollback transaction on error
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
            header('Location: manage_timetable.php');
            exit;
        }
    } else {
        // If the array sizes don't match, return an error
        $_SESSION['error_message'] = "Error: Mismatch in subject, room, day, or time fields.";
        header('Location: manage_timetable.php');
        exit;
    }
}

// Update a single timetable entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_single_timetable'])) {
    $timetable_id = $_POST['timetable_id'];  // ID of the timetable being updated
    $section_id = $_POST['section_id'];  // The section ID
    $subject_code = $_POST['subject'];  // Subject code from the form
    $room_name = $_POST['room'];  // Room name from the form
    $day_of_week = $_POST['day'];  // Day from the form
    $start_time = $_POST['start_time'];  // Start time from the form
    $end_time = $_POST['end_time'];  // End time from the form

    try {
        // Fetch the subject_id based on subject_code
        $subject_id = getSubjectIdByCode($subject_code);

        // Fetch the room_id based on room_name
        $room_id = getRoomIdByName($room_name);

        // Check if both IDs are valid
        if ($subject_id && $room_id) {
            // 1. Ensure no duplicate subjects in the same section
            $stmt = $conn->prepare("
                SELECT COUNT(*) 
                FROM sms3_timetable 
                WHERE section_id = ? 
                AND subject_id = ? 
                AND id != ?");
            $stmt->bind_param("iii", $section_id, $subject_id, $timetable_id);
            $stmt->execute();
            $stmt->bind_result($duplicate_subject_count);
            $stmt->fetch();
            $stmt->close();

            if ($duplicate_subject_count > 0) {
                // Subject already exists in the section
                $_SESSION['error_message'] = "Error: Duplicate subject in this section.";
                header('Location: manage_timetable.php');
                exit;
            }

            // 2. Ensure no time conflicts within the section on the same day
            $stmt = $conn->prepare("
                SELECT COUNT(*) 
                FROM sms3_timetable 
                WHERE section_id = ? 
                AND day_of_week = ? 
                AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?)) 
                AND id != ?
                AND NOT (end_time = ?)");
            $stmt->bind_param("issssssi", $section_id, $day_of_week, $end_time, $start_time, $end_time, $start_time, $timetable_id, $start_time);
            $stmt->execute();
            $stmt->bind_result($time_conflict_count);
            $stmt->fetch();
            $stmt->close();

            if ($time_conflict_count > 0) {
                // Time conflict detected
                $_SESSION['error_message'] = "Error: Time conflict on {$day_of_week} between {$start_time} and {$end_time}.";
                header('Location: manage_timetable.php');
                exit;
            }

            // 3. If no conflicts, update the single timetable entry
            $stmt = $conn->prepare("
                UPDATE sms3_timetable
                SET subject_id = ?, room_id = ?, day_of_week = ?, start_time = ?, end_time = ?
                WHERE id = ?");
            $stmt->bind_param("iisssi", $subject_id, $room_id, $day_of_week, $start_time, $end_time, $timetable_id);
            $stmt->execute();

            $_SESSION['success_message'] = "Timetable updated successfully!";
            header('Location: manage_timetable.php');
            exit;
        } else {
            // Handle error if subject_id or room_id is invalid
            $_SESSION['error_message'] = "Invalid subject or room.";
            header('Location: manage_timetable.php');
            exit;
        }
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header('Location: manage_timetable.php');
        exit;
    }
}

// Delete entire timetable for a section (or group of timetables)
if (isset($_GET['delete_timetable_id'])) {
    $delete_id = $_GET['delete_timetable_id'];
    
    try {
        // Delete all timetable entries for a section based on the timetable ID
        $stmt = $conn->prepare("DELETE FROM sms3_timetable WHERE section_id = (SELECT section_id FROM sms3_timetable WHERE id = ?)");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success_message'] = "Entire timetable deleted successfully!";
        header('Location: manage_timetable.php');
        exit;
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header('Location: manage_timetable.php');
        exit;
    }
}

// Delete a single row from the timetable
if (isset($_GET['delete_row_id'])) {
    $delete_row_id = $_GET['delete_row_id'];

    try {
        $stmt = $conn->prepare("DELETE FROM sms3_timetable WHERE id = ?");
        $stmt->bind_param("i", $delete_row_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success_message'] = "Timetable entry deleted successfully!";
        header('Location: manage_timetable.php');
        exit;
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header('Location: manage_timetable.php');
        exit;
    }
}


// Fetch all timetables (adjust to join all relevant data)
$timetables = $conn->query("
    SELECT t.*, s.subject_code, sec.section_number, r.room_name, d.department_code
    FROM sms3_timetable t 
    JOIN sms3_subjects s ON t.subject_id = s.id 
    JOIN sms3_sections sec ON t.section_id = sec.id 
    JOIN rooms r ON t.room_id = r.id
    JOIN sms3_departments d ON sec.department_id = d.id");

