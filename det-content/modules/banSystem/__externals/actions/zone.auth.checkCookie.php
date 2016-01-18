<?php
    detemiro::actions()->add(array(
        'priority' => false,
        'function' => function($id) {
            $t = true;

            if(detemiro::authBan()->ipTry && detemiro::authBan()->checkBan()) {
                $t = false;
            }

            if($t && detemiro::authBan()->idTry && detemiro::authBan()->checkBan($id)) {
                $t = false;
            }

            return $t;
        }
    ));
?>