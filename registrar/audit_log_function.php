<?php

// Log audit entries into the database
function logAudit($conn, $userId, $action, $targetTable, $targetId = null, $details = null) {
    $shortDetails = $details ? generateShortDetails($action, $targetTable, $details) : null;

    $stmt = $conn->prepare("INSERT INTO sms3_audit_log (user_id, action, target_table, target_id, details) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issis", $userId, $action, $targetTable, $targetId, $shortDetails);
    $stmt->execute();
    $stmt->close();
}

// Generate short details dynamically for any table
function generateShortDetails($action, $table, $details) {
    $summary = "";

    switch ($action) {
        case 'ADD':
            $summary = "Added to $table: " . formatDetails($details);
            break;

        case 'EDIT':
            $old = $details['old'] ?? [];
            $new = $details['new'] ?? [];
            $changes = [];

            foreach ($new as $key => $value) {
                // Log changes only if old value exists and differs from the new value
                if (array_key_exists($key, $old) && $old[$key] != $value) { // Use != for string-int comparisons
                    $changes[] = "'$key' from '{$old[$key]}' to '$value'";
                }
            }

            if (!empty($changes)) {
                $summary = "Edited $table (ID: {$details['id']}): " . implode("; ", $changes);
            } else {
                $summary = "Edited $table (ID: {$details['id']}): No changes detected.";
            }
            break;

        case 'DELETE':
            $summary = "Deleted from $table (ID: {$details['id']}): " . formatDetails($details);
            break;

        default:
            $summary = "Performed $action on $table.";
            break;
    }

    return $summary;
}

// Helper function to format details into a readable string
function formatDetails($details) {
    if (is_array($details)) {
        return implode("; ", array_map(
            fn($key, $value) => ucfirst(str_replace('_', ' ', $key)) . ": '$value'",
            array_keys($details),
            $details
        ));
    }
    return (string)$details;
}