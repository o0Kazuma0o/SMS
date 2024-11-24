<?php
require('../database.php');
require_once 'session.php';
checkAccess('Registrar'); // Ensure only users with the 'admin' role can access this page

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];

$response = ['success' => deleteAcademicYear($id)];
echo json_encode($response);

