<?php 

class Router {

    
    public function __construct($get, $post, &$session)
    {
        if (isset($get['action'])) {

            switch($get['action']) {

                case "pricing":
                    require_once('layout/views/pricing.php');
                    break;

                case "login":
                    echo 'to do: login';
                    break;

                case "logout":
                echo 'to do: logout';
                    break;

                default:
                    require_once('layout/views/videos.php');
                    break;

            }

        } else {
            require_once('layout/views/videos.php');
        }
    }

}