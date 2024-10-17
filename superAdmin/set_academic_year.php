<?php
require('../database.php');

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];

$response = ['success' => setCurrentAcademicYear($id)];
echo json_encode($response);

