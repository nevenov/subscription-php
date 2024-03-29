<?php 

class FakeLogin {

    private $session;

    public function __construct(&$session)
    {
        $this->session = &$session;
    }

    public function login()
    {
        $this->session['logged_in'] = true;
        $this->session['name'] = 'Stoyan';
        $this->session['email'] = 'example@email.com';
    }

    public function logout()
    {
        session_unset();
        session_destroy();
    }

}