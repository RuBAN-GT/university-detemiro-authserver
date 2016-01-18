<?php
    detemiro::actions()->add(array(
        'function' => function() {
            if($urls = \detemiro::db()->select(array(
                'table'  => 'urls',
                'cols'   => 'address',
                'oneCol' => 0,
                'cond'   => array(
                    'param' => 'service_id',
                    'value' => detemiro::auth()->service['id']
                )
            ))) {
                return (isset(detemiro::auth()->query['url']) && in_array(detemiro::auth()->query['url'] ,$urls));
            }
            else {
                return true;
            }
        }
    ));
?>