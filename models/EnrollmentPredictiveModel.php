<?php
class EnrollmentSARIMAModel
{
    protected $historicalData; // Changed from private to protected
    protected $seasonalPeriod;
    protected $forecastPeriods;
    protected $differencingOrder;
    protected $p; // Autoregressive (AR) terms
    protected $d; // Differencing
    protected $q; // Moving Average (MA) terms
    protected $P; // Seasonal AR terms
    protected $D; // Seasonal differencing
    protected $Q; // Seasonal MA terms

    public function __construct(
        $historicalData,
        $seasonalPeriod = 12,
        $forecastPeriods = 2,
        $differencingOrder = 1,
        $p = 1,
        $d = 1,
        $q = 1,
        $P = 0,
        $D = 1,
        $Q = 1
    ) {
        $this->historicalData = $historicalData;
        $this->seasonalPeriod = $seasonalPeriod;
        $this->forecastPeriods = $forecastPeriods;
        $this->differencingOrder = $differencingOrder;
        $this->p = $p;
        $this->d = $d;
        $this->q = $q;
        $this->P = $P;
        $this->D = $D;
        $this->Q = $Q;
    }

    public function forecast()
    {
        // Step 1: Differencing
        $differencedData = $this->applyDifferencing($this->historicalData);

        // Step 2: Seasonal Decomposition
        list($trendComponent, $seasonalComponent) = $this->decomposeTimeSeries($differencedData);

        // Step 3: Forecast using SARIMA components
        $forecast = $this->generateForecast($trendComponent, $seasonalComponent);

        // Step 4: Reintegrate the forecasted values
        $forecast = $this->reintegrateForecast($forecast, $this->historicalData);

        return $forecast;
    }

    private function applyDifferencing($data)
    {
        $differencedData = [];
        for ($i = $this->differencingOrder; $i < count($data); $i++) {
            $differencedData[] = [
                'year' => $data[$i]['year'],
                'month' => $data[$i]['month'],
                'enrollments' => $data[$i]['enrollments'] - $data[$i - $this->differencingOrder]['enrollments']
            ];
        }
        return $differencedData;
    }

    private function decomposeTimeSeries($data)
    {
        // Calculate trend using linear regression
        $trend = $this->calculateTrendComponent($data);

        // Calculate seasonality
        $seasonal = $this->calculateSeasonalComponent($data);

        return [$trend, $seasonal];
    }

    private function calculateTrendComponent($data)
    {
        $x = array_column($data, 'month');
        $y = array_column($data, 'enrollments');

        $n = count($x);
        $xMean = array_sum($x) / $n;
        $yMean = array_sum($y) / $n;

        $numerator = 0;
        $denominator = 0;
        for ($i = 0; $i < $n; $i++) {
            $numerator += ($x[$i] - $xMean) * ($y[$i] - $yMean);
            $denominator += ($x[$i] - $xMean) ** 2;
        }

        $slope = $numerator / $denominator;
        $intercept = $yMean - $slope * $xMean;

        // Create trend line
        $trend = [];
        foreach ($data as $entry) {
            $trend[] = $slope * $entry['month'] + $intercept;
        }

        return $trend;
    }

    private function calculateSeasonalComponent($data)
    {
        $seasonalSums = array_fill(0, $this->seasonalPeriod, 0);
        $seasonalCounts = array_fill(0, $this->seasonalPeriod, 0);

        foreach ($data as $entry) {
            $monthIndex = ($entry['month'] - 1) % $this->seasonalPeriod;
            $seasonalSums[$monthIndex] += $entry['enrollments'];
            $seasonalCounts[$monthIndex]++;
        }

        $seasonalComponent = [];
        for ($i = 0; $i < $this->seasonalPeriod; $i++) {
            $seasonalComponent[$i] = $seasonalCounts[$i] > 0 ? $seasonalSums[$i] / $seasonalCounts[$i] : 0;
        }

        return $seasonalComponent;
    }

