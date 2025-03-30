<?php
class SARIMAModel
{
  private $historicalData;
  private $seasonalPeriod;
  private $forecastPeriods;
  private $differencingOrder;
  private $p; // Autoregressive (AR) terms
  private $d; // Differencing
  private $q; // Moving Average (MA) terms
  private $P; // Seasonal AR terms
  private $D; // Seasonal differencing
  private $Q; // Seasonal MA terms

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
        'admissions' => $data[$i]['admissions'] - $data[$i - $this->differencingOrder]['admissions']
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
    $y = array_column($data, 'admissions');

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
      $seasonalSums[$monthIndex] += $entry['admissions'];
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

    if ($lastHistoricalYear == $currentYear && $lastHistoricalMonth >= 7) {
      // If historical data includes July or August of the current year, forecast for next year's July and August
      $forecastYear = $currentYear + 1;
    }

    $forecast = [];
    // Forecast for July and August of the forecast year
    $forecastMonths = [7, 8];
    foreach ($forecastMonths as $month) {
      $seasonalIndex = ($month - 1) % $this->seasonalPeriod;
      $predictedValue = end($trendComponent) + $seasonalComponent[$seasonalIndex];

      $forecast[] = [
        'year' => $forecastYear,
        'month' => $month,
        'predicted_admissions' => round($predictedValue)
      ];
    }

    return $forecast;
  }
  private function reintegrateForecast($forecast, $originalData)
  {
    $reintegratedForecast = [];
    $lastOriginalValue = end($originalData)['admissions'];

    foreach ($forecast as $prediction) {
      $lastOriginalValue += $prediction['predicted_admissions'];
      $prediction['predicted_admissions'] = $lastOriginalValue;
      $reintegratedForecast[] = $prediction;
    }

    return $reintegratedForecast;
  }
}
