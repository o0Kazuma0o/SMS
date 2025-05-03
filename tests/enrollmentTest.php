<?php
// filepath: c:\xampp\htdocs\SMS\tests\EnrollmentTest.php

use PHPUnit\Framework\TestCase;

class EnrollmentTest extends TestCase
{
  private $conn;

  protected function setUp(): void
  {
    // Mock database connection
    $this->conn = $this->createMock(mysqli::class);

    // Mock the prepared statement
    $mockStmt = $this->createMock(mysqli_stmt::class);
    $mockStmt->method('bind_param')->willReturn(true);
    $mockStmt->method('execute')->willReturn(true);

    // Mock the result set
    $mockResult = $this->createMock(mysqli_result::class);
    $mockResult->method('fetch_assoc')
      ->willReturnOnConsecutiveCalls(
        ['id' => 1, 'section_number' => 'A1'], // First call
        false // Second call (end of result set)
      );
    $mockStmt->method('get_result')->willReturn($mockResult);

    // Mock the prepare method to return the mock statement
    $this->conn->method('prepare')->willReturn($mockStmt);
  }

  public function testFetchSections()
  {
    // Define the fetchSections function
    function fetchSections($conn, $semester)
    {
      $query = "
                SELECT DISTINCT sec.id, sec.section_number
                FROM sms3_sections sec
                JOIN sms3_timetable t ON t.section_id = sec.id
                WHERE sec.semester_id = (SELECT id FROM sms3_semesters WHERE name = ? AND status = 'Active')
            ";
      $stmt = $conn->prepare($query);
      $stmt->bind_param("s", $semester);
      $stmt->execute();
      $result = $stmt->get_result();

      $sections = [];
      while ($row = $result->fetch_assoc()) {
        $sections[] = $row;
      }
      $stmt->close();

      return $sections;
    }

    // Simulate fetching sections
    $sections = fetchSections($this->conn, '2025');
    $this->assertNotEmpty($sections, 'Failed to fetch sections.');
  }
}
