<?php
    /**
     * Проверка объекта для работы с кешем, БД, а также создание сервиса auth.
     *
     * @see \detemiro\space\authSystem;
     */
    detemiro::actions()->add(array(
        'code'     => 'authSystem.init',
        'function' => function() {
            $cache = detemiro::cache();
            $db    = detemiro::db();

            if($cache) {
                if($db) {
                    try {
                        return detemiro::services()->set('auth', new detemiro\space\authSystem($db, $cache));
                    }
                    catch(\Exception $error) {
                        detemiro::messages()->push(array(
                            'title'  => 'Ошибка!',
                            'type'   => 'auth',
                            'status' => 'error',
                            'text'   => $error->getMessage()
                        ));

                        return false;
                    }
                }
                else {
                    detemiro::messages()->push(array(
                        'title'  => 'Ошибка!',
                        'type'   => 'auth',
                        'status' => 'error',
                        'text'   => 'По каким-то причинам не существует объекта для работы с БД.'
                    ));

                    return false;
                }
            }
            else {
                detemiro::messages()->push(array(
                    'title'  => 'Ошибка!',
                    'type'   => 'auth',
                    'status' => 'error',
                    'text'   => 'По каким-то причинам не существует объекта для работы с кешем.'
                ));

                return false;
            }
        }
    ));
?>