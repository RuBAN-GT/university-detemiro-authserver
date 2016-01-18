<?php
    detemiro::actions()->add(array(
        'code'     => 'auth.checkCookie',
        'priority' => false,
        'function' => function() {
            if(detemiro::auth()->status && $_GET['sessionToken']) {
                if($token = detemiro::auth()->checkCookie()) {
                    detemiro::auth()->backRedirect(array(
                        'authToken' => $token
                    ));
                }
            }
            elseif(detemiro::auth()->status === null) {
                detemiro::auth()->backRedirect(array(
                    'authToken' => null
                ));
            }
            else {
                detemiro::router()->redirectOnPage('404');
            }
        }
    ));

    detemiro::actions()->add(array(
        'code'     => 'auth.prepareForm',
        'priority' => false,
        'function' => function() {
            $form = new \detemiro\modules\forms\form();

            detemiro::registry()->set('page.authentication', $form);
        }
    ));

    detemiro::actions()->add(array(
        'code'     => 'auth.preparePOST',
        'priority' => true,
        'function' => function() {
            if($form = detemiro::registry()->get('page.authentication')) {
                if($_POST && $form->fillIn($_POST)) {
                    if($form->validateAll()) {
                        if($token = detemiro::auth()->checkAuth($form->data(), $form)) {
                            if(detemiro::auth()->createCookie($token) !== false) {
                                detemiro::auth()->backRedirect(array(
                                    'authToken' => $token
                                ));
                            }
                            else {
                                detemiro::messages()->push(array(
                                    'title'  => 'Неудача!',
                                    'text'   => 'Включите поддержку cookie в вашем браузере.',
                                    'status' => 'error',
                                    'type'   => 'auth.public'
                                ));
                            }
                        }
                    }
                    else {
                        detemiro::messages()->push(array(
                            'title'  => 'Неудача!',
                            'text'   => 'Проверьте правильность заполненных полей.',
                            'status' => 'error',
                            'type'   => 'auth.public'
                        ));
                    }
                }
            }
        }
    ));
?>