﻿## Конфигурация ##

Конфигурация представляет собой ассоциативный массив или объект в терминалогии JS (JSON-структуру). 
Основная конфигурация для ВСЕХ сервисов хранится в */det-space/config.json*.

Ключ        | Описание
----------- | ---------------------------------------------------------------------------------------------------------------------------
database    | Параметры БД
.config     | PDO-строка настройки БД
.user       | Пользователь БД
.pass       | Пароль БД
theme       | Тема (шаблон) сервиса
router      | Параметры ЧПУ
.page       | Главная страница
.url        | Основной адрес сервера
.ssl        | Использование HTTPS
.hosts      | Возможные альтернативные хосты
.preferred  | Предпочитаемые схемы формирование ссылок (см. модуль router)
auth        | Параметры сервера аутентификации
.universal  | Пользователь при аутентификации получает данные и куки на ВСЕ сервисы
.tokenLive  | Время жизни токена
.tmpName    | Префикс имени ключей для таблиц кеша и кук
.cookieLive | Время жизни кук
banSystem   | Параметры модуля блокировок
.idTry      | Кол-во разрешённых попыток для входа в пределах логина
.ipTry      | Кол-во разрешённых попыток для входа в пределах IP
.idBanTTL   | Время бана всего логина
.ipBanTTL   | Время бана IP
radius      | Параметры радиус-сервера
.host       | Хост сервера
.port       | Порт
.secret     | Секретный ключ
.map        | Ассоциативный массив подмены ключей (оригинальный ключ => новый ключ).
.fields     | Поля, выдаваемые клиенту. Указываются через запятую (если пустое, то все поля пойдут в результат).
employee    | Параметры выдачи данных по сотруднику
.host       | Хост сервера базы
.user       | Пользователь базы
.pass       | Пароль базы
.home       | Домашний каталог базы Oracle
.lib        | Путь до библиотек базы Oracle
.tns        | Путь TNS
.lang       | NLS Lang
.mode       | Режим выдачи результата: all - студент и сотрудни, s - студент, e - сотрудник
.database.s | Имя БД по студентам
.database.e | Имя БД по сотрудникам
.map        | Ассоциативный массив подмены ключей (оригинальный ключ => новый ключ).
.fields.s   | Поля, выдаваемые по студенту. Указываются через запятую (если пустое, то все поля пойдут в результат).
.fields.e   | Поля, выдаваемые по сотрдунику
rels       | Массив, укаывающий необходимые модули для ВСЕХ сервисов

## Сервисы ##

Каждый тип аутентификации, её уникальные цель - это сервисы.

Параметры сервисов определяются в БД в следующих таблицах...

### Таблица services ###

В данной таблице указываются сервисы со следующими параметрами:
* id - уникальный идентификатор сервиса
* code - символьное имя сервиса, уникальное для всех таблицы
* secret - секретный ключ, позволяющий клиенту использовать данных сервис
* info - пояснение, комментарий к сервису

### Таблица urls ###

В данной таблице указываются возможные URL приложения по схеме: `id сервиса => ссылка`.

### Таблица redirects ###

В данной таблице указываются возможные обратные ссылки (редиректы) для сервисов по схеме: `id сервиса => обратная ссылка`.

### Таблица sessions ###

В данной таблице хранится информация об активных сессиях со следующими параметрами:

* service_id - id сервиса
* identifier - определяющее значения аккаунта (например, электронный адрес или логин).
* token - токен, временный хеш-код, используемый для получения данных по пользователю и быстрого прохождения аутентификации с помощью COOKIE.
* created - дата создания токена

### Таблица modules ###

Каждый сервис может иметь свою уникальную систему аутентификации, обработчики, шаблоны, формы и т.п.  
Поэтому необходима возможность указывать дополнительные модули.

Для того, чтобы указать эти дополнительные модули необходимо добавить в таблицу **modules** запись со следующими параметрами:

* service_id - id сервиса
* name - имя (код) модуля
* method - метод подключения (возможны *require, support, ignore*)

### Таблица config ###

В данной таблице можно хранить специфическую конфигурацию сервиса, которая будет перезаписывать получаемый контент из *config.json*.

## Шаблон ##

Шаблоны для detemiro располагаются в директории det-content/themes. 
Далее директория будет выбираться в зависимости от параметра theme в конфигурации системы.

Стандартная тема **master** имеет следующие шаблоны (файлы):

* header.php/footer.php - "верхняя" часть шаблона и его "низ"
* 404.php  - шаблон страницы 404;
* authentication.php - шаблон страницы аутентификации (authentication)
* __templates - шаблоны элементов формы
* __externals - дополнительные обработчики темы для вывода сообщений и элементов формы

## Страницы ##

Код страницы    | Описание
--------------- | ------------------------------------------------------------------------------------------------------------------------------------
authentication  | Страница аутентификации пользователя.
prepareSession  | Страница, при вызове которой с правильным запросом клиента, происходит подготовка временной сессии для пользователя этого клиента.
checkToken      | Страница, при вызове которой с правильным запросом клиента, происходит проверка токена и в случае успеха - получение данных по нему.
logout          | Страница, при вызове которой с правильным запросом клиента, удаляются все данные по пользователю, имеющим указанный токен, на сервере аутентификации.
404             | Самая популярная страница любого сайта.

## Зоны с экшенами (хуки) ##

Имя зоны                 | Описание                                                      | Аргументы                                                | Ожидаемое поведение 
------------------------ | ------------------------------------------------------------- | -------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------
auth.checkAuth           | Стандартная зона, принимающая аргументы метода checkAuth      | (array) данные формы, (object) форма                     | В экшенах ожидается проверка пользовательских данных, а также булевый результат. Возможно сохранение данных для последующего получения.
auth.checkAuth.success   | Зона, срабатываемая в случае успеха всей зоны auth.checkAuth  | (string) логин, (string) токен                           | 
auth.checkAuth.fail      | Зона, срабатываемая в случае провала auth.checkAuth           | (string) логин                                           | 
auth.getByToken          | Зона, срабатываемая в случае успешной проверки токена         | (string) логин владельца токена                          | В экшенах ожидается получение данных для конкретного пользователя в любом виде. Эти данные будут закодированы в JSON.
auth.checkToken.expired  | Зона, срабатываемая при устаревании токена                    | (string) логин владельца токена                          | В экшенах ожидается удаление данных, если они хранятся, например, в кеше.
auth.before.backRedirect | Зона, срабатываемая перед обратным редиректом                 |                                                          | 
auth.checkCookie         | Зона, срабатываемая после успешной проверки куки              | (string) логин владельца куки                            | В экшенах ожидание выдача булевого результата. В случае провала проверки, кука будет удалена.
auth.public.main-form    | Зона, вызываемая в шаблоне authentication.php внутри формы    |                                                          | В данной зоне ожидаете вывод дополнительных элементов формы аутентификации.
auth.logout              | Зона, вызываемая при выходе пользователя по его токену        | (string) логин владельца токена                          | В данной зоне ожидается удаления всех  данных по пользователю на сервере аутентификации.
theme.footer             | Зона темы                                                     |                                                          | 
theme.header             | Зона темы                                                     |                                                          | 
theme.messages           | Зона темы                                                     |                                                          | 
theme.content            | Зона темы                                                     |                                                          | 

## Сообщения системы ##

Код (тип) сообщений | Описание
------------------- | --------------------------------------------------------------------
auth.public         | Категория, в которую попадают сообщения для вывода для пользователя.
auth.result         | Категория, в которую попадают сообщения, выводимые для клиентского приложения в результате. Такие сообщения могут иметь статус error или info/notice.
auth                | Общая категория, такие сообщения могут, например, логироваться.