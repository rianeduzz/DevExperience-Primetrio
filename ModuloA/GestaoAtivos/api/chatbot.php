<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

// Autenticação mínima
if (!isset($_SESSION['user_id'])) {
	http_response_code(401);
	echo json_encode(['error' => 'Usuário não autenticado']);
	exit;
}

// Ler input JSON
$input = json_decode(file_get_contents('php://input'), true);
$pergunta = trim($input['pergunta'] ?? '');
if ($pergunta === '') {
	http_response_code(400);
	echo json_encode(['error' => 'Pergunta vazia']);
	exit;
}

// logs
$logDir = __DIR__ . '/../storage/logs';
@mkdir($logDir, 0755, true);
$logFile = $logDir . '/chatbot.log';
function chatbot_log($msg) {
	global $logFile;
	@file_put_contents($logFile, date('Y-m-d H:i:s') . " | $msg" . PHP_EOL, FILE_APPEND);
}

// Fallback local (consulta simples nas tabelas já existentes)
function fallback_local($pergunta) {
    global $logFile;
    $dbHost = '127.0.0.1';
    $dbUser = 'root';
    $dbPass = '';
    $dbName = 'gestao_ativos';
    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($mysqli->connect_errno) {
        @file_put_contents($logFile, date('Y-m-d H:i:s') . " | DB connect error: " . $mysqli->connect_error . PHP_EOL, FILE_APPEND);
        return null;
    }
    $stmt = $mysqli->prepare("SELECT resposta FROM chatbot_conhecimento WHERE pergunta LIKE ? LIMIT 1");
    $qLike = '%' . $pergunta . '%';
    $stmt->bind_param("s", $qLike);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $stmt->close();
        $mysqli->close();
        return $row['resposta'];
    }
    $stmt->close();
    // keywords fallback
    $words = preg_split('/\s+/', preg_replace('/[^\p{L}\p{N}\s]/u','', mb_strtolower($pergunta)));
    foreach ($words as $w) {
        if (strlen($w) < 3) continue;
        $stmt2 = $mysqli->prepare("SELECT resposta FROM chatbot_respostas WHERE palavra_chave = ? LIMIT 1");
        $stmt2->bind_param("s", $w);
        $stmt2->execute();
        $r2 = $stmt2->get_result();
        if ($rr = $r2->fetch_assoc()) {
            $stmt2->close();
            $mysqli->close();
            return $rr['resposta'];
        }
        $stmt2->close();
    }
    $mysqli->close();
    return null;
}

// Config Gemini via env (preferível) ou ajustar aqui
$geminiKey = getenv('GOOGLE_API_KEY') ?: null;
$geminiProject = getenv('GEMINI_PROJECT') ?: '545890445743';
$geminiModel = getenv('GEMINI_MODEL') ?: 'gen-lang-client-0987251068';
$geminiLocation = getenv('GEMINI_LOCATION') ?: 'us-central1';

// tentativa Gemini quando chave disponível
$resposta = null;
$used_fallback = false;
if ($geminiKey) {
    // Monta endpoint (v1beta2). Ajuste se outro endpoint/version for necessário.
    $modelPath = "projects/{$geminiProject}/locations/{$geminiLocation}/models/{$geminiModel}";
    $url = "https://generativelanguage.googleapis.com/v1beta2/{$modelPath}:generateText?key=" . urlencode($geminiKey);

    // payload compatível (pode exigir ajustes conforme versão da API)
    $payload = [
        "prompt" => [
            "text" => $pergunta
        ],
        "temperature" => 0.2,
        "maxOutputTokens" => 512
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    chatbot_log("Gemini HTTP $httpCode; curlErr: " . ($curlErr ?: 'none'));

    if ($curlErr || $httpCode >= 400) {
        chatbot_log("Gemini erro ou indisponível. Mensagem: " . ($curlErr ?: substr($response,0,1000)));
        // tenta fallback local
        $resposta = fallback_local($pergunta);
        $used_fallback = true;
    } else {
        $body = json_decode($response, true);
        // Primeiro tenta estruturas comuns: 'candidates' ou 'output' / 'content'
        if (isset($body['candidates'][0]['output'])) {
            $resposta = trim($body['candidates'][0]['output']);
        } elseif (isset($body['candidates'][0]['content'])) {
            $resposta = trim($body['candidates'][0]['content']);
        } elseif (isset($body['output'][0]['content'])) {
            $resposta = trim($body['output'][0]['content']);
        } elseif (isset($body['response'])) {
            $resposta = trim($body['response']);
        } else {
            // se resposta vazia, tenta fallback local
            chatbot_log("Gemini retornou sem campo esperado. Body: " . substr($response,0,2000));
            $resposta = fallback_local($pergunta);
            $used_fallback = true;
        }
    }
} else {
    chatbot_log("GOOGLE_API_KEY ausente. Usando fallback local.");
    $resposta = fallback_local($pergunta);
    $used_fallback = true;
}

// Se ainda não há resposta, retornar mensagem genérica
if (!$resposta) {
    echo json_encode([
        'resposta' => 'Desculpe, não encontrei uma resposta no momento. Tente reformular sua pergunta ou contate suporte.',
        'fallback' => $used_fallback
    ]);
    exit;
}

echo json_encode(['resposta' => $resposta, 'fallback' => $used_fallback]);
