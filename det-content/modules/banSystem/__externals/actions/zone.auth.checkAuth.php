<?php
    detemiro::actions()->add(array(
        'priority' => false,
        'function' => function($id) {
            $t = true;

            if(detemiro::authBan()->ipTry && detemiro::authBan()->checkBan()) {
                $t = false;
            }

            if($t && detemiro::authBan()->idTry && detemiro::authBan()->checkBan($id['identifier'])) {
                $t = false;

                if($time = detemiro::authBan()->getBanTime($id['identifier'])) {
                    $time  += (detemiro::authBan()->idBanTTL - date('U'));
                }
                else {
                    $time = detemiro::authBan()->idBanTTL;
                }
                $time = round($time/60, 2);

                detemiro::messages()->push(array(
                    'type'   => 'auth.public',
                    'status' => 'error',
                    'title'  => 'Вы заблокированы!',
                    'text'   => 'Вы заблокированы по логину и временно не можете проходить аутентификацию из-за странной активности вашего аккаунта в течение ' . $time . ' мин.'
                ));
            }

            return $t;
        }
    ));
?>