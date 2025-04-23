<?php

class KMeansClustering
{
  private $k;
  private $max_iterations;
  private $features;

  public function __construct($k = 3, $max_iterations = 100)
  {
    $this->k = $k;
    $this->max_iterations = $max_iterations;
    $this->features = array();
  }

  public function fetch_enrollment_data()
  {
    global $conn;
    $query = "
          SELECT 
              e.student_id,
              t.subject_id,
              t.section_id,
              t.day_of_week,
              t.start_time,
              t.end_time
          FROM 
              sms3_pending_enrollment e
          LEFT JOIN 
              sms3_timetable t 
              ON e.timetable_1 = t.id 
              OR e.timetable_2 = t.id 
              OR e.timetable_3 = t.id 
              OR e.timetable_4 = t.id 
              OR e.timetable_5 = t.id 
              OR e.timetable_6 = t.id 
              OR e.timetable_7 = t.id 
              OR e.timetable_8 = t.id;
      ";
    $result = $conn->query($query);
    if (!$result) {
      throw new Exception("Query failed: " . $conn->error);
    }

    while ($row = $result->fetch_assoc()) {
      $this->features[] = $row;
    }
  }

  public function preprocess_data()
  {
    $preprocessed_features = array();
    foreach ($this->features as $row) {
      $startTime = strtotime($row['start_time']);
      $startMinutes = $startTime % 86400;

      $endTime = strtotime($row['end_time']);
      $endMinutes = $endTime % 86400;

      $days = array('Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6);
      $dayNumber = $days[$row['day_of_week']];

      $preprocessed_features[] = array(
        'student_id' => $row['student_id'],
        'subject_id' => $row['subject_id'],
        'section_id' => $row['section_id'],
        'day_number' => $dayNumber,
        'start_minutes' => $startMinutes,
        'end_minutes' => $endMinutes
      );
    }

    return $preprocessed_features;
  }

  // Calculate Euclidean distance between two points
  private function calculate_distance($point1, $point2)
  {
    $distance = 0;
    foreach ($point1 as $key => $value) {
      $distance += pow($value - $point2[$key], 2);
    }
    return sqrt($distance);
  }

  // Initialize centroids
  private function initialize_centroids($data)
  {
    shuffle($data);
    return array_slice($data, 0, $this->k);
  }

  // Assign clusters to each data point
  private function assign_clusters($data, $centroids)
  {
    $clusters = array_fill(0, count($centroids), array());
    foreach ($data as $point) {
      $min_distance = INF;
      $cluster_index = 0;
      foreach ($centroids as $i => $centroid) {
        $distance = $this->calculate_distance($point, $centroid);
        if ($distance < $min_distance) {
          $min_distance = $distance;
          $cluster_index = $i;
        }
      }
      $clusters[$cluster_index][] = $point;
    }
    return $clusters;
  }

  // Update centroids
  private function update_centroids($clusters)
  {
    $centroids = array();
    foreach ($clusters as $cluster) {
      if (empty($cluster)) {
        continue;
      }
      $centroid = array();
      foreach ($cluster as $point) {
        foreach ($point as $key => $value) {
          if (!isset($centroid[$key])) {
            $centroid[$key] = 0;
          }
          $centroid[$key] += $value;
        }
      }
      foreach ($centroid as $key => $value) {
        $centroid[$key] /= count($cluster);
      }
      $centroids[] = $centroid;
    }
    return $centroids;
  }

  // Run the K-means clustering algorithm
  public function run_clustering($data)
  {
    $centroids = $this->initialize_centroids($data);
    for ($i = 0; $i < $this->max_iterations; $i++) {
      $clusters = $this->assign_clusters($data, $centroids);
      $new_centroids = $this->update_centroids($clusters);
      if ($centroids == $new_centroids) {
        break;
      }
      $centroids = $new_centroids;
    }
    return array('clusters' => $clusters, 'centroids' => $centroids);
  }

  // Calculate silhouette coefficient
  public function calculate_silhouette_coefficient($clusters, $data)
  {
    $silhouette = 0;
    $n = count($data);
    foreach ($clusters as $cluster) {
      $cluster_size = count($cluster);
      if ($cluster_size <= 1) {
        continue;
      }
      foreach ($cluster as $point) {
        $a = 0;
        $b = INF;
        // Calculate average distance to all points in the same cluster
        foreach ($cluster as $other_point) {
          $dist = $this->calculate_distance($point, $other_point);
          $a += $dist;
        }
        $a /= $cluster_size;
        // Calculate average distance to points in next nearest cluster
        foreach ($clusters as $other_cluster) {
          if ($other_cluster === $cluster) {
            continue;
          }
          foreach ($other_cluster as $other_point) {
            $dist = $this->calculate_distance($point, $other_point);
            $b = min($b, $dist);
          }
        }
        $silhouette += ($b - $a) / max($a, $b);
      }
    }
    return $silhouette / $n;
  }

  // Elbow method to determine optimal number of clusters
  public function elbow_method($data, $max_k)
  {
    $sum_squared_errors = array();
    for ($k = 1; $k <= $max_k; $k++) {
      $this->k = $k;
      $result = $this->run_clustering($data);
      $clusters = $result['clusters'];
      $sse = 0;
      foreach ($clusters as $cluster) {
        foreach ($cluster as $point) {
          $centroid = $result['centroids'][array_search($cluster, $clusters)];
          $distance = $this->calculate_distance($point, $centroid);
          $sse += $distance ** 2;
        }
      }
      $sum_squared_errors[$k] = $sse;
    }
    return $sum_squared_errors;
  }

  // Main function to perform clustering
  public function cluster()
  {
    $preprocessed_data = $this->preprocess_data();
    $result = $this->run_clustering($preprocessed_data);
    return $result;
  }
}
