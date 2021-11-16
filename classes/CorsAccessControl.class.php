<?php

class CorsAccessControl {

    private $allowed = array();
    // Always adds your own domain with the current ssl settings.
    public function __construct() {
        // Add your own domain, with respect to the current SSL settings.
        $this->allowed[] =
            "http".
            ((array_key_exists("HTTPS", $_SERVER) && $_SERVER["HTTPS"] && strtolower($_SERVER["HTTPS"]) !== "off") ? "s" : null).
            "://".
            $_SERVER["HTTP_HOST"];
    }

    // Optionally add additional domains. Each is only added one time.
    public function add($domain) {
        if (!in_array( $domain, $this->allowed) {
            $this->allowed[] = $domain;
        }
    }
    // Send 'em all as one header so no browsers grumble about it.
    public function send() {
        $domains = implode( ", ", $this->allowed);
        header( "Access-Control-Allow-Origin: ".$domains, true); // We want to send them all as one shot, so replace should be true here.
    }
}