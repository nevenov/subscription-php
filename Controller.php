<?php 

class Controller {

    private $session;

    const BASE_URL = 'http://localhost/lessons/paypal/subscription/';

    public function __construct(&$session) {

        print_r($session);
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
        $login = new FakeLogin($this->session);
        $login->login();
        return header("Location: " . self::BASE_URL);
    }

    public function logout()
    {
        $logout = new FakeLogin($this->session);
        $logout->logout();
        return header("Location: " . self::BASE_URL);
    }

}