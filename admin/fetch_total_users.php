<?php
require '../database.php';
require_once __DIR__ . '/../vendor/autoload.php';

$propertyId = '478793835';

function getTotalUsersByDateRange($propertyId, $startDate, $endDate) {
    try {
        $client = initializeAnalytics();
        $service = new Google\Service\AnalyticsData($client);

        $dateRange = new Google\Service\AnalyticsData\DateRange(
            $startDate,
            $endDate
        );

        $request = new Google\Service\AnalyticsData\RunReportRequest([
            'dateRanges' => [$dateRange],
            'metrics' => [new Google\Service\AnalyticsData\Metric(['name' => 'totalUsers'])]
        ]);

        $response = $service->properties->runReport("properties/$propertyId", $request);

        if (!empty($response->rows)) {
            $firstRow = $response->rows[0];
            if (!empty($firstRow->metricValues)) {
                return $firstRow->metricValues[0]->value;
            }
        }

        return 0;
    } catch (Exception $e) {
        error_log('GA4 Total Users Error: ' . $e->getMessage());
        return 'N/A';
    }
}

function getRelativeDate($range) {
    $today = new DateTime();
    switch ($range) {
        case 'today':
            $startDate = $today->format('today') ?: $today->format('Y-m-d');
            $endDate = $startDate;
            break;
        case 'this_week':
            $today->modify('Monday this week');
            $startDate = $today->format('Y-m-d');
            $today->modify('Sunday this week');
            $endDate = $today->format('Y-m-d');
            break;
        case 'this_month':
            $today->modify('first day of this month');
            $startDate = $today->format('Y-m-d');
            $today->modify('last day of this month');
            $endDate = $today->format('Y-m-d');
            break;
        case 'this_year':
            $today->modify('first day of January this year');
            $startDate = $today->format('Y-m-d');
            $today->modify('last day of December this year');
            $endDate = $today->format('Y-m-d');
            break;
        default:
            $startDate = '2020-01-01';
            $endDate = 'today';
            break;
    }
    return [$startDate, $endDate];
}

if (isset($_GET['range'])) {
    $range = $_GET['range'];
    list($startDate, $endDate) = getRelativeDate($range);
    $totalUsers = getTotalUsersByDateRange($propertyId, $startDate, $endDate);
    echo is_numeric($totalUsers) ? number_format((float)$totalUsers) : $totalUsers;
} else {
    echo 'Invalid request';
}