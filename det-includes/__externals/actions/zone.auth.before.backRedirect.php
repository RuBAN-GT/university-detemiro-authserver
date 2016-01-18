<?php
    detemiro::actions()->add(array(
        'function' => function() {
            if(detemiro::router()->page == 'authentication' && isset($_GET['sessionToken'])) {
                detemiro::auth()->removeData($_GET['sessionToken']);
            }
        }
    ));
?>