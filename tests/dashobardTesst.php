<?php
// filepath: c:\xampp\htdocs\SMS\tests\DashboardTest.php

use PHPUnit\Framework\TestCase;

class DashboardTest extends TestCase
{
    public function testInitializeAnalytics()
    {
        // Mock the Google Client
        $mockClient = $this->createMock(Google\Client::class);
        $mockClient->expects($this->once())->method('setApplicationName')->with('Analytics Reporting');
        $mockClient->expects($this->once())->method('setAuthConfig');
        $mockClient->expects($this->once())->method('setScopes');

        // Test the initialization function
        $client = initializeAnalytics();
        $this->assertInstanceOf(Google\Client::class, $client);
    }
}