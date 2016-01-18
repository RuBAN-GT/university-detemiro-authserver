<?php
    detemiro::actions()->add(array(
        'function' => function() {
            return function_exists('radius_auth_open');
        }
    ));
?>