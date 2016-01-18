<?php
    detemiro::actions()->add(array(
        'code'     => 'auth.init',
        'priority' => 0,
        'function' => function() {
            detemiro::auth()->init();
        }
    ));
?>