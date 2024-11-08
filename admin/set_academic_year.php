<?php
require('../database.php');
require('../access_control.php'); // Include the file with the checkAccess function
checkAccess('admin'); // Ensure only users with the 'admin' role can access this page

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];

$response = ['success' => setCurrentAcademicYear($id)];
echo json_encode($response);

