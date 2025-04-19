<?php
require('../database.php');

// Fetch enrollment data
$query = "SELECT timetable_1, timetable_2, timetable_3, timetable_4, timetable_5, timetable_6, timetable_7, timetable_8 
          FROM sms3_enrollment_data";
$result = $conn->query($query);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = array_map('intval', array_values($row)); // Convert timetable IDs to integers
}

// Normalize data for clustering
function normalize($data) {
    $min = min($data);
    $max = max($data);
    return array_map(function ($value) use ($min, $max) {
        return ($value - $min) / ($max - $min);
    }, $data);
}

// Prepare data for clustering
$timetable_data = array_map(function ($row) {
    return normalize($row); // Normalize each row of timetable data
}, $data);

// K-Means Clustering
function kmeans($data, $k, $max_iterations = 100) {
    $centroids = array_slice($data, 0, $k); // Initialize centroids
    $clusters = [];
    for ($iteration = 0; $iteration < $max_iterations; $iteration++) {
        $clusters = array_fill(0, $k, []);
        foreach ($data as $point) {
            $distances = array_map(function ($centroid) use ($point) {
                return sqrt(array_sum(array_map(function ($a, $b) {
                    return pow($a - $b, 2);
                }, $point, $centroid)));
            }, $centroids);
            $cluster_index = array_search(min($distances), $distances);
            $clusters[$cluster_index][] = $point;
        }
        $new_centroids = array_map(function ($cluster) {
            return array_map(function (...$values) {
                return array_sum($values) / count($values);
            }, ...$cluster);
        }, $clusters);
        if ($centroids === $new_centroids) break; // Stop if centroids don't change
        $centroids = $new_centroids;
    }
    return $clusters;
}

// Perform clustering
$k = 3; // Number of clusters
$clusters = kmeans($timetable_data, $k);

// Prepare data for eCharts
$chart_data = [];
foreach ($clusters as $index => $cluster) {
    $chart_data[] = [
        'name' => "Cluster " . ($index + 1),
        'value' => count($cluster)
    ];
}

// Send data to frontend
header('Content-Type: application/json');
echo json_encode($chart_data);
?>