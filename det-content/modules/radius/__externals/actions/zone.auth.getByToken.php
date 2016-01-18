<?php
    detemiro::actions()->add(array(
        'priority' => 0,
        'function' => function($id) {
            return detemiro::auth()->getData("radius.{$id}");
        }
    ));
?>