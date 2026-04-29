<?php

$token = "SEU_TOKEN_AQUI";
$api_url = "https://api.telegram.org/bot$token/";

$administradores = ["antalogicmeme"];

function bot($method,$parameters=[]){
global $api_url;

$ch = curl_init($api_url.$method);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($parameters));
curl_setopt($ch,CURLOPT_HTTPHEADER,["Content-Type: application/json"]);

$res = curl_exec($ch);
curl_close($ch);

return json_decode($res,true);
}

function realizarConsulta($url,$parametros){

$full_url = $url.'?'.http_build_query($parametros);

$ch = curl_init();
curl_setopt($ch,CURLOPT_URL,$full_url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);

$response = curl_exec($ch);
curl_close($ch);

return $response;

}

function formatarRespostaBruta($dados){

$dados = preg_replace('/Warning.*\n/','',$dados);
$dados = strip_tags($dados);

$dados = str_replace(['{"','"}','",'],["","","\n"],$dados);
$dados = str_replace(':',' =',$dados);
$dados = str_replace('"','',$dados);

return "*Resultado da consulta:*\n\n".$dados;

}

function processarConsulta($chat_id,$comando,$parametro){

$base_url="https://centralbrasil.shop/apis/";
$key="123";

switch($comando){

case "/consulta_cpf":
$url=$base_url."cpf.php";
$param=["key"=>$key,"cpf"=>$parametro];
break;

case "/consulta_cns":
$url=$base_url."cns.php";
$param=["key"=>$key,"cns"=>$parametro];
break;

case "/consulta_falecimento":
$url=$base_url."data_falecimento.php";
$param=["key"=>$key,"data_falecimento"=>$parametro];
break;

case "/consulta_nascimento":
$url=$base_url."nascimento.php";
$param=["key"=>$key,"nascimento"=>$parametro];
break;

case "/consulta_nome":
$url=$base_url."nome.php";
$param=["key"=>$key,"nome"=>$parametro];
break;

default:

bot("sendMessage",[
"chat_id"=>$chat_id,
"text"=>"❌ Comando inválido"
]);

return;

}

$resultado = realizarConsulta($url,$param);

if($resultado){

$msg=formatarRespostaBruta($resultado);

}else{

$msg="❌ Não foi possível consultar";

}

bot("sendMessage",[

"chat_id"=>$chat_id,
"text"=>$msg,
"parse_mode"=>"Markdown"

]);

}

function start($chat_id,$nome){

$msg="🔎 *Sistema de Consultas*\n\n".
"1️⃣ CPF\n".
"/consulta_cpf 00000000000\n\n".
"2️⃣ CNS\n".
"/consulta_cns 000000000000000\n\n".
"3️⃣ Data nascimento\n".
"/consulta_nascimento 00/00/0000\n\n".
"4️⃣ Nome\n".
"/consulta_nome JOAO\n\n".
"5️⃣ Falecimento\n".
"/consulta_falecimento 00/00/0000";

bot("sendMessage",[

"chat_id"=>$chat_id,
"text"=>$msg,
"parse_mode"=>"Markdown"

]);

}

$update = json_decode(file_get_contents("php://input"),true);

if(isset($update["message"])){

$chat_id=$update["message"]["chat"]["id"];
$texto=$update["message"]["text"]??"";
$nome=$update["message"]["from"]["first_name"]??"";

if(strpos($texto,"/start")===0){

start($chat_id,$nome);

}else{

$partes=explode(" ",$texto,2);
$comando=$partes[0];
$param=$partes[1]??"";

processarConsulta($chat_id,$comando,$param);

}

}

echo "OK";
