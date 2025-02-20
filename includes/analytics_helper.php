<?php
require_once '../vendor/autoload.php';

function initializeAnalytics() {
    $KEY_FILE_LOCATION = __DIR__ . 'bcp-analytics-api-1951ba8ee03d.json'; // Path to your JSON key file
    
    $client = new Google\Client();
    $client->setApplicationName("Analytics Reporting");
    $client->setAuthConfig($KEY_FILE_LOCATION);
    $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
    return $client;
}

function getRealtimeUsers($propertyId) {
    try {
        $client = initializeAnalytics();
        $service = new Google\Service\AnalyticsData($client);
        
        $request = new Google\Service\AnalyticsData\RunRealtimeReportRequest([
            'dimensions' => [new Google\Service\AnalyticsData\Dimension(['name' => 'country'])],
            'metrics' => [new Google\Service\AnalyticsData\Metric(['name' => 'activeUsers'])]
        ]);
        
        $response = $service->properties->runRealtimeReport("properties/$propertyId", $request);
        return $response->getTotals()[0]->getMetricValues()[0]->getValue();
    } catch (Exception $e) {
        return 'N/A';
    }
}

function getTotalUsers($propertyId) {
    try {
        $client = initializeAnalytics();
        $service = new Google\Service\AnalyticsData($client);
        
        $dateRange = new Google\Service\AnalyticsData\DateRange([
            'start_date' => '2020-01-01', // Adjust as needed
            'end_date' => 'today'
        ]);
        
        $request = new Google\Service\AnalyticsData\RunReportRequest([
            'dateRanges' => [$dateRange],
            'metrics' => [new Google\Service\AnalyticsData\Metric(['name' => 'totalUsers'])]
        ]);
        
        $response = $service->properties->runReport("properties/$propertyId", $request);
        return $response->getTotals()[0]->getMetricValues()[0]->getValue();
    } catch (Exception $e) {
        return 'N/A';
    }
}
?>