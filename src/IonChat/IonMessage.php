<?php

namespace IonChat;

class IonMessage{

    public $sender_email;
    public $thread_id;
    public $message_id;
    public $content;
    public $mothership_url = "http://18.216.171.144";
    public $site_url;
    public $route = "/wp-json/ion/message";
    public $ApplicationPassword = "123";
    public $throttle;

    public function sendToMothership($message){

        $this->consumeBetterMessagesWordPressPluginMessage($message);
        $url = ($this->mothership_url . $this->route);
        \update_site_option('IonMessage', ($this->mothership_url . $this->route));

        // Application Password for authentication
        $ApplicationPassword = $this->applicationPassword;

        // Use WordPress functions to post the Message object to the API
        $ch = curl_init();

        $headers = [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode(':' . $ApplicationPassword)
        ];

        $postFields = ['serializedObject' => serialize($this)];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postFields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            // Handle error, e.g.:
            // echo 'Curl error: ' . curl_error($ch);
        }

        curl_close($ch);

        \update_site_option('IonMessage', array('serializedObject' => \serialize($this)) );
        return true;
    }

    private function consumeBetterMessagesWordPressPluginMessage( $message ){
        //\update_site_option('nope', $message);
        $user_info = get_userdata($message->sender_id);
        $this->sender_email = $user_info->user_email;
        $this->thread_id = $message->thread_id;
        $this->message_id = $message->id;
        $this->content = $message->message;
        $this->site_url = \site_url();
       //
        //\update_site_option('nope', $this);
    }


}