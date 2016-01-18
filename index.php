<?php
    require_once('det-core/detconnect.php');

    try {
        detemiro::main();
        detemiro::run();
    }
    catch(Exception $error) {
        exit();
    }
?>