<?php
    detemiro::actions()->add(array(
        'function' => function($id) {
            if(detemiro::authBan()->ipTry) {
                if($ban = detemiro::authBan()->makeBan()) {
                    if($time = detemiro::authBan()->getBanTime()) {
                        $time  += (detemiro::authBan()->ipBanTTL - date('U'));
                    }
                    else {
                        $time = detemiro::authBan()->ipBanTTL;
                    }
                    $time = round($time/60, 2);

                    detemiro::messages()->push(array(
                        'type'   => 'auth.public',
                        'status' => 'error',
                        'title'  => 'Вы заблокированы!',
                        'text'   => "Вы заблокированы и теперь временно не можете проходить аутентификацию в течение $time мин."
                    ));
                    detemiro::messages()->push(array(
                        'type'   => 'auth',
                        'status' => 'error',
                        'title'  => 'Пользователь заблокирован!',
                        'text'   => 'Пользователь был успешно заблокирован по IP [' . detemiro::authBan()->userIP . '].'
                    ));
                }
                else {
                    detemiro::messages()->push(array(
                        'type'   => 'auth.public',
                        'status' => 'warning',
                        'title'  => 'Число попыток ограничено!',
                        'text'   => 'У вас осталось ' . (detemiro::authBan()->ipTry - detemiro::authBan()->getBanNumber()) . ' попыток входа'
                    ));
                }
            }

            if(detemiro::authBan()->idTry && detemiro::authBan()->makeBan($id)) {
                detemiro::messages()->push(array(
                    'type'   => 'auth',
                    'status' => 'error',
                    'title'  => 'Пользователь заблокирован!',
                    'text'   => "Пользователь с именем $id был успешно заблокирован."
                ));
            }
        }
    ));
?>