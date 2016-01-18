<?php
    detemiro::actions()->add(array(
        'code'     => 'auth.employeeRemove.logout',
        'function' => function($id) {
            detemiro::auth()->removeData("employee.{$id}");
        }
    ));
?>