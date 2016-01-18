<?php
    /**
     * Контроллер выбора страниц.
     * Загружается нужная страница при правильной клиенте или поле allStatus == true.
     */
    detemiro::actions()->add(array(
        'function' => function() {
            $tmp = detemiro::pages()->get(detemiro::router()->page);

            if($tmp) {
                $page = $tmp;
            }
            elseif(($tmp = detemiro::pages()->get('404')) && $tmp->function) {
                $page = $tmp;
            }
            else {
                $page = null;
            }

            if($page) {
                $page->show();
            }
        }
    ));
?>