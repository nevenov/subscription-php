<?php 

class Router {

    
    public function __construct($get, $post, &$session)
    {

        $controller = new Controller($session);

        if (isset($get['action'])) {

            switch($get['action']) {

                case "pricing":
                    $controller->pricing();
                    break;

                case "login":
                    $controller->login();
                    break;

                case "logout":
                    $controller->logout();
                    break;

                case "admin/create-plan":
                    $controller->createPlan();
                    break;    

                default:
                    $controller->showVideos();
                    break;

            }

        } else {
            $controller->showVideos();
        }
    }

}