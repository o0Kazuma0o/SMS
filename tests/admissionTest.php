<?php
// filepath: c:\xampp\htdocs\SMS\tests\admissionTest.php

use PHPUnit\Framework\TestCase;

class AdmissionTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        // Mock the database connection
        $this->conn = $this->createMock(mysqli::class);

        // Mock the query result
        $mockResult = $this->createMock(mysqli_result::class);
        $mockResult->method('fetch_assoc')->willReturn(['student_number' => '24100001']);

        // Mock the query method to return the mock result
        $this->conn->method('query')->willReturn($mockResult);
    }

    public function testGenerateStudentNumber()
    {
        // Include the function to test
        require_once 'c:\xampp\htdocs\SMS\admin\admission.php';

        // Call the function with the mocked connection
        $generatedNumber = generateStudentNumber($this->conn);

        // Assert the expected result
        $this->assertEquals('24100002', $generatedNumber, 'Student number generation failed.');
    }
}