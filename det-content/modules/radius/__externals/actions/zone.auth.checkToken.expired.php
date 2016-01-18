<?php
    detemiro::actions()->add(array(
        'code'     => 'auth.radiusRemove.Expired',
        'function' => function($id) {
            return detemiro::auth()->removeData("radius.{$id}");
        }
    ));
?>