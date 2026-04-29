<?php

// TOKEN NOVO DO BOT
$token = "8248830236:AAFpVQ9WWNA0D6BoI8mx3PP2m4TcwvxklYU";
$api_url = "https://api.telegram.org/bot{$token}/";

// Admins sem @
$administradores = ["antalogicmeme"];

function bot($method, $parameters = []) {
    global $api_url;

    $ch = curl_init($api_url . $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

function isAdministrador($username) {
    global $administradores;
    return in_array($username, $administradores);
}

function start($chat_id, $nome) {
    $txt = "🔹 *Bem-vindo, {$nome}*\n\n"
         . "Bot funcionando na Hostinger ✅\n\n"
         . "Comandos disponíveis:\n"
         . "/start - iniciar bot\n"
         . "/ping - testar resposta\n"
         . "/admin - verificar admin";

    bot("sendMessage", [
        "chat_id" => $chat_id,
        "text" => $txt,
        "parse_mode" => "Markdown"
    ]);
}

function processarComando($chat_id, $texto, $username) {
    if ($texto === "/ping") {
        bot("sendMessage", [
            "chat_id" => $chat_id,
            "text" => "pong ✅"
        ]);
        return;
    }

    if ($texto === "/admin") {
        $msg = isAdministrador($username)
            ? "✅ Você é administrador."
            : "❌ Você não é administrador.";

        bot("sendMessage", [
            "chat_id" => $chat_id,
            "text" => $msg
        ]);
        return;
    }

    bot("sendMessage", [
        "chat_id" => $chat_id,
        "text" => "❌ Comando inválido. Use /start"
    ]);
}

// RECEBE WEBHOOK DO TELEGRAM
$update = json_decode(file_get_contents("php://input"), true);

if (!$update) {
    echo "Bot online";
    exit;
}

if (isset($update["message"])) {
    $message = $update["message"];

    $chat_id = $message["chat"]["id"];
    $nome = $message["from"]["first_name"] ?? "Usuário";
    $username = $message["from"]["username"] ?? "";
    $texto = trim($message["text"] ?? "");

    if (strpos($texto, "/start") === 0) {
        start($chat_id, $nome);
    } else {
        processarComando($chat_id, $texto, $username);
    }
}

http_response_code(200);
echo "OK";
