<?php
require_once(__DIR__ . '/../database.php');
require_once(__DIR__ . '/audit_log_function.php');
require_once(__DIR__ . '/session.php');

// Function to handle automatic receipt status updates
function autoUpdateReceiptStatus($conn) {
    $updatedEnrollments = [];
    
    try {
        // Start transaction
        $conn->autocommit(false);

        // Fetch eligible pending enrollments
        $query = "
            SELECT pe.id AS enrollment_id, s.id AS student_id, 
                   pe.timetable_1, pe.timetable_2, pe.timetable_3, pe.timetable_4,
                   pe.timetable_5, pe.timetable_6, pe.timetable_7, pe.timetable_8
            FROM sms3_pending_enrollment pe
            JOIN sms3_students s ON pe.student_id = s.id
            WHERE s.status = 'Enrolled' AND pe.receipt_status != 'Paid'
        ";
        
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Update pending enrollment receipt status
                $stmt = $conn->prepare("UPDATE sms3_pending_enrollment SET receipt_status = 'Paid' WHERE id = ?");
                $stmt->bind_param("i", $row['enrollment_id']);
                $stmt->execute();
                $stmt->close();

                // Insert into enrollment_data
                $insertStmt = $conn->prepare("
                    INSERT INTO sms3_enrollment_data (
                        student_id, timetable_1, timetable_2, timetable_3, timetable_4,
                        timetable_5, timetable_6, timetable_7, timetable_8, receipt_status, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $status = 'Approved';
                $paidStatus = 'Paid';
                $insertStmt->bind_param(
                    "iiiiiiiiiss",
                    $row['student_id'],
                    $row['timetable_1'],
                    $row['timetable_2'],
                    $row['timetable_3'],
                    $row['timetable_4'],
                    $row['timetable_5'],
                    $row['timetable_6'],
                    $row['timetable_7'],
                    $row['timetable_8'],
                    $paidStatus,
                    $status
                );
                $insertStmt->execute();
                $insertStmt->close();

                // Delete from pending enrollments
                $deleteStmt = $conn->prepare("DELETE FROM sms3_pending_enrollment WHERE id = ?");
                $deleteStmt->bind_param("i", $row['enrollment_id']);
                $deleteStmt->execute();
                $deleteStmt->close();

                // Log the audit entry
                $userId = $_SESSION['user_id']; // Assuming user ID is stored in session
                logAudit($conn, $userId, 'ACCEPT', 'sms3_enrollment_data', $row['enrollment_id'], [
                    'student_id' => $row['student_id'],
                    'timetable_1' => $row['timetable_1'],
                    'timetable_2' => $row['timetable_2'],
                    'timetable_3' => $row['timetable_3'],
                    'timetable_4' => $row['timetable_4'],
                    'timetable_5' => $row['timetable_5'],
                    'timetable_6' => $row['timetable_6'],
                    'timetable_7' => $row['timetable_7'],
                    'timetable_8' => $row['timetable_8'],
                    'receipt_status' => $paidStatus,
                    'status' => $status
                ]);

                $updatedEnrollments[] = $row['enrollment_id'];
            }
        }
        
        // Commit transaction
        $conn->commit();
        $conn->autocommit(true);
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $conn->autocommit(true);
        error_log("Receipt update error: " . $e->getMessage());
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
    
    return ['status' => 'success', 'updatedEnrollments' => $updatedEnrollments];
}

// Main execution
if ($conn) {
    // Run the update process
    $result = autoUpdateReceiptStatus($conn);
    
    // Close connection
    $conn->close();

    // Log results
    $logMessage = date('Y-m-d H:i:s') . " - ";
    $logMessage .= ($result['status'] === 'success') 
        ? "Updated enrollments: " . implode(',', $result['updatedEnrollments'])
        : "Error: " . $result['message'];
    
    file_put_contents('receipt_updates.log', $logMessage . PHP_EOL, FILE_APPEND);
    
    // Optional: Output for manual testing
    if (php_sapi_name() === 'cli') {
        echo $logMessage . PHP_EOL;
    }
} else {
    $error = "Database connection failed";
    error_log($error);
    file_put_contents('receipt_updates.log', date('Y-m-d H:i:s') . " - " . $error . PHP_EOL, FILE_APPEND);
    
    if (php_sapi_name() === 'cli') {
        echo $error . PHP_EOL;
    }
}