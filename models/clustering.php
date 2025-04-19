<?php
// Debugging: Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '256M'); // Set a reasonable memory limit

require('../database.php');

// Initialize response
$response = [];

// Fetch enrollment data in chunks
$offset = 0;
$limit = 1000; // Process 1000 rows at a time
$k = 3; // Number of clusters

// Initialize centroids from the first chunk
$centroids = [];
$first_chunk = [];

// Fetch the first chunk to initialize centroids
$query = "SELECT timetable_1, timetable_2, timetable_3, timetable_4, timetable_5, timetable_6, timetable_7, timetable_8 
          FROM sms3_enrollment_data LIMIT $offset, $limit";
$result = $conn->query($query);

if (!$result) {
  $response['error'] = 'Database query failed: ' . $conn->error;
  header('Content-Type: application/json');
  echo json_encode($response);
  exit;
}

// Collect the first chunk for centroid initialization
while ($row = $result->fetch_assoc()) {
  $normalized_row = array_map(function ($value) {
    return is_null($value) ? 0 : intval($value);
  }, array_values($row));

  $first_chunk[] = $normalized_row;
}

if (empty($first_chunk)) {
  $response['error'] = 'No data available for clustering';
  header('Content-Type: application/json');
  echo json_encode($response);
  exit;
}

// Initialize centroids from the first chunk
shuffle($first_chunk);
$centroids = array_slice($first_chunk, 0, $k);

// Process remaining data in chunks
$offset = $limit;

// Debugging: Output the number of rows fetched in the first iteration
error_log("Rows fetched in the first iteration: " . count($first_chunk));

// K-Means Clustering function
function calculate_distances($point, $centroids)
{
  $distances = [];
  foreach ($centroids as $centroid) {
    $distance = 0;
    foreach ($point as $feature => $value) {
      $distance += pow($value - $centroid[$feature], 2);
    }
    $distances[] = sqrt($distance);
  }
  return $distances;
}

function update_centroids($clusters, $k)
{
  $centroids = [];
  for ($i = 0; $i < $k; $i++) {
    if (!empty($clusters[$i])) {
      $transposed = array_map(null, ...$clusters[$i]);
      $centroid = [];
      foreach ($transposed as $dimension) {
        $centroid[] = array_sum($dimension) / count($dimension);
      }
      $centroids[] = $centroid;
    } else {
      // If a cluster is empty, keep the previous centroid
      $centroids[] = $i < count($centroids) ? $centroids[$i] : [];
    }
  }
  return $centroids;
}

// Process remaining chunks
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

  $clusters = array_fill(0, $k, []);
  $row_count = 0;

  while ($row = $result->fetch_assoc()) {
    $normalized_row = array_map(function ($value) {
      return is_null($value) ? 0 : intval($value);
    }, array_values($row));

    // Assign the point to the nearest cluster
    $distances = calculate_distances($normalized_row, $centroids);
    $cluster_index = array_search(min($distances), $distances);
    $clusters[$cluster_index][] = $normalized_row;
    $row_count++;
  }

  // Update centroids
  $new_centroids = update_centroids($clusters, $k);

  // Check for convergence
  $centroids_changed = false;
  foreach ($centroids as $index => $centroid) {
    if ($centroid !== $new_centroids[$index]) {
      $centroids_changed = true;
      break;
    }
  }

  if (!$centroids_changed) {
    break; // Centroids have converged, exit early
  }

  $centroids = $new_centroids;
  $offset += $limit;

  // Debugging: Output the number of rows fetched in this iteration
  error_log("Rows fetched in this iteration: $row_count");
} while ($result->num_rows > 0);

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
