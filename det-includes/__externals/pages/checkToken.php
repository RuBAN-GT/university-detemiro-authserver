<?php
    detemiro::pages()->set(array(
        'function' => function() {
            if(detemiro::auth()->status !== false) {
                header('Content-Type: application/json; charset=utf-8');
            }

            if(detemiro::auth()->status) {
               $res = detemiro::auth()->getByToken();

               if($res) {
                   detemiro::messages()->push(array(
                       'title'  => 'Успех!',
                       'text'   => 'Данные успешно получены.',
                       'type'   => 'auth.result',
                       'status' => 'notice',
                       'code'   => 'auth.successToken'
                   ));
               }
               elseif($res === null) {
                   detemiro::messages()->push(array(
                       'title'  => 'Провал!',
                       'text'   => 'Токен успешно прошёл проверку, но данных он не содержит.',
                       'type'   => 'auth.result',
                       'code'   => 'auth.emptyToken',
                       'status' => 'error'
                   ));
               }
               elseif($res === false) {
                   detemiro::messages()->push(array(
                       'title'  => 'Провал!',
                       'text'   => 'Токен некорректен.',
                       'type'   => 'auth.result',
                       'code'   => 'auth.wrongToken',
                       'status' => 'error'
                   ));

                   $res = null;
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