<?php
    detemiro::actions()->add(array(
        'priority' => 0,
        'function' => function() {
            if($form = detemiro::registry()->get('page.authentication')) {
                $form->set(array(
                    'name'    => 'identifier',
                    'title'   => 'Почта',
                    'desc'    => 'Введите ваш электронный адрес',
                    'type'    => 'mail',
                    'require' => true
                ));
                $form->set(array(
                    'name'    => 'password',
                    'title'   => 'Пароль',
                    'desc'    => 'Введите ваш пароль',
                    'type'    => 'password',
                    'require' => true
                ));

                detemiro::registry()->set('page.authentication', $form);
            }
        }
    ));
?>