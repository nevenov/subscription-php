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

                case "admin/show-plans":
                    $controller->showPlans();
                    break; 
                
                case "admin/delete-plan":
                    $controller->deletePlan();
                    break; 

                case "subscribe":
                    $controller->subscribe();
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