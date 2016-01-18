<?php
    detemiro::actions()->add(array(
        'priority' => 10,
        'code'     => 'auth.checkRadius',
        'function' => function($data) {
            if(isset($data['identifier'], $data['password']) && $data['identifier'] && $data['password']) {
                /**
                 * Шаблон конфига
                 */
                $custom = array(
                    'host'     => 'localhost',
                    'port'     => 1813,
                    'secret'   => '',
                    'nasHost'  => '',
                    'service'  => '',
                    'map'      => array(),
                    'fields'   => ''
                );

                if($cfg = detemiro::config()->getByPrefix('radius.', true)) {
                    $custom = array_replace_recursive($custom, $cfg);
                }

                /**
                 * Проверка наличия конфига
                 */
                if(
                    function_exists('radius_auth_open') &&
                    is_string($custom['host']) &&
                    $custom['host'] &&
                    is_numeric($custom['port']) &&
                    $custom['port'] &&
                    is_string($custom['secret']) &&
                    $custom['secret']
                ) {
                    $radius = radius_auth_open();

                    if(radius_add_server($radius, $custom['host'], $custom['port'], $custom['secret'], 5, 3)) {
                        /**
                         * Подключение
                         */
                        radius_create_request($radius, RADIUS_ACCESS_REQUEST);

                        /**
                         * Подстановка атрибутов
                         */
                        radius_put_attr($radius, RADIUS_USER_NAME, $data['identifier']);
                        radius_put_attr($radius, RADIUS_USER_PASSWORD, $data['password']);
                        radius_put_attr($radius, RADIUS_SERVICE_TYPE, RADIUS_AUTHENTICATE_ONLY);

                        /**
                         * Определяю NAS Address
                         */
                        $nas = null;
                        if($custom['nasHost'] != 'auto' && is_string($custom['nasHost']) && $custom['nasHost']) {
                            $nas = $custom['nasHost'];
                        }
                        elseif(detemiro::auth()->query['ip']) {
                            $nas = detemiro::auth()->query['ip'];
                        }

                        if($nas) {
                            if(filter_var($nas, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                                radius_put_addr($radius, RADIUS_NAS_IP_ADDRESS, $nas);
                                radius_put_attr($radius, RADIUS_NAS_PORT_TYPE, RADIUS_VIRTUAL);
                            }
                            elseif(filter_var($nas, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                                radius_put_attr($radius, RAD_NAS_IPV6_ADDRESS, inet_pton($nas));
                                radius_put_attr($radius, RADIUS_NAS_PORT_TYPE, RADIUS_VIRTUAL);
                            }
                        }

                        if($custom['service']) {
                            $service = $custom['service'];
                        }
                        else {
                            $service = detemiro::auth()->service['code'];
                        }

                        radius_put_attr($radius, 32, $service);

                        $answer = radius_send_request($radius);

                        /**
                         * В случае успеха сбор данных
                         */
                        if($answer == RADIUS_ACCESS_ACCEPT) {
                            $res = array('email' => $data['identifier']);

                            while($item = radius_get_attr($radius)) {
                                if(is_array($item)) {
                                    if($item['attr'] == RADIUS_VENDOR_SPECIFIC) {
                                        $spec = radius_get_vendor_attr($item['data']);

                                        if(is_array($spec)) {
                                            $res["{$spec['vendor']}.{$spec['attr']}"] = $spec['data'];
                                        }
                                    }
                                    elseif(
                                        $item['attr'] == RADIUS_FRAMED_MTU || 
                                        $item['attr'] == RADIUS_FRAMED_ROUTING || 
                                        $item['attr'] == RADIUS_FRAMED_COMPRESSION || 
                                        $item['attr'] == RADIUS_FRAMED_PROTOCOL || 
                                        $item['attr'] == RADIUS_SERVICE_TYPE
                                    ) 
                                    {
                                        $res[$item['attr']] = radius_cvt_int($item['data']);
                                    }
                                    else {
                                        $res[$item['attr']] = $item['data'];
                                    }
                                }
                            }

                            if($res) {
                                if($custom['map'] == null || is_array($custom['map']) == false) {
                                    $custom['map'] = array();
                                }
                                $mapped = array();

                                if(is_string($custom['fields'])) {
                                    $custom['fields'] = ($custom['fields']) ? explode(',', $custom['fields']) : array();
                                }

                                foreach($res as $key=>$value) {
                                    /**
                                     * Маппинг
                                     */
                                    if(
                                       isset($custom['map'][$key]) && 
                                       is_string($custom['map'][$key]) && 
                                       $custom['map'][$key]
                                    ) 
                                    {
                                        $key = $custom['map'][$key];
                                    }

                                    /**
                                     * Фильтрация
                                     */
                                    if($custom['fields'] == null || in_array($key, $custom['fields'])) {
                                        $mapped[$key] = $value;
                                    }
                                }

                                $res = $mapped;

                                ksort($res, SORT_NATURAL | SORT_FLAG_CASE);

                                return detemiro::auth()->setData("radius.{$data['identifier']}", $res);
                            }

                            return true;
                        }
                        elseif($answer == RADIUS_ACCESS_REJECT) {
                            detemiro::messages()->push(array(
                                'title'  => 'Неудача!',
                                'text'   => 'Вы ввели неверный логин или пароль.',
                                'status' => 'error',
                                'type'   => 'auth.public'
                            ));
                        }
                    }
                }
            }

            return false;
        }
    ));
?>