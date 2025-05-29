<?php
require('../database.php');
require_once __DIR__ . '/../vendor/autoload.php'; // Include mPDF library

use Mpdf\Mpdf;

// Get date range and report type from GET or fallback to today
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'range';

// Helper for grouping
function getGroupBy($report_type) {
    if ($report_type === 'daily') {
        return "DATE(created_at)";
    } elseif ($report_type === 'monthly') {
        return "DATE_FORMAT(created_at, '%Y-%m')";
    }
    return null;
}

// Summary queries (always for the selected range)
$sql_pending_admissions = "SELECT COUNT(*) AS count FROM sms3_pending_admission WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
$result_pending_admissions = $conn->query($sql_pending_admissions);
$pending_admissions = $result_pending_admissions->fetch_assoc()['count'];

$sql_pending_enrollments = "SELECT COUNT(*) AS count FROM sms3_pending_enrollment WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
$result_pending_enrollments = $conn->query($sql_pending_enrollments);
$pending_enrollments = $result_pending_enrollments->fetch_assoc()['count'];

$sql_accepted_admissions = "SELECT COUNT(*) AS count FROM sms3_admissions_data WHERE status = 'Accepted' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
$result_accepted_admissions = $conn->query($sql_accepted_admissions);
$accepted_admissions = $result_accepted_admissions->fetch_assoc()['count'];

$sql_enrolled_admissions = "SELECT COUNT(*) AS count FROM sms3_admissions_data WHERE status = 'Enrolled' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
$result_enrolled_admissions = $conn->query($sql_enrolled_admissions);
$enrolled_admissions = $result_enrolled_admissions->fetch_assoc()['count'];

$sql_students_enrolled = "SELECT COUNT(*) AS count FROM sms3_students WHERE status = 'Enrolled' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
$result_students_enrolled = $conn->query($sql_students_enrolled);
$students_enrolled = $result_students_enrolled->fetch_assoc()['count'];

$sql_students_not_enrolled = "SELECT COUNT(*) AS count FROM sms3_students WHERE status = 'Not Enrolled' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
$result_students_not_enrolled = $conn->query($sql_students_not_enrolled);
$students_not_enrolled = $result_students_not_enrolled->fetch_assoc()['count'];

// Grouped report if daily or monthly
$group_by = getGroupBy($report_type);
$grouped_data = [];
if ($group_by) {
    // Admissions grouped
    $sql_grouped_admissions = "
        SELECT $group_by AS period, 
            SUM(status = 'Accepted') AS accepted, 
            SUM(status = 'Enrolled') AS enrolled
        FROM sms3_admissions_data
        WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
        GROUP BY period
        ORDER BY period ASC
    ";
    $result_grouped_admissions = $conn->query($sql_grouped_admissions);
    while ($row = $result_grouped_admissions->fetch_assoc()) {
        $grouped_data['admissions'][] = $row;
    }

    // Students grouped
    $sql_grouped_students = "
        SELECT $group_by AS period, 
            SUM(status = 'Enrolled') AS enrolled, 
            SUM(status = 'Not Enrolled') AS not_enrolled
        FROM sms3_students
        WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
        GROUP BY period
        ORDER BY period ASC
    ";
    $result_grouped_students = $conn->query($sql_grouped_students);
    while ($row = $result_grouped_students->fetch_assoc()) {
        $grouped_data['students'][] = $row;
    }
}

// Frequency data for last school in range
$sql_last_school = "
    SELECT last_school, COUNT(*) AS count 
    FROM sms3_students 
    WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
    GROUP BY last_school 
    ORDER BY count DESC
";
$result_last_school = $conn->query($sql_last_school);
$last_school_data = [];
while ($row = $result_last_school->fetch_assoc()) {
    $last_school_data[] = $row;
}

// Frequency data for admission type in range
$sql_admission_type = "
    SELECT admission_type, COUNT(*) AS count
    FROM sms3_students
    WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
    GROUP BY admission_type
    ORDER BY count DESC
";
$result_admission_type = $conn->query($sql_admission_type);
$admission_type_data = [];
while ($row = $result_admission_type->fetch_assoc()) {
    $admission_type_data[] = $row;
}

// Frequency data for year level in range
$sql_year_level = "
    SELECT year_level, COUNT(*) AS count
    FROM sms3_students
    WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
    GROUP BY year_level
    ORDER BY count DESC
";
$result_year_level = $conn->query($sql_year_level);
$year_level_data = [];
while ($row = $result_year_level->fetch_assoc()) {
    $year_level_data[] = $row;
}

// Frequency data for sex in range
$sql_sex = "
    SELECT sex, COUNT(*) AS count
    FROM sms3_students
    WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
    GROUP BY sex
    ORDER BY count DESC
";
$result_sex = $conn->query($sql_sex);
$sex_data = [];
while ($row = $result_sex->fetch_assoc()) {
    $sex_data[] = $row;
}

// Generate the PDF
$mpdf = new Mpdf();
$mpdf->SetTitle('Dashboard Report');