    private function generateForecast($trendComponent, $seasonalComponent)
    {
        $lastItem = end($this->historicalData);
        $currentYear = date('Y');
        $currentMonth = date('n'); // Current month (1-12)

        // Determine the forecast year
        $forecastYear = $currentYear;

        // Check if the last historical data includes current year's July and August
        $lastHistoricalYear = $lastItem['year'];
        $lastHistoricalMonth = $lastItem['month'];

        if ($lastHistoricalYear == $currentYear && $lastHistoricalMonth >= 1) {
            // If historical data includes July or August of the current year, forecast for next year's July and August
            $forecastYear = $currentYear + 1;
        }

        $forecast = [];
        // Forecast for July and August of the forecast year
        $forecastMonths = [1, 2, 7, 8];
        foreach ($forecastMonths as $month) {
            $seasonalIndex = ($month - 1) % $this->seasonalPeriod;
            $predictedValue = end($trendComponent) + $seasonalComponent[$seasonalIndex];

            $forecast[] = [
                'year' => $forecastYear,
                'month' => $month,
                'predicted_enrollments' => round($predictedValue)
            ];
        }

        return $forecast;
    }

    private function reintegrateForecast($forecast, $originalData)
    {
        $reintegratedForecast = [];
        $lastOriginalValue = end($originalData)['enrollments'];

        foreach ($forecast as $prediction) {
            $lastOriginalValue += $prediction['predicted_enrollments'];
            $prediction['predicted_enrollments'] = $lastOriginalValue;
            $reintegratedForecast[] = $prediction;
        }

        return $reintegratedForecast;
    }
}

class EnrollmentPredictiveModel extends EnrollmentSARIMAModel
{
    private $conn;

    public function __construct($conn, $historicalData, $seasonalPeriod = 12, $forecastPeriods = 2)
    {
        parent::__construct($historicalData, $seasonalPeriod, $forecastPeriods);
        $this->conn = $conn;
    }

    private function getCachedForecast($department)
    {
        $cacheFile = __DIR__ . "/cache/{$department}_forecast.json";
        if (file_exists($cacheFile) && time() - filemtime($cacheFile) < 3600) { // Cache valid for 1 hour
            return json_decode(file_get_contents($cacheFile), true);
        }
        return null;
    }

    private function setCachedForecast($department, $forecast)
    {
        $cacheDir = __DIR__ . "/cache";
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true); // Create cache directory if it doesn't exist
        }

        $cacheFile = "{$cacheDir}/{$department}_forecast.json";
        file_put_contents($cacheFile, json_encode($forecast));
    }

    public function forecastByDepartment($offset = 0, $limit = 12)
    {
        $query = "
            SELECT DISTINCT d.department_name
            FROM sms3_departments d
            LIMIT ?, ?
        ";

        // Use prepare and bind_param for the LIMIT clause
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("ii", $offset, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $forecasts = [];
        while ($row = $result->fetch_assoc()) {
            $department = $row['department_name'];

            // Check if forecast is cached
            $cachedForecast = $this->getCachedForecast($department);
            if ($cachedForecast) {
                $forecasts[$department] = $cachedForecast;
                continue;
            }

            // Fetch historical data for the department
            $historicalData = $this->getHistoricalDataByDepartment($department);

            // Generate forecast
            $this->historicalData = $historicalData;
            $forecast = $this->forecast();
            $forecasts[$department] = $forecast;

            // Cache the forecast
            $this->setCachedForecast($department, $forecast);
        }

        $stmt->close();
        return $forecasts;
    }

    private function getHistoricalDataByDepartment($department)
    {
        $query = "
            SELECT 
                d.department_name,
                MONTH(ed.created_at) AS month,
                YEAR(ed.created_at) AS year,
                COUNT(DISTINCT ed.student_id) AS total_enrollments
            FROM sms3_enrollment_data ed
            INNER JOIN sms3_timetable t ON 
                ed.timetable_1 = t.id OR
                ed.timetable_2 = t.id OR
                ed.timetable_3 = t.id OR
                ed.timetable_4 = t.id OR
                ed.timetable_5 = t.id OR
                ed.timetable_6 = t.id OR
                ed.timetable_7 = t.id OR
                ed.timetable_8 = t.id
            INNER JOIN sms3_sections s ON t.section_id = s.id
            INNER JOIN sms3_departments d ON s.department_id = d.id
            WHERE d.department_name = ?
            GROUP BY d.department_name, month, year
            ORDER BY year, month
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $department);
        $stmt->execute();
        $result = $stmt->get_result();

        $historicalData = [];
        while ($row = $result->fetch_assoc()) {
            $historicalData[] = [
                'year' => $row['year'],
                'month' => $row['month'],
                'enrollments' => $row['total_enrollments']
            ];
        }

        return $historicalData;
    }
}
