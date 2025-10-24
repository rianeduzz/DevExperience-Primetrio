<?php
require_once(__DIR__ . '/../controllers/PrevisaoController.php');

$controller = new PrevisaoController();
$controller->analisarAtivos();
