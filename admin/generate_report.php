<?php
require('../database.php');
require_once __DIR__ . '/../vendor/autoload.php'; // Include mPDF library

use Mpdf\Mpdf;

// Fetch data for the report
$dateToday = date('Y-m-d');

// Pending admissions added today
$sql_pending_admissions_today = "SELECT COUNT(*) AS count FROM sms3_pending_admission WHERE DATE(created_at) = '$dateToday'";
$result_pending_admissions_today = $conn->query($sql_pending_admissions_today);
$pending_admissions_today = $result_pending_admissions_today->fetch_assoc()['count'];

// Pending enrollments added today
$sql_pending_enrollments_today = "SELECT COUNT(*) AS count FROM sms3_pending_enrollment WHERE DATE(created_at) = '$dateToday'";
$result_pending_enrollments_today = $conn->query($sql_pending_enrollments_today);
$pending_enrollments_today = $result_pending_enrollments_today->fetch_assoc()['count'];

// Total enrolled and not enrolled students
$sql_enrollment_status = "
    SELECT 
        COUNT(CASE WHEN status = 'Enrolled' THEN 1 END) AS enrolled_count,
        COUNT(CASE WHEN status = 'Not Enrolled' THEN 1 END) AS not_enrolled_count
    FROM sms3_students
";
$result_enrollment_status = $conn->query($sql_enrollment_status);
$row_enrollment_status = $result_enrollment_status->fetch_assoc();
$enrolled_count = $row_enrollment_status['enrolled_count'];
$not_enrolled_count = $row_enrollment_status['not_enrolled_count'];

// Students grouped by last school
$sql_last_school = "
    SELECT last_school, COUNT(*) AS count 
    FROM sms3_students 
    GROUP BY last_school 
    ORDER BY count DESC
";
$result_last_school = $conn->query($sql_last_school);
$last_school_data = [];
while ($row = $result_last_school->fetch_assoc()) {
    $last_school_data[] = $row;
}

// Frequency data for admission type
$sql_admission_type = "
    SELECT admission_type, COUNT(*) AS count
    FROM sms3_students
    GROUP BY admission_type
    ORDER BY count DESC
";
$result_admission_type = $conn->query($sql_admission_type);
$admission_type_data = [];
while ($row = $result_admission_type->fetch_assoc()) {
    $admission_type_data[] = $row;
}

// Frequency data for year level
$sql_year_level = "
    SELECT year_level, COUNT(*) AS count
    FROM sms3_students
    GROUP BY year_level
    ORDER BY count DESC
";
$result_year_level = $conn->query($sql_year_level);
$year_level_data = [];
while ($row = $result_year_level->fetch_assoc()) {
    $year_level_data[] = $row;
}

// Frequency data for sex
$sql_sex = "
    SELECT sex, COUNT(*) AS count
    FROM sms3_students
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
<p style='text-align: center;'>Date: " . date('F j, Y') . "</p>

<h2>Summary</h2>
<ul>
    <li><strong>Pending Admissions Today:</strong> $pending_admissions_today</li>
    <li><strong>Pending Enrollments Today:</strong> $pending_enrollments_today</li>
    <li><strong>Total Enrolled Students:</strong> $enrolled_count</li>
    <li><strong>Total Not Enrolled Students:</strong> $not_enrolled_count</li>
</ul>

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
