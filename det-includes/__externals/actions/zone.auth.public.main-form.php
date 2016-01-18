<?php
    detemiro::actions()->add(array(
        'code'     => 'auth.public.service',
        'priority' => true,
        'function' => function() {
            if(isset($_GET['sessionToken'])) {
                echo '<input type="hidden" name="form-service"  value="' . detemiro::auth()->query['service'] . '" />';
                echo '<input type="hidden" name="form-redirect" value="' . detemiro::auth()->query['redirect'] . '" />';
            }
        }
    ));
?>