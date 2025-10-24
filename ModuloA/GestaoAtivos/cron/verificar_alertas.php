<?php
require_once(__DIR__ . '/../controllers/AlertaController.php');

$controller = new AlertaController();
$controller->verificarAlertas();
