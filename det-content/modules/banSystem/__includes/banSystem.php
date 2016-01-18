<?php
    namespace detemiro\modules\banSystem;

    class banSystem extends \detemiro\magicControl {
        protected $config = array(
            'ipTry'    => null,
            'idTry'    => null,
            'ipBanTTL' => null,
            'idBanTTL' => null
        );

        /**
         * Количество попыток входа по IP
         *
         * @var int
         */
        protected $ipTry = 0;

        /**
         * Количество попыток входа по логину
         *
         * @var int
         */
        protected $idTry = 0;

        /**
         * Вреия жизни кеша про IP
         *
         * @var int
         */
        protected $ipBanTTL = 900;

        /**
         * Время жизни кеша по идентификатору
         *
         * @var int
         */
        protected $idBanTTL = 900;

        /**
         * IP-пользователя
         *
         * @var string
         */
        protected $userIP;

        public function __construct() {
            if($cfg = \detemiro::config()->getByPrefix('banSystem.', true)) {
                $this->config = array_replace($this->config, $cfg);
            }

            if(is_numeric($this->config['ipTry']) && $this->config['ipTry'] >= 0) {
                $this->ipTry = $this->config['ipTry'];
            }
            if(is_numeric($this->config['idTry']) && $this->config['idTry'] >= 0) {
                $this->idTry = $this->config['idTry'];
            }
            if(is_numeric($this->config['ipBanTTL']) && $this->config['ipBanTTL'] >= 60) {
                $this->ipBanTTL = $this->config['ipBanTTL'];
            }
            if(is_numeric($this->config['idBanTTL']) && $this->config['idBanTTL'] >= 60) {
                $this->idBanTTL = $this->config['idBanTTL'];
            }

            $this->userIP = \detemiro\space\authSystem::clientIP();
        }

        /**
         * Установка бана
         *
         * Если $id не заполнен, то блокировка идёт по ip.
         *
         * @param string|int $id
         *
         * @return bool
         */
        public function makeBan($id = null) {
            if($table = $this->getTableName($id)) {
                if($id) {
                    $ttl = $this->idBanTTL;
                    $try = $this->idTry;
                }
                else {
                    $ttl = $this->ipBanTTL;
                    $try = $this->ipTry;
                }

                if($try > 0) {
                    $now = (int)\detemiro::auth()->getData($table);

                    if($now) {
                        if($try == 1) {
                            return true;
                        }
                        else {
                            $now++;

                            if($now == $try) {
                                \detemiro::auth()->setData($table, $now + 1, $ttl);
                                \detemiro::auth()->setData("time.{$table}", date('U'));

                                return true;
                            }
                            elseif($now < $try) {
                                \detemiro::auth()->setData($table, $now, $ttl);
                            }
                            else {
                                return true;
                            }
                        }
                    }
                    else {
                        $now = 1;

                        \detemiro::auth()->setData($table, $now, $ttl);

                        return ($try == $now);
                    }
                }
            }

            return false;
        }

        /**
         * Определение числа записей про бан
         *
         * @param string|int $id
         *
         * @return int
         */
        public function getBanNumber($id = null) {
            if($table = $this->getTableName($id)) {
                return ($now = (int) \detemiro::auth()->getData($table)) ? $now : 0;
            }
            else {
                return 0;
            }
        }

        /**
         * Определение время бана, если он был
         *
         * @param string|int $id
         *
         * @return int|null
         */
        public function getBanTime($id = null) {
            if($table = $this->getTableName($id)) {
                return ($time = \detemiro::auth()->getData("time.$table")) ? $time : null;
            }
            else {
                return null;
            }
        }

        /**
         * Проверка бана
         *
         * @param string|int $id
         *
         * @return bool
         */
        public function checkBan($id = null) {
            if($table = $this->getTableName($id)) {
                if($id) {
                    $try = $this->idTry;
                }
                else {
                    $try = $this->ipTry;
                }

                $now = (int)\detemiro::auth()->getData($table);

                return ($now && ($try == 1 || $now > $try));
            }
            else {
                return false;
            }
        }

        /**
         * Очистка бана
         *
         * @param string|int $id
         *
         * @return bool
         */
        public function removeBan($id = null) {
            return ($table = $this->getTableName($id)) ? \detemiro::auth()->removeData($table) : false;
        }

        protected function getTableName($id) {
            if($id) {
                if(is_string($id) || is_numeric($id)) {
                    return 'ban-id.' . md5($id);
                }
                else {
                    return null;
                }
            }
            elseif($this->userIP) {
                return 'ban-ip' . md5($this->userIP);
            }
            else {
                return null;
            }
        }
    }
?>