// Add a header and footer
$mpdf->SetHTMLHeader('
    <div style="text-align: center; font-weight: bold; font-size: 14px;">
        Dashboard Report
    </div>
    <hr style="border: 1px solid #000;">
');
$mpdf->SetHTMLFooter('
    <hr style="border: 1px solid #000;">
    <div style="text-align: center; font-size: 12px;">
        Page {PAGENO} of {nbpg}
    </div>
');

// Start HTML content for the PDF
$html = "
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
        color: #333;
    }
    h1 {
        text-align: center;
        color: #0056b3;
    }
    h2 {
        color: #0056b3;
        border-bottom: 2px solid #0056b3;
        padding-bottom: 5px;
    }
    ul {
        list-style-type: none;
        padding: 0;
    }
    ul li {
        margin: 5px 0;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    table th, table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    table th {
        background-color: #f2f2f2;
        color: #333;
    }
</style>

<h1>Dashboard Report</h1>
<p style='text-align: center;'>Date Range: " . htmlspecialchars($start_date) . " to " . htmlspecialchars($end_date) . "</p>
<p style='text-align: center;'><b>Report Type:</b> " . ucfirst($report_type) . "</p>

<h2>Summary</h2>
<ul>
    <li><strong>Pending Admissions in Range:</strong> $pending_admissions</li>
    <li><strong>Pending Enrollments in Range:</strong> $pending_enrollments</li>
    <li><strong>Admissions Accepted in Range:</strong> $accepted_admissions</li>
    <li><strong>Admissions Enrolled in Range:</strong> $enrolled_admissions</li>
    <li><strong>Students Enrolled in Range:</strong> $students_enrolled</li>
    <li><strong>Students Not Enrolled in Range:</strong> $students_not_enrolled</li>
</ul>
";

// If daily/monthly, show grouped table
if ($group_by && !empty($grouped_data)) {
    $html .= "<h2>Grouped Report (" . ucfirst($report_type) . ")</h2>";
    // Admissions
    if (!empty($grouped_data['admissions'])) {
        $html .= "<h3>Admissions</h3>
        <table>
            <thead>
                <tr>
                    <th>" . ($report_type === 'daily' ? 'Date' : 'Month') . "</th>
                    <th>Accepted</th>
                    <th>Enrolled</th>
                </tr>
            </thead>
            <tbody>";
        foreach ($grouped_data['admissions'] as $row) {
            $html .= "<tr>
                <td>" . htmlspecialchars($row['period']) . "</td>
                <td>" . $row['accepted'] . "</td>
                <td>" . $row['enrolled'] . "</td>
            </tr>";
        }
        $html .= "</tbody></table>";
    }
    // Students
    if (!empty($grouped_data['students'])) {
        $html .= "<h3>Students</h3>
        <table>
            <thead>
                <tr>
                    <th>" . ($report_type === 'daily' ? 'Date' : 'Month') . "</th>
                    <th>Enrolled</th>
                    <th>Not Enrolled</th>
                </tr>
            </thead>
            <tbody>";
        foreach ($grouped_data['students'] as $row) {
            $html .= "<tr>
                <td>" . htmlspecialchars($row['period']) . "</td>
                <td>" . $row['enrolled'] . "</td>
                <td>" . $row['not_enrolled'] . "</td>
            </tr>";
        }
        $html .= "</tbody></table>";
    }
}

$html .= "

<h2>Student's Last School Attended</h2>
<table>
    <thead>
        <tr>
            <th>Last School</th>
            <th>Number of Students</th>
        </tr>
    </thead>
    <tbody>";
foreach ($last_school_data as $school) {
    $html .= "
        <tr>
            <td>" . htmlspecialchars($school['last_school']) . "</td>
            <td>" . $school['count'] . "</td>
        </tr>";
}
$html .= "
    </tbody>
</table>";

$html .= "

<h2>Frequency Comparative Analysis</h2>

<h3>Admission Type</h3>
<table>
    <thead>
        <tr>
            <th>Admission Type</th>
            <th>Number of Students</th>
        </tr>
    </thead>
    <tbody>";
foreach ($admission_type_data as $type) {
    $html .= "
        <tr>
            <td>" . htmlspecialchars($type['admission_type']) . "</td>
            <td>" . $type['count'] . "</td>
        </tr>";
}
$html .= "
    </tbody>
</table>

<h3>Year Level</h3>
<table>
    <thead>
        <tr>
            <th>Year Level</th>
            <th>Number of Students</th>
        </tr>
    </thead>
    <tbody>";
foreach ($year_level_data as $level) {
    $html .= "
        <tr>
            <td>" . htmlspecialchars($level['year_level']) . "</td>
            <td>" . $level['count'] . "</td>
        </tr>";
}
$html .= "
    </tbody>
</table>

<h3>Sex</h3>
<table>
    <thead>
        <tr>
            <th>Sex</th>
            <th>Number of Students</th>
        </tr>
    </thead>
    <tbody>";
foreach ($sex_data as $sex) {
    $html .= "
        <tr>
            <td>" . htmlspecialchars($sex['sex']) . "</td>
            <td>" . $sex['count'] . "</td>
        </tr>";
}
$html .= "
    </tbody>
</table>";

// Write the HTML content to the PDF
$mpdf->WriteHTML($html);

// Output the PDF
$mpdf->Output('Dashboard_Report.pdf', 'D');