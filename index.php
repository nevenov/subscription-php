<?php

session_start();

$session = &$_SESSION;

include('include.php');

include('layout/header.php');

// instead of inlude views we will use Router.php class
// include('layout/views/videos.php');
new Router($_GET, $_POST, $session);

include('layout/footer.php');