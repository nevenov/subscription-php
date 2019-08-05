<?php 

class Controller {

    private $session;

    const BASE_URL = 'http://localhost/lessons/paypal/subscription/';

    public function __construct(&$session) {

        $this->session = &$session;

    }

    public function pricing()
    {
        require_once('layout/views/pricing.php');
    }

    public function showVideos()
    {
        require_once('layout/views/videos.php');
    }

    public function login()
    {
        // to do login
        return header("Location: " . self::BASE_URL);
    }

    public function logout()
    {
        // to do logout
        return header("Location: " . self::BASE_URL);
    }

}