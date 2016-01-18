<?php
    /**
     * Страница, на которой происходит создание временной сессии и выдача токена на неё.
     */
    detemiro::pages()->set(array(
        'function' => function() {
            if(detemiro::auth()->status !== false) {
                header('Content-Type: application/json; charset=utf-8');
            }

            if(detemiro::auth()->status) {
                if($token = detemiro::auth()->prepareSession()) {
                    detemiro::messages()->push(array(
                        'title'  => 'Успех!',
                        'code'   => 'auth.prepareSessionOK',
                        'text'   => 'Временная сессия успешно создана на 2 часа.',
                        'type'   => 'auth.result',
                        'status' => 'notice'
                    ));
                }
                else {
                    detemiro::messages()->push(array(
                        'title'  => 'Провал!',
                        'code'   => 'auth.prepareSessionFail',
                        'text'   => 'Не удалось создать временную сессию.',
                        'type'   => 'auth.result',
                        'status' => 'error'
                    ));
                }

                echo \detemiro\space\authSystem::formResult($token);
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