<?php

namespace IonChat;

class IonUser extends \WP_User {

    public $origin_url = null;

    public function __get($property){
        if ($property === "origin_url"){
            return \site_url();
        }else{
            return $this->{$property};
        }
    }
}

/*
Ion User - random