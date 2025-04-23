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

<h2>Students by Last School</h2>
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

// Write the HTML content to the PDF
$mpdf->WriteHTML($html);

// Output the PDF
$mpdf->Output('Dashboard_Report.pdf', 'D');
