# Fitness Placet Group

### Несколько скриншотов для понимания, в самом низу порядок работы

![Image alt](https://github.com/HubUzername/fitness/raw/master/images/1.jpg)
![Image alt](https://github.com/HubUzername/fitness/raw/master/images/2.jpg)
![Image alt](https://github.com/HubUzername/fitness/raw/master/images/3.jpg)
![Image alt](https://github.com/HubUzername/fitness/raw/master/images/4.jpg)
![Image alt](https://github.com/HubUzername/fitness/raw/master/images/5.jpg)
![Image alt](https://github.com/HubUzername/fitness/raw/master/images/6.jpg)
![Image alt](https://github.com/HubUzername/fitness/raw/master/images/7.jpg)
![Image alt](https://github.com/HubUzername/fitness/raw/master/images/8.jpg)
![Image alt](https://github.com/HubUzername/fitness/raw/master/images/9.jpg)
![Image alt](https://github.com/HubUzername/fitness/raw/master/images/10.jpg)
![Image alt](https://github.com/HubUzername/fitness/raw/master/images/11.jpg)
![Image alt](https://github.com/HubUzername/fitness/raw/master/images/12.jpg)

### Порядок создания проекта

- Начал разработку с сущности пользователя со всеми необходимыми свойствами
- На основе сущности пользователя доктрина создала таблицу в базе данных
- Для осуществления безопасной аутентификации использовал бандл security
- Появились роуты app_logout и app_login, которые отвечают за авторизацию и выход из аккаунта
- Основным параметром для авторизации выбран email
- Для хранения пароля выбрал plaintext, так как было удобно добавить первого юзера сразу через бд, конечно можно использовать и bcrypt с солью и прочим
- Подключил webpack через composer, для того чтобы подключить Bootstrap и свои стили/js, если потребуется; webpack соединит все это в один app.js и app.css, и сам все будет подключать
- Чтобы постоянно не чистить кэш при изменении CSS и прочего, добавил в Twig расширение Unixtime, которое буду подключать к подгружаемым webpack js и css, чтобы те каждый раз обновлялись
- Далее сразу перешел к созданию админ панели, а вернее ее контроллеру AdminController
- Добавил роут /admin/newuser (AdminController::newUser), который отвечает за регистрацию нового клиента со статусом "Не активен"
- Появляется UserController, отвечающий за бизнес-логику клиентов
- Как и требовалось, отправляется письмо с подтверждением роут /activation/{hash} (UserController::userActivation), после чего клиент вводит свой пароль, статус становится "Активен", теперь клиенты в таблице созданы через админ панель
- Для отправки писем использовал бандл Swift_Mailer реализовав методы формирования отправки писем в классе App\Service\Mailer, тут подключается шаблонизатор чтобы заменять переменные типа {{ username }} в templates/mail (шаблоны писем расположил здесь)
- После добавления клиентов перешел к профилю, роут /profile (UserController::userProfile), тут выводится вся информация о клиенте и его фото (если есть)
- Изменение пароля пользователем через профиль на отдельном роуте /profile/changepassword (UserController::userChangepassword)
- Для генерации форм использовал билдер createFormBuilder и один раз попробовал создать форму через Form/FormType
- Вся разметка осуществляется через Twig шаблонизатор
- Наличие профиля, смены пароля и добавление клиента позволяет дальше работать над админ панелью
- Создаю редактирование клиентов по роуту /admin/users/{userId}/manage (UserController::manageUser)
- На основе ранее созданной формы добавления клиента сделал редактирование, возникла трудность с фотографией, в форму поступала String из базы данных, а требовался File, погуглив нашел решение, которое не помогло, пришлось добавить буферную переменную для сохранения новой фотографии, если ее будут менять, и удаления старой
- Закончив с редактированием клиента перешел к его блокировке/разблокировке
- Заблокированный клиент автоматически разлогинивается, для этого решил реализовать подписчика ивентов на KernelEvents::RESPONSE в классе App\Service\RequestListener
- Создал роут /admin/users (AdminController::showAllUsers) чтобы смотреть всех клиентов, их статусы, основную информацию и переходить к редактированию
- Создал сущность Lessons, которая отвечает за хранение информации о занятиях
- Создал сущность Subscription, которая отвечает за хранение информаии о подписках
- Создал зависимость для доктрины Lessons<>Subscription<>User
- Создал форму добавления нового занятия по роуту /admin/lessons/new (AdminController::addNewLesson)
- Приступил к реализации подписки пользователей
- Создал страницу /profile/lessons (UserController::userLessons) для просмотра занятий
- Решено было выводить занятия на которые подписан выше всех остальных
- Подписка на занятие по умолчанию принимает оповещение Subscription::NOTIFIER_BY_EMAIL
- Сделал в админке роут /admin/lessons (AdminController::lessonsList) вывожу там все занятия и количество подписчиков, добавил кнопку "Оповещение" у каждого занятия
- Для работы RabbitMQ остановил свой выбор на бандле RabbitMQBundle, подклил его, настроил
- Сначала использовал cloudamqp.com, но в последствии понял, что для моих задач там хотят денег
- Пришлось поставить на свою машину RabbitMQ и Erlang, они зависимы
- Подключил свой RabbitMQ к приложению
- Настроил конфиг rabbitmq для двух очередей, одна основная (send.sms.queue) и вторая (send.delayed), как требовалось, для сообщений, что не дошли по какой-либо причине
- Создал /api/sms (SendSmsController) роут и настроил все ответы в JSON формате: Response::HTTP_OK -> успешно ушло сообщение, Response::HTTP_INTERNAL_SERVER_ERROR -> Сообщение не ушло, и будет перемещено во вторую очередь и Response::HTTP_BAD_REQUEST если какие-либо параметры не были переданы
- Запрос принимает GET параметры phone и text
- Для основной очереди (send.sms.queue) создал callback App\Consumer\SmsConsumer, он принимает сообщения из основной очереди, делает запрос к ранее созданному API и ждет ответа, если 200, то возвращает положительный ответ, если иной то перенаправляем в очередь send.delayed
- Для второй очереди создал callback App\Consumer\BlankConsumer, в нее через 10 минут (в тестовом режиме поставил 7 секунд), приходит сообщение. Чтобы не повторять логику отправки письма, решил перенаправить запрос в основную очередь, где он срау же будет обработан и попытка отправки SMS повториться вновь
- Запуск консьюмеров осуществляется через .api/console, которые сразу же принимаются слушать очереди
- Вернулся к списку занятий и ко кнопке "Оповещение"
- По нажатию на кнопку выбираю всех подписчиков данного занятия, которые не заблокированы и не отключили оповещения. E-mail рассылаю как и раньше через Swift_Mailer, а SMS через RabbitMQ, создав запрос добавления события в основную очередь в AdmiController::sendNotification
- Осуществил последние настройки доступа в security.yaml, чтобы к /admin был доступ только у тех, у кого есть роль ROLE_ADMIN, чтобы к / только у тех, кто ROLE_USER, к /api и /login и /activation только те, кто IS_AUTHENTICATED_ANONYMOUSLY
