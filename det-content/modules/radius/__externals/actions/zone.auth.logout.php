<?php
    detemiro::actions()->add(array(
        'code'     => 'auth.radiusRemove.logout',
        'function' => function($id) {
            detemiro::auth()->removeData("radius.{$id}");
        }
    ));
?>