<?php
// Debugging: Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '256M'); // Increase memory limit to 256MB

require('../database.php');

// Initialize response
$response = [];

// Fetch enrollment data in chunks
$offset = 0;
$limit = 1000; // Process 1000 rows at a time
$data = [];

do {
  $query = "SELECT timetable_1, timetable_2, timetable_3, timetable_4, timetable_5, timetable_6, timetable_7, timetable_8 
              FROM sms3_enrollment_data LIMIT $offset, $limit";
  $result = $conn->query($query);

  if (!$result) {
    $response['error'] = 'Database query failed: ' . $conn->error;
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
  }

  $row_count = 0; // Debugging: Count rows in this iteration
  while ($row = $result->fetch_assoc()) {
    $data[] = array_map(function ($value) {
      return is_null($value) ? 0 : intval($value);
    }, array_values($row));
    $row_count++;
  }

  error_log("Rows fetched in this iteration: $row_count");

  $offset += $limit;
} while ($result->num_rows > 0);

// Debugging: Output the size of $data
error_log("Total rows in data array: " . count($data));

if (empty($data)) {
  $response['error'] = 'No data available for clustering';
  header('Content-Type: application/json');
  echo json_encode($response);
  exit;
}

// Normalize data for clustering
function normalize($row)
{
  $min = min($row);
  $max = max($row);
  if ($min === $max) {
    return array_fill(0, count($row), 0);
  }
  return array_map(function ($value) use ($min, $max) {
    return ($value - $min) / ($max - $min);
  }, $row);
}

// Prepare data for clustering
$timetable_data = array_map('normalize', $data);

// Debugging: Output the first few rows of normalized data
error_log("Normalized data: " . print_r(array_slice($timetable_data, 0, 5), true));

// K-Means Clustering
function kmeans($data, $k, $max_iterations = 100)
{
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
      $transposed = array_map(null, ...$cluster); // Transpose the cluster data
      return array_map(function ($dimension) {
        return array_sum($dimension) / count($dimension); // Calculate the average for each dimension
      }, $transposed);
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
