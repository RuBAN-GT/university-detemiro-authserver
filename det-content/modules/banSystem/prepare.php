<?php
    detemiro::actions()->add(array(
        'function' => function() {
            return detemiro::services()->set(
                'authBan',
                function() {
                    return new detemiro\modules\banSystem\banSystem();
                },
                false,
                true
            );
        }
    ));
?>