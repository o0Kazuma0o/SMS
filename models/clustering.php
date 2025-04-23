<?php
class Clustering
{
  private $db;
  private $batchSize;
  private $totalRecords;
  private $totalBatches;

  public function __construct($db, $batchSize = 500)
  {
    $this->db = $db;
    $this->batchSize = $batchSize;
    $this->calculateTotalRecords();
    $this->totalBatches = ceil($this->totalRecords / $this->batchSize);
  }

  private function calculateTotalRecords()
  {
    $query = "SELECT COUNT(*) FROM sms3_enrollment_data WHERE receipt_status = 'Paid' AND status = 'Approved'";
    $result = $this->db->query($query);
    $this->totalRecords = $result->fetch_assoc()['COUNT(*)'];
  }

  public function processBatches()
  {
    for ($batch = 0; $batch < $this->totalBatches; $batch++) {
      $offset = $batch * $this->batchSize;
      $this->processBatch($offset);
    }
  }

  private function processBatch($offset)
  {
    $query = "
            SELECT 
                ed.id,
                ed.student_id,
                t1.subject_id as subj1,
                t2.subject_id as subj2,
                t3.subject_id as subj3,
                t4.subject_id as subj4,
                t5.subject_id as subj5,
                t6.subject_id as subj6,
                t7.subject_id as subj7,
                t8.subject_id as subj8
            FROM sms3_enrollment_data ed
            LEFT JOIN sms3_timetable t1 ON ed.timetable_1 = t1.id
            LEFT JOIN sms3_timetable t2 ON ed.timetable_2 = t2.id
            LEFT JOIN sms3_timetable t3 ON ed.timetable_3 = t3.id
            LEFT JOIN sms3_timetable t4 ON ed.timetable_4 = t4.id
            LEFT JOIN sms3_timetable t5 ON ed.timetable_5 = t5.id
            LEFT JOIN sms3_timetable t6 ON ed.timetable_6 = t6.id
            LEFT JOIN sms3_timetable t7 ON ed.timetable_7 = t7.id
            LEFT JOIN sms3_timetable t8 ON ed.timetable_8 = t8.id
            WHERE ed.receipt_status = 'Paid' AND ed.status = 'Approved'
            LIMIT {$this->batchSize} OFFSET {$offset}
        ";

    $result = $this->db->query($query, MYSQLI_USE_RESULT);
    $data = array();
    $subjects = array();

    while ($row = $result->fetch_assoc()) {
      $student_data = array();
      for ($i = 1; $i <= 8; $i++) {
        $subject_id = $row['subj' . $i];
        if (!in_array($subject_id, $subjects)) {
          $subjects[] = $subject_id;
        }
        $student_data[] = $subject_id;
      }
      $data[] = $student_data;
    }

    $result->free();

    $clusters = $this->kmeans_clustering($data, 3);
    $this->analyze_clusters($clusters, $subjects);
  }

  private function kmeans_clustering($vectors, $k = 3, $max_iterations = 100)
  {
    $num_points = count($vectors);
    if ($num_points == 0) return array('centroids' => array(), 'clusters' => array());

    $dimensions = count($vectors[0]);
    $centroids = array();
    for ($i = 0; $i < $k; $i++) {
      $random_index = rand(0, $num_points - 1);
      $centroids[] = $vectors[$random_index];
    }

    for ($iteration = 0; $iteration < $max_iterations; $iteration++) {
      $clusters = array_fill(0, $k, array());
      foreach ($vectors as $point) {
        $nearest_centroid = 0;
        $min_distance = INF;
        foreach ($centroids as $centroid_id => $centroid) {
          $distance = $this->calculate_distance($point, $centroid);
          if ($distance < $min_distance) {
            $min_distance = $distance;
            $nearest_centroid = $centroid_id;
          }
        }
        $clusters[$nearest_centroid][] = $point;
      }

      $new_centroids = array();
      foreach ($clusters as $cluster) {
        if (empty($cluster)) {
          $new_centroids[] = $centroids[$i];
          continue;
        }
        $centroid = array_fill(0, $dimensions, 0);
        foreach ($cluster as $point) {
          foreach ($point as $dim => $value) {
            $centroid[$dim] += $value;
          }
        }
        $count = count($cluster);
        foreach ($point as $dim => $value) {
          $centroid[$dim] /= $count;
        }
        $new_centroids[] = $centroid;
      }

      if ($this->centroids_converged($centroids, $new_centroids)) {
        break;
      }
      $centroids = $new_centroids;
    }

    return array('centroids' => $centroids, 'clusters' => $clusters);
  }

  private function calculate_distance($point1, $point2)
  {
    $distance = 0;
    foreach ($point1 as $dim => $value) {
      $distance += pow($value - $point2[$dim], 2);
    }
    return $distance;
  }

  private function centroids_converged($old_centroids, $new_centroids)
  {
    foreach ($old_centroids as $i => $old_centroid) {
      foreach ($old_centroid as $dim => $value) {
        if (abs($value - $new_centroids[$i][$dim]) > 0.001) {
          return false;
        }
      }
    }
    return true;
  }

  private function analyze_clusters($clusters, $subjects)
  {
    $cluster_analysis = array();
    foreach ($clusters as $cluster_id => $cluster_points) {
      $subject_counts = array_fill(0, count($subjects), 0);
      foreach ($cluster_points as $point) {
        foreach ($point as $dim => $value) {
          if ($value == 1) {
            $subject_counts[$dim]++;
          }
        }
      }
      $cluster_analysis[$cluster_id] = $subject_counts;
    }
    $this->store_cluster_analysis($cluster_analysis, $subjects);
  }

  private function store_cluster_analysis($cluster_analysis, $subjects)
  {
    // Create a unique file name with timestamp
    $file_path = __DIR__ . '/cluster_analysis/';
    $file_name = "cluster_analysis_" . date('Y-m-d_H-i-s') . ".csv";
    $full_path = $file_path . $file_name;

    // Create directory if it doesn't exist
    if (!file_exists($file_path)) {
      mkdir($file_path, 0755, true);
    }

    // Open the file for writing
    $file = fopen($full_path, 'w');
    if ($file === false) {
      return "Error: Could not open file for writing.";
    }

    // Write CSV header
    fputcsv($file, array('Cluster Number', 'Subject ID', 'Count'));

    // Write cluster analysis to file
    foreach ($cluster_analysis as $cluster_id => $counts) {
      // Explicitly cast $cluster_id to integer
      $cluster_number = (int)$cluster_id + 1;
      arsort($counts);
      foreach ($counts as $subject_id => $count) {
        if ($count > 0) {
          fputcsv($file, array(
            $cluster_number,
            $subjects[$subject_id],
            $count
          ));
        }
      }
    }

    // Close the file
    fclose($file);

    // Return success message
    return "Cluster analysis stored in file: $full_path";
  }
}
