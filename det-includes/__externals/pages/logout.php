<?php
    detemiro::pages()->set(array(
        'function'  => function() {
            if(detemiro::auth()->status !== false) {
                header('Content-Type: application/json; charset=utf-8');
            }

            if(detemiro::auth()->status) {
                $res = null;

                if(detemiro::auth()->logout()) {
                   detemiro::messages()->push(array(
                       'title'  => 'Успех!',
                       'text'   => 'Пользователь успешно вышел из системы.',
                       'type'   => 'auth.result',
                       'status' => 'notice',
                       'code'   => 'auth.successLogout'
                   ));

                   $res = true;
                }
                else {
                   detemiro::messages()->push(array(
                       'title'  => 'Провал!',
                       'text'   => 'Не удалось выйти пользователю.',
                       'type'   => 'auth.result',
                       'code'   => 'auth.wrongLogout',
                       'status' => 'error'
                   ));

                   $res = false;
                }

                echo \detemiro\space\authSystem::formResult($res);
            }
            elseif(detemiro::auth()->status === null) {
                echo \detemiro\space\authSystem::formResult(null);
            }
            else {
                detemiro::router()->redirectOnPage('404');
            }
        }
    ));
?>