<?php
    detemiro::actions()->add(array(
        'function' => function($id) {
            detemiro::authBan()->removeBan();
            detemiro::authBan()->removeBan($id);
        }
    ));
?>