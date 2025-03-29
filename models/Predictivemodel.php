<?php
class SARIMAModel
{
  private $historicalData;
  private $seasonalPeriod;
  private $forecastPeriods;

  public function __construct($historicalData, $seasonalPeriod = 12, $forecastPeriods = 3)
  {
    $this->historicalData = $historicalData;
    $this->seasonalPeriod = $seasonalPeriod; // 12 months for yearly seasonality
    $this->forecastPeriods = $forecastPeriods;
  }

  public function forecast()
  {
    $seasonalComponent = $this->calculateSeasonalComponent();
    $trendComponent = $this->calculateTrendComponent();

    $lastItem = end($this->historicalData);
    $lastYear = $lastItem['year'];
    $lastMonth = $lastItem['month'];

    $forecast = [];
    $currentYear = $lastYear;
    $currentMonth = $lastMonth;

    for ($i = 1; $i <= $this->forecastPeriods; $i++) {
      $currentMonth++;

      if ($currentMonth > 12) {
        $currentMonth = 1;
        $currentYear++;
      }

      $seasonalIndex = ($currentMonth - 1) % $this->seasonalPeriod;
      $predictedValue = $trendComponent + $seasonalComponent[$seasonalIndex];

      $forecast[] = [
        'year' => $currentYear,
        'month' => $currentMonth,
        'predicted_admissions' => round($predictedValue)
      ];
    }

    return $forecast;
  }

  private function calculateSeasonalComponent()
  {
    $seasonalSums = array_fill(0, $this->seasonalPeriod, 0);
    $seasonalCounts = array_fill(0, $this->seasonalPeriod, 0);

    foreach ($this->historicalData as $data) {
      $monthIndex = ($data['month'] - 1) % $this->seasonalPeriod;
      $seasonalSums[$monthIndex] += $data['admissions'];
      $seasonalCounts[$monthIndex]++;
    }

    $seasonalComponent = [];
    for ($i = 0; $i < $this->seasonalPeriod; $i++) {
      $seasonalComponent[$i] = $seasonalCounts[$i] > 0 ? $seasonalSums[$i] / $seasonalCounts[$i] : 0;
    }

    return $seasonalComponent;
  }

  private function calculateTrendComponent()
  {
    $x = array_column($this->historicalData, 'month');
    $y = array_column($this->historicalData, 'admissions');

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

    return $intercept; // Simplified trend component
  }
}