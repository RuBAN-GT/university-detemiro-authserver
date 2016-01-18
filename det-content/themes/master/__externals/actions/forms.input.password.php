<?php
    detemiro::actions()->add(array(
        'function' => function($item) {
            if($item->valide === false) {
                $class = ' has-error';
            }
            else {
                $class = '';
            }

            detemiro::theme()->incFile('__templates/forms/password.php', array(
                'title'   => (($item->title) ? $item->title : 'Пароль'),
                'place'   => (($item->desc)  ? $item->desc  : '****'),
                'value'   => '',
                'name'    => $item->name,
                'class'   => $class,
                'require' => $item->require,
                'ignore'  => (bool) $item->ignore
            ));
        }
    ));
?>