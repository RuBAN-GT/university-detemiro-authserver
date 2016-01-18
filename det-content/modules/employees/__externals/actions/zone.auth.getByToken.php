<?php
    detemiro::actions()->add(array(
        'priority' => 50,
        'function' => function($main, $data) {
            $try = detemiro::auth()->getData("employee.$main");

            /**
             * Подбор данных из кеша
             */
            if($try !== false) {
                return $try;
            }
            else {
                $res = array('roleOccupant' => array());

                if(isset($data['employeeNumber']) && is_string($data['employeeNumber'])) {
                    $ids = explode(', ', $data['employeeNumber']);

                    /**
                     * Настрока оракла
                     */
                    $config = array(
                        'host'       => '',
                        'user'       => '',
                        'pass'       => '',
                        'home'       => '',
                        'lib'        => '',
                        'tns'        => '',
                        'lang'       => '',
                        'mode'       => '',
                        'database.s' => '',
                        'database.e' => '',
                        'fields.s'   => '',
                        'fields.e'   => '',
                        'map'        => array()
                    );

                    if($cfg = \detemiro::config()->getByPrefix('employees.', true)) {
                        $config = array_replace($config, $cfg);
                    }

                    /**
                     * Тех, кто адаптировал оракл для PHP, ждём отдельный котёл
                     */
                    $_ENV['ORACLE_HOME']     = $config['home'];
                    $_ENV['TNS_ADMIN']       = $config['tns'];
                    $_ENV['LD_LIBRARY_PATH'] = $config['lib'];
                    $_ENV['NLS_LANG']        = $config['lang'];

                    putenv("ORACLE_HOME={$_ENV['ORACLE_HOME']}");
                    putenv("TNS_ADMIN={$_ENV['TNS_ADMIN']}");
                    putenv("LD_LIBRARY_PATH={$_ENV['LD_LIBRARY_PATH']}");
                    putenv("NLS_LANG={$_ENV['NLS_LANG']}");

                    /**
                     * Просмотр сотрудников
                     */
                    if($config['mode'] == 'all' || $config['mode'] == 'e') {
                        if(is_string($config['fields.e'])) {
                            $config['fields.e'] = ($config['fields.e']) ? explode(',', $config['fields.e']) : array();
                        }

                        if(strpos($data['employeeNumber'], 'e#') !== false) {
                            $employee = oci_connect($config['user'], $config['pass'], "{$config['host']}/{$config['database.e']}");

                            if($employee) {
                                foreach($ids as $id) {
                                    if(strpos($id, 'e#') !== false) {
                                        $id = substr($id, 2);

                                        $stid = oci_parse($employee, "SELECT * FROM uic_pers WHERE ID=$id");
                                        if(oci_execute($stid)) {
                                            while($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
                                                $row['employeeType'] = 'employee';

                                                $res['roleOccupant'][] = $row;
                                            }
                                        }
                                    }
                                }
                            }

                            oci_close($employee);
                        }
                    }
                    /**
                     * Просмотр студентов
                     */
                    if($config['mode'] == 'all' || $config['mode'] == 's') {
                        if(is_string($config['fields.s'])) {
                            $config['fields.s'] = ($config['fields.s']) ? explode(',', $config['fields.s']) : array();
                        }

                        if(strpos($data['employeeNumber'], 's#') !== false) {
                            $student = oci_connect($config['user'], $config['pass'], "{$config['host']}/{$config['database.s']}");

                            if($student) {
                                foreach($ids as $id) {
                                    if(strpos($id, 's#') !== false) {
                                        $id = substr($id, 2);

                                        $stid = oci_parse($student, "SELECT * FROM uic_pers WHERE ID=$id");
                                        if(oci_execute($stid)) {
                                            while($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
                                                $row['employeeType'] = 'student';

                                                $res['roleOccupant'][] = $row;
                                            }
                                        }
                                    }
                                }
                            }

                            oci_close($student);
                        }
                    }
                }

                /**
                 * Маппинг и фильтрация
                 */
                if($config['map'] == null || is_array($config['map']) == false) {
                    $config['map'] = array();
                }
                $mapped = array();

                foreach($res['roleOccupant'] as $i=>$part) {
                    $tmp  = array();
                    $type = ($part['employeeType'] == 'student') ? 's' : 'e';

                    foreach($part as $key=>$value) {
                        /**
                         * Маппинг
                         */
                        if(
                           isset($config['map'][$key]) && 
                           is_string($config['map'][$key]) && 
                           $config['map'][$key]
                        ) 
                        {
                            $key = $config['map'][$key];
                        }

                        /**
                         * Фильтрация
                         */
                        if(
                           $type == 's' && 
                           ($config['fields.s'] == null || in_array($key, $config['fields.s']))
                           ||
                           $type == 'e' &&
                           ($config['fields.e'] == null || in_array($key, $config['fields.e']))
                        ) {
                            $tmp[$key] = $value;
                        }
                    }

                    if($tmp) {
                        $tmp['employeeType'] = $part['employeeType'];

                        ksort($tmp, SORT_NATURAL | SORT_FLAG_CASE);

                        $mapped[] = $tmp;
                    }
                }

                $res['roleOccupant'] = $mapped;

                detemiro::auth()->setData("employee.$main", $res);

                return $res;
            }
        }
    ));
?>