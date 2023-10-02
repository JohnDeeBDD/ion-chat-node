<?php
/*
Plugin Name: Ion Chat Node
Plugin URI: https://ioncity.ai
Description: The Singularity is here!
Version: 1.0
Author: johndee
Author URI: https://generalchicken.guru
License: Copyright(C) 2023, generalchicken.guru . All rights reserved. THIS IS NOT FREE SOFTWARE.
*/

namespace IonChatNode;

//die("IonChatNode!");

\add_action( 'better_messages_message_sent', 'IonChatNode\on_message_sent', 10, 1 );

// Hook to run the function upon plugin activation
\register_activation_hook(__FILE__, 'IonChatNode\create_ion_city_connections_table');

global $functions;
$functions = [
    createFunctionMetadata(
        "get_current_weather",
        "Get the current weather in a given location",
        [
            "type" => "object",
            "properties" => [
                "location" => [
                    "type" => "string",
                    "description" => "The city and state, e.g. San Francisco, CA",
                ],
                "unit" => [
                    "type" => "string",
                    "enum" => ["celsius", "fahrenheit"]
                ],
            ],
            "required" => ["location"],
        ]
    )
];

function compile_messages_for_transport($messageIDs, $conversationInitiation = []) {
    log_data_to_option($messageIDs, "compile_messages_for_transport_to_ChatGPT");
    $conversationInitiation = [
        ['role' => 'system', 'content' => 'You are a helpful assistant.']
    ];
    global $wpdb; // This is the WordPress database object

    // Check if $messageIDs is an array and not empty
    if (!is_array($messageIDs) || empty($messageIDs)) {
        return false;
    }

    // Convert the message IDs to a comma-separated string
    $ids = implode(',', array_map('intval', $messageIDs)); // Ensure the IDs are integers for security

    // Query the database to get the messages
    $query = "SELECT * FROM {$wpdb->prefix}bm_message_messages WHERE id IN ($ids) ORDER BY date_sent ASC";
    $messages = $wpdb->get_results($query);

    // Initialize the result array with the conversation initiation messages
    $result = $conversationInitiation;

    // Loop through the queried messages and format them
    foreach ($messages as $message) {
        $result[] = [
            'role' => 'user', // Assuming all messages in the database are from users. Adjust as needed.
            'content' => $message->message
        ];
    }

    // Convert the result array to an object
    $objectToSend = (object) $result;

    return $objectToSend;
}

function createFunctionMetadata($name, $description, $parameters) {
    return [
        "name" => $name,
        "description" => $description,
        "parameters" => $parameters
    ];
}

function doPutMessageInDB($sender_id, $thread_id, $content){
    $message_id = Better_Messages()->functions->new_message([
        'sender_id'    => $sender_id,
        'thread_id'    => $thread_id,
        'content'      => $content,
        'return'       => 'message_id',
        'error_type'   => 'wp_error'
    ]);
    if ( is_wp_error( $message_id ) ) {$error = $message_id->get_error_message();}
}

function generateInstructions(){
    return ['system', 'You are a helpful a.i. assistant named "Ion".'];
}

function get_api_key($user_id){
    return \file_get_contents("/var/www/html/wp-content/plugins/ion-chat/api_key.txt");
}

function getIonReply($thread_id ){

    $userInThread = Better_Messages()->functions->get_recipients_ids( $thread_id);

    //Only support for two chatters so far!
    if ( ! (\count($userInThread) ) === 2 ) {
        return;
    }

    $noIons = true;
    foreach($userInThread as $user_id){
        if (isIonUser($user_id)){
            $noIons = false;
        }
    }

    if($noIons){return;}

    $api_key = get_api_key(123/* to do! */);
    log_data_to_option($api_key, "api key");
    $message_thread_ids_array = returnArrayOfMessagesThread($thread_id);
    log_data_to_option($message_thread_ids_array, "message thread ids array");
    $compiled_messages = compile_messages_for_transport($message_thread_ids_array);
    $response = sendUp($compiled_messages, $api_key);
    log_data_to_option($response, "response from GPT");

   if(isset( $response["choices"][0]["message"]["content"] )){
        $response = $response["choices"][0]["message"]["content"];
    }else{
        $response = \var_export($response, true);
    }
    doPutMessageInDB(3, $thread_id, $response);

}

function isIonUser($user_id){
    $user_info = get_userdata($user_id);
    $user_email = $user_info->user_email;

    if($user_email === "jiminac@aol.com") {
        return true;
    }else{
        return false;
    }
}

function log_data_to_option($data, $tag = "tag"){
    $db = get_option('ion-chat');
    \update_option('ion-chat', $db . $tag . "<br />" . \var_export($data, true) . "<br /><br />");
}

function on_message_sent( $message ){
    log_data_to_option($message, "on_message_sent");
    // Sender ID
    $user_id = (int) $message->sender_id;

    // Conversation ID
    $thread_id = $message->thread_id;

    // Message ID
    $message_id = $message->id;

    // Message Content
    $content = $message->message;

    if(!(doesThreadIncludeIon($thread_id))){
        return;
    }
    getIonReply($thread_id);
}

function doesThreadIncludeIon($thread_id){
    return true;
}

/**
 * Retrieves an array of message IDs from a given thread.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int $thread_id The ID of the thread for which to retrieve messages. Default is 1.
 * @param int $last The maximum number of message IDs to retrieve. Default is 1000.
 *
 * @return int[] An array of message IDs.
 */
function returnArrayOfMessagesThread($thread_id = 1, $last = 1000): array {
    global $wpdb;

    // Define the table name
    $table_name = 'wp_bm_message_messages';

    // Prepare the SQL query
    $sql = $wpdb->prepare(
        "SELECT id FROM $table_name WHERE thread_id = %d ORDER BY date_sent DESC LIMIT %d",
        $thread_id,
        $last
    );

    // Execute the query and retrieve the results
    $results = $wpdb->get_col($sql);
    return array_map('intval', $results);
}

function get_mothership_url(){
    //"https://api.openai.com/v1/chat/completions";
    return "http://52.14.142.112";
}

function sendUp($messages, $api_key, $functions = null) {
    // OpenAI API endpoint for ChatGPT
    $url = get_mothership_url();

    // Ensure messages is an array
    $messages = is_object($messages) ? (array) $messages : $messages;

    // Prepare the data for the request
    $data = [
        "model" => "gpt-3.5-turbo-0613",
        //"model" => "gpt-4",
        'messages' => array_values($messages), // Convert to indexed array
        'max_tokens' => 150 // You can adjust this as needed
    ];
    $functions = [
        [
            "name" => "send_user_php",
            "description" => "Send user PHP related data or tasks",
            "parameters" => [
                "type" => "object",
                "properties" => [
                    "php_code" => [
                        "type" => "string",
                        "description" => "The PHP code or script to be sent",
                    ],
                    "action" => [
                        "type" => "string",
                        "enum" => ["execute", "analyze", "store"],
                        "description" => "The action to be performed on the provided PHP code",
                    ],
                ],
                "required" => ["php_code", "action"],
            ],
        ]
    ];


    // If functions are provided, add them to the data
    if ($functions !== null) {
        $data['functions'] = $functions;
    }

    // Initialize cURL session
    $ch = curl_init($url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $api_key",
        'Content-Type: application/json'
    ]);

    // Execute cURL session and get the response
    $response = curl_exec($ch);

    // Close cURL session
    curl_close($ch);

    // Decode the response
    return json_decode($response, true);
}

function transformNicename($niceName){
    //return $niceName;
    if($niceName === "ion"){
        $niceName = "assistant";
    }else{
        $niceName = "user";
    }
    return $niceName;
}

if(isset($_GET['b'])){
    var_dump(\get_option("ion-chat"));die();
}