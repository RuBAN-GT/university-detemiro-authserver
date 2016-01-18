<?php
    namespace detemiro\space;

    /**
     * Класс-контролер для аутентификации и выдачи токенов
     *
     * @package detemiro\space
     */
    class authSystem extends \detemiro\magicControl {
        protected $__ignoreGET = array('cache', 'db');

        /**
         * Объект кеша
         *
         * @var \detemiro\modules\abstractClassCache\cacheActions
         */
        protected $cache;

        /**
         * Объект БД
         *
         * @var \detemiro\modules\database\dbActions
         */
        protected $db;

        /**
         * Конфигурация системы
         *
         * @var array $config
         */
        protected $config = array(
            'universal'  => false,
            'tokenLive'  => 86400,
            'cookieLive' => 86400,
            'tmpName'    => 'auth'
        );

        /**
         * Запрос клиента
         *
         * @var array
         */
        protected $query = array(
            'service'    => '',
            'token'      => '',
            'secret'     => '',
            'redirect'   => '',
            'ip'         => ''
        );

        /**
         * Данные сервиса
         *
         * @var array $service
         */
        protected $service = array(
            'id'        => null,
            'code'      => null,
            'secret'    => null,
            'redirects' => array()
        );

        /**
         * Время жизни токена
         *
         * @var int
         */
        protected $tokenLive = 86400;

        /**
         * Время жизни кук
         * 
         * @var int
         */
        protected $cookieLive = 86400;

        /**
         * Универсальная аутентификация (общей кеш, общая кука)
         *
         * @var bool
         */
        protected $universal = false;

        /**
         * Статус успешности проверки клиента
         *
         * * true  - клиент успешно проверен
         * * null  - клиент найден, но сессия обслуживания устарела
         * * false - клиент чётко не определён
         *
         * @var bool|null $status
         */
        protected $status = false;

        /**
         * Зависимости
         *
         * @var \detemiro\modules\relations $rels
         */
        protected $rels;

        /**
         * Конструктор
         *
         * @param \detemiro\modules\database\dbActions              $db
         * @param \detemiro\modules\abstractClassCache\cacheActions $cache
         */
        public function __construct(
            \detemiro\modules\database\dbActions $db,
            \detemiro\modules\abstractClassCache\cacheActions $cache
        )
        {
            $this->db    = $db;
            $this->cache = $cache;
        }

        /**
         * Проверка запроса по сервису
         *
         * @zones auth.checkQuery
         *
         * @return bool
         */
        protected function checkQuery() {
            return (
                $this->query['service'] &&
                $this->service['code'] === $this->query['service'] &&
                $this->service['secret'] === $this->query['secret'] &&
                $this->query['redirect'] &&
                in_array($this->query['redirect'], $this->service['redirects']) &&
                (\detemiro::actions()->makeCheckZone('auth.checkQuery', $this->query, $this->service) !== false)
            );
        }

        /**
         * Получение данных о сервисе в поле $service
         *
         * @param  string $code
         *
         * @return bool
         */
        protected function initService($code) {
            if($code && is_string($code)) {
                if($service = $this->db->select(array(
                    'table'  => 'services',
                    'cols'   => 'id,code,secret',
                    'oneRow' => 0,
                    'cond'   => array(
                        'param' => 'code',
                        'value' => $code
                    )
                ))
                ) {
                    if($redirects = $this->db->select(array(
                        'table'  => 'redirects',
                        'cols'   => 'address',
                        'oneCol' => 0,
                        'cond'   => array(
                            'param' => 'service_id',
                            'value' => $service['id']
                        )
                    ))) {
                        $service['redirects'] = $redirects;

                        $this->service = $service;

                        $this->initConfig();

                        if($this->initModules()) {
                            return true;
                        }
                        else {
                            $this->status = null;

                            $errors = array();
                            foreach(\detemiro::modules()->relations()->dumpType('authSystem') as $item) {
                                if($item->status === false) {
                                    $errors[] = "{$item->method}: {$item->name}";
                                }
                            }

                            \detemiro::messages()->push(array(
                                'status' => 'error',
                                'type'   => 'auth.result',
                                'title'  => 'Произошла ошибка',
                                'code'   => 'auth.unknownError',
                                'text'   => 'Произошла неизвестная ошибка, обратитесь к администратору.'
                            ));
                            \detemiro::messages()->push(array(
                                'status' => 'error',
                                'type'   => 'auth',
                                'title'  => 'Ошибка модулей',
                                'text'   => 'Произошла ошибка запуска модулей: ' . implode(', ', $errors) . '.'
                            ));
                        }
                    }
                }
            }

            return false;
        }

        /**
         * Активация и проверка модулей
         *
         * @return bool
         */
        protected function initModules() {
            if($list = $this->db->select(array(
                'table' => 'modules',
                'cols'  => 'name,method',
                'cond'  => array(
                    'param' => 'service_id',
                    'value' => $this->service['id']
                )
            )))
            {
                \detemiro::modules()->relations()->pack($list, 'authSystem');

                return \detemiro::modules()->relations()->check('authSystem');
            }
            else {
                return true;
            }
        }

        /**
         * Установка конфигурации сервиса
         *
         * @return void
         */
        protected function initConfig() {
            /**
             * Поиск конфигурации в БД
             */
            if($config = $this->db->select(array(
                'table' => 'config',
                'cols'  => 'param,value',
                'cond'  => array(
                    'param' => 'service_id',
                    'value' => $this->service['id']
                )
            ))) {
                foreach($config as $item) {
                    if($try = \detemiro\json_decode_struct($item['value'])) {
                        $item['value'] = $try;
                    }

                    if($try = \detemiro::config()->get($item['param'])) {
                        if(is_array($try) && is_array($item['value'])) {
                            $item['value'] = array_replace_recursive($try, $item['value']);
                        }
                    }

                    \detemiro::config()->set($item['param'], $item['value']);
                }
            }

            /**
             * Интерпритация значений конфигурации
             */
            if($cfg = \detemiro::config()->getByPrefix('auth.', true)) {
                $this->config = array_replace($this->config, $cfg);
            }

            if(is_numeric($this->config['tokenLive']) && $this->config['tokenLive'] >= 600) {
                $this->tokenLive = (int) $this->config['tokenLive'];
            }

            if(is_numeric($this->config['cookieLive']) && $this->config['cookieLive'] >= 0) {
                $this->cookieLive = (int) $this->config['cookieLive'];
            }

            $this->universal = (bool) $this->config['universal'];

            $this->tmpName = ($this->config['tmpName'] && is_string($this->config['tmpName'])) ? $this->config['tmpName'] : 'auth';
        }

        /**
         * Подготовка временной сессии для сервиса
         *
         * @return string|null Временный токен для пользователя
         */
        public function prepareSession() {
            $token = \detemiro\random_hash(20, false);

            if($this->cache->set($token, \detemiro\json_val_encode($this->query), 7200)) {
                return $token;
            }
            else {
                return null;
            }
        }

        /**
         * Метод инициализации системы аутентификации
         *
         * Инициализация заключаются в парсинге клиентского запроса, а также его валидация.
         *
         * @return bool
         */
        public function init() {
            if(isset($_GET['sessionToken'])) {
                if($this->initSessionTry($_GET['sessionToken'])) {
                    $this->status = true;
                }
            }
            elseif(
                file_get_contents('php://input') &&
                $this->initBasic(file_get_contents('php://input'))
            )
            {
                $this->status = true;
            }

            return $this->status;
        }

        /**
         * Метод определения клиента по входящему запросу
         *
         * @param  string $data
         *
         * @return bool
         */
        protected function initBasic($data) {
            if(is_string($data) && $data) {
                if($copy = \detemiro\json_decode_struct($data)) {
                    $data = $copy;
                }
                else {
                    parse_str($data, $data);
                }

                if($data && is_array($data) && isset($data['service'], $data['redirect'])) {
                    $this->status = null;

                    $this->query = array_replace($this->query, $data);

                    $this->query['ip'] = self::clientIP();

                    /**
                     * Получение данных о сервисе и проверяю данные
                     */
                    if(
                        $this->initService($this->query['service']) &&
                        $this->checkQuery()
                    )
                    {
                        $this->status = true;
                    }

                    if($this->status == false) {
                        \detemiro::messages()->push(array(
                            'status' => 'error',
                            'type'   => 'auth.result',
                            'title'  => 'Неверный запрос клиента',
                            'code'   => 'auth.wrongRequest',
                            'text'   => 'Клиент отправил неверный запрос, или сервис клиента не определён.'
                        ));
                        \detemiro::messages()->push(array(
                            'status' => 'error',
                            'type'   => 'auth',
                            'title'  => 'Неверный запрос клиента',
                            'text'   => 'Клиент отправил запрос с неверными параметрами: ' . \detemiro\json_val_encode($this->query) . '.'
                        ));
                    }
                }
            }

            return $this->status;
        }

        /**
         * Определение клиента по временной сессии
         *
         * @param string $data Временный токен
         *
         * @return bool
         */
        protected function initSessionTry($data) {
            if(is_string($data) && $data) {
                /**
                 * Проверяю данные кеша
                 */
                if($try = $this->cache->get($data)) {
                    $try = \detemiro\json_decode_struct($try);

                    if(isset($try['service']) && is_string($try['service']) && $try['service']) {
                        $this->query = array_replace($this->query, $try);

                        if(
                            $this->initService($this->query['service']) &&
                            $this->checkQuery()
                        )
                        {
                            $this->status = true;
                        }
                        else {
                            $this->status = null;
                        }
                    }
                }
                elseif(isset($_POST['form-service'])) {
                    $this->query['service'] = $_POST['form-service'];

                    if($this->initService($this->query['service'])) {
                        /**
                         * Попытка определить редирект
                         */
                        if(isset($_POST['form-redirect']) && is_string($_POST['form-redirect']) && $_POST['form-redirect']) {
                            if(in_array($_POST['form-redirect'], $this->service['redirects'])) {
                                $this->query['redirect'] = $_POST['form-redirect'];
                            }
                        }
                        if($this->query['redirect'] == null && isset($_SERVER['HTTP_REFERER']) && is_string($_SERVER['HTTP_REFERER'])) {
                            if(in_array($_SERVER['HTTP_REFERER'], $this->service['redirects'])) {
                                $this->query['redirect'] = $_SERVER['HTTP_REFERER'];
                            }
                        }

                        if($this->query['redirect']) {
                            $this->status = null;
                        }
                    }
                    else {
                        $this->status = false;
                    }
                }
            }

            return $this->status;
        }

        /**
         * Обратный редирект с GET-параметрами
         *
         * @zones auth.before.backRedirect
         *
         * @param  array $data GET-параметры
         *
         * @return bool
         */
        public function backRedirect(array $data = null) {
            if($this->status) {
                $url = $this->query['redirect'];

                if($data) {
                    if($get = http_build_query($data, '', '&')) {
                        if(parse_url($this->query['redirect'], PHP_URL_QUERY)) {
                            $url .= "&$get";
                        }
                        else {
                            $url .= "?$get";
                        }
                    }
                }

                \detemiro::actions()->makeZone('auth.before.backRedirect');

                \detemiro::router()->redirect($url);

                return true;
            }
            else {
                return false;
            }
        }

        /**
         * Проверка пользовательских данных
         *
         * Данный метод осуществляет проверку введённых пользователем данных с помощью экшенов, которые должны возвращать логин пользователя.
         * В случае успеха осуществляет генерацию токена.
         *
         * Примечание: $data должен содержать ключ 'identifier', в котором должно содержаться определяющее имя пользователя
         *
         * @zones auth.checkAuth, auth.checkAuth.success, auth.checkAuth.fail
         *
         * @param array $data
         *
         * @return string|bool
         */
        public function checkAuth(array $data) {
            if($this->status && isset($data['identifier']) && (is_string($data['identifier']) || is_numeric($data['identifier']))) {
                if(call_user_func_array(array(\detemiro::actions(), 'makeCheckZone'), array_merge(array('auth.checkAuth'), func_get_args()))) {
                    if($token = $this->generateToken($data['identifier'])) {
                        \detemiro::actions()->makeZone('auth.checkAuth.success', $data['identifier'], $token);

                        return $token;
                    }
                }
                else {
                    \detemiro::actions()->makeZone('auth.checkAuth.fail', $data['identifier']);
                }
            }

            return false;
        }

        /**
         * Генерация имени таблицы в кеше по сервису
         *
         * @param  string      $table
         *
         * @return string|null
         */
        public function getCacheName($table) {
            if($this->status && is_string($table) && $table) {
                $name = '';

                if($this->universal == false) {
                    $name .= $this->service['code'];
                }

                $name .= $table;

                return $name;
            }
            else {
                return null;
            }
        }

        /**
         * Установка данных в кеш
         *
         * @param string $table  Имя ячейки, например, логин пользователя
         * @param array  $data
         * @param int    $ttl    Время жизни кеша
         *
         * @return bool
         */
        public function setData($table, $data, $ttl = null) {
            if(is_numeric($ttl) == false || $ttl < 60) {
                $ttl = $this->tokenLive;
            }

            if($table = $this->getCacheName($table)) {
                if(is_array($data) || is_object($data)) {
                    $data = \detemiro\json_val_encode($data);
                }

                if($data) {
                    return $this->cache->set($table, $data, $ttl);
                }
            }

            return false;
        }

        /**
         * Получение данных из кеша
         *
         * @param string $table
         *
         * @return mixed
         */
        public function getData($table) {
            if($table = $this->getCacheName($table)) {
                return $this->cache->get($table);
            }
            else {
                return false;
            }
        }

        /**
         * Удаление данных из кеша
         *
         * @param string $table
         *
         * @return bool
         */
        public function removeData($table) {
            if($table = $this->getCacheName($table)) {
                return $this->cache->delete($table);
            }
            else {
                return false;
            }
        }

        /**
         * Проверка токена
         *
         * Данный метод проверяет токен пользователя и в случае успеха возвращает его логин.
         * Если токен просрочен, он будет удалён, а результатом будет являться null, противном случае - false.
         *
         * @zones auth.checkToken.expired
         *
         * @param $token
         *
         * @return string|bool
         */
        protected function checkToken($token) {
            if(is_string($token) && $token) {
                if($res = $this->cache->get(md5($this->service['id'] . $token))) {
                    return $res;
                }
            }

            return false;
        }

        /**
         * Генерация токена
         *
         * Данный метод генерирует токен и связывает его с пользователем $identifier и сервисом, указанным в запросе клиента.
         * В случае успешного добавления токена в БД, возвращает этот самый токен (хеш из латиницы и цифр).
         *
         * @param  string|int $identifier
         *
         * @return string|null
         */
        protected function generateToken($identifier) {
            $key = md5($this->service['id'] . $identifier);
            if($hash = $this->cache->get($key)) {
                return $hash;
            }
            else {
                $hash = \detemiro\random_hash(25, false);
                $ext  = md5($this->service['id'] . $hash);

                if($this->cache->set($key, $hash, $this->tokenLive) && $this->cache->set($ext, $identifier, $this->tokenLive)) {
                    return $hash;
                }
                else {
                    $this->cache->delete($key);
                    $this->cache->delete($ext);

                    return null;
                }
            }
        }

        /**
         * Получение данных пользователя по токену
         *
         * Данный метод получает данные по токену, указанному в аргументе, в противном случае подставляет из запроса клиента.
         *
         * @zones auth.getByToken
         *
         * @param string $token
         *
         * @return array|null|false
         */
        public function getByToken($token = null) {
            if($token == null) {
                $token = $this->query['token'];
            }

            if($token) {
                if($identifier = $this->checkToken($token)) {
                    if($actions = \detemiro::actions()->getZone('auth.getByToken')) {
                        $res = array();

                        foreach($actions as $key=>$action) {
                            $item = $action->make($identifier, $res);

                            if($try = \detemiro\json_decode_struct($item)) {
                                $item = $try;
                            }

                            if(is_array($item)) {
                                $res = array_replace($res, $item);
                            }
                            else {
                                $res["more.{$key}"] = $item;
                            }
                        }

                        ksort($res, SORT_NATURAL | SORT_FLAG_CASE);

                        return $res;
                    }
                    else {
                        return null;
                    }
                }
            }

            return false;
        }

        /**
         * Проверка куки
         *
         * Данный метод осуществляет проверки куки, в случае успеха возвращаёт её значение, при провале - удаляет её.
         *
         * @zones auth.checkCookie
         *
         * @return string|false
         */
        public function checkCookie() {
            if($this->status && $this->cookieLive) {
                $name = $this->tmpName;
                if($this->universal == false) {
                    $name .= '-' . md5($this->service['code']);
                }

                if(isset($_COOKIE[$name])) {
                    if($identifier = $this->checkToken($_COOKIE[$name])) {
                        if(\detemiro::actions()->makeCheckZone('auth.checkCookie', $identifier) !== false) {
                            return $_COOKIE[$name];
                        }
                    }

                    \detemiro\destroy_cookie($name);
                }
            }

            return false;
        }

        /**
         * Полный выход пользователя
         *
         * @zones auth.logout
         *
         * @param string $token Токен пользователя
         * 
         * @return bool
         */
        public function logout($token = null) {
            if($token == null) {
                $token = $this->query['token'];
            }

            if($this->status) {
                /**
                 * Вызор обработчика, чтобы модули могли почистить свой кеш
                 */
                if($identifier = $this->checkToken($token)) {
                    if(\detemiro::actions()->makeCheckZone('auth.logout', $identifier) !== false) {
                        /**
                         * Удаление общего кеша с токенами
                         */
                        $this->cache->delete(md5($this->service['id'] . $identifier));
                        $this->cache->delete(md5($this->service['id'] . $token));

                        return true;
                    }
                }
            }
            
            return false;
        }

        /**
         * Генерация куки с необходимым именем
         *
         * @param string $content Контент куки (токен)
         *
         * @return bool|null
         */
        public function createCookie($content) {
            if($this->status && is_string($content) && $content) {
                if($this->cookieLive) {
                    $name = $this->tmpName;
                    if($this->universal == false) {
                        $name .= '-' . md5($this->service['code']);
                    }

                    $_COOKIE[$name] = $content;

                    return setcookie($name, $content, strtotime(date('c')) + $this->cookieLive, '/', '', true);
                }
                else {
                    return null;
                }
            }

            return false;
        }

        /**
         * Получение правдоподобного IP клиента
         * 
         * @return string|null
         */
        public static function clientIP() {
            if(isset($_SERVER['X-Forwarded-For'])) {
                return $_SERVER['X-Forwarded-For'];
            }
            elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            elseif(isset($_SERVER['REMOTE_ADDR'])) {
                return $_SERVER['REMOTE_ADDR'];
            }
            else {
                return null;
            }
        }

        /**
         * Получение сообщений из коллектора сообщений в массиве
         *
         * @param $type
         * @param $status
         *
         * @return array
         */
        protected static function formMessages($type, $status) {
            $res = array();

            if($messages = \detemiro::messages()->getType($type, $status)) {
                foreach($messages as $message) {
                    $res[] = array(
                        'time'    => $message->date,
                        'comment' => $message->text,
                        'info'    => $message->title,
                        'code'    => (($message->code) ? $message->code : '')
                    );
                }
            }

            return $res;
        }

        /**
         * Формирование результата в виде JSON-строка для последующего парсина клиента
         *
         * *Если $data невозможно закодировать, то аргументу присваивается null.*
         *
         * @param mixed $data
         *
         * @return string
         */
        public static function formResult($data = null) {
            try {
                json_encode($data, JSON_UNESCAPED_UNICODE);
            }
            catch(\Exception $e) {
                $data = null;
            }

            $result = array(
                'data'    => $data,
                'notices' => array(),
                'errors'  => array()
            );

            $result['errors']  = self::formMessages('auth.result', 'error');
            $result['notices'] = self::formMessages('auth.result', 'notice');

            echo \detemiro\json_val_encode($result);

            exit();
        }
    }
?>