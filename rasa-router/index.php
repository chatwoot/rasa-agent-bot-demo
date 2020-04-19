<?php

/* Config */

$rasa_url = 'http://localhost:5005';
$chatwoot_url = 'http://localhost:3000';
$chatwoot_bot_token = '<your agent bot token>';


$message_type = $_REQUEST["message_type"];
$message = $_REQUEST["content"];
$conversation = $_REQUEST["conversation"]["display_id"];
$contact = $_REQUEST["contact"]["id"];
$account = $_REQUEST["account"]["id"];


error_log("message_type: {$message_type}", 0);

 
if($message_type == "incoming")
{  
  error_log("sending message to bot: {$message}", 0);
  $bot_response = send_to_bot($contact, $message);
  error_log("bot replied: {$bot_response->text}", 0);
  $create_message = send_to_chatwoot($account, $conversation, $bot_response->text);
}


function send_to_bot($sender, $message){
  global $rasa_url;
  $url = "{$rasa_url}/webhooks/rest/webhook";
  $data = array('sender' => $sender, 'message' => $message);

  $options = array(
  'http' => array(
      'method'  => 'POST',
      'content' => json_encode( $data ),
      'header'=>  "Content-Type: application/json\r\n" .
                  "Accept: application/json\r\n"
      )
  );

  $context  = stream_context_create( $options );
  $result = file_get_contents( $url, false, $context );
  $response = json_decode($result);
  return $response[0];
}



function send_to_chatwoot($account, $conversation, $message){
  global $chatwoot_url, $chatwoot_bot_token; 
  $url = "{$chatwoot_url}/api/v1/accounts/{$account}/conversations/{$conversation}/messages";
  $data = array('content' => $message);

  $options = array(
  'http' => array(
      'method'  => 'POST',
      'content' => json_encode( $data ),
      'header'=>  "Content-Type: application/json\r\n" .
                  "Accept: application/json\r\n" .
                  "api_access_token: {$chatwoot_bot_token}"
      )
  );

  $context  = stream_context_create( $options );
  $result = file_get_contents( $url, false, $context );
  $response = json_decode($result);
  return $result;
}

?>
