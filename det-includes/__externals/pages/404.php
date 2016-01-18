<?php
    detemiro::pages()->set(array(
        'title'    => 'Страница не найдена',
        'function' => function() {
            header("{$_SERVER["SERVER_PROTOCOL"]} 404 Not Found");

            if(detemiro::theme()->incFile('404.php') == false) {
                echo '404. Page not found.';
            }
        }
    ));
?>