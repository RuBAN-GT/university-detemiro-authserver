<?php
    detemiro::pages()->set(array(
        'title'     => 'Аутентификация',
        'function'  => function($form = null) {
            if($form) {
                detemiro::theme()->incFile('authentication.php', array(
                    'form' => $form
                ));
            }
            else {
                detemiro::router()->redirectOnPage('404');
            }
        }
    ));
?>