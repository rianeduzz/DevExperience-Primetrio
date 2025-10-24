<?php
header('Content-Type: application/json');
require_once '../config/Database.php';

$db = new Database();

$sql = "SELECT f.*, c.nome as categoria 
        FROM faq f 
        JOIN faq_categorias c ON f.categoria_id = c.id 
        ORDER BY c.nome, f.ordem";

$faqs = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'faqs' => $faqs
]);
