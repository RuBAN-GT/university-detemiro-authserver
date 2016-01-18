<?php
    detemiro::actions()->add(array(
        'code'     => 'auth.employeeRemove.Expired',
        'function' => function($id) {
            return detemiro::auth()->removeData("employee.{$id}");
        }
    ));
?>