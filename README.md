# in-ref-gtm
## Определение реферальной ссылки и передача данных в GTM

Цель разработки плагина -- реализация учета продаж по реферальным ссылкам в Google Tag Manager. Плагин реализует следующие возможности:
* Справочник зарегистрированных рефералов
* Проверка реферальной ссылки и сохранение данных о реферале в куки
* Передача данных их куки в переменную dataLayer
* Интеграция с плагином [DuracellTomi's Google Tag Manager for WordPress](https://ru.wordpress.org/plugins/duracelltomi-google-tag-manager/)

## Описание фукнций
### Справочник зарегистрированных рефералов
При активации плагина создается новая роль пользователей referral (Партнер) на базе роли Subscriber. В профиль пользователя добавляется мета-поле refcode ("Код партнера"). Эта инеформация используется для проверки корректности кода партнера.
Реализация: класс INRG_User.

### Проверка реферальной ссылки и сохранение данных о реферале в куки
Рефералы ставят и распространяют свою реферальную ссылку в виде http://example.com/?ref=ABC. Параметр ref указывается в настройках плагина.
При переходе по такой ссылке плагин проверяет значение кода реферала в списке пользовталеей (пользователь должен иметь роль Партнер и код должден соотвествовать указанному. Множественные роли пользователя допускаются. 
Если код совпадает с указанным в профиле пользователя, плагин записывает этот код в куки refcode (имя куки и срок действия указывается в настройках плагина) и осуществляет 303 переадресацию на тот же URL, чтобы убрать реверальный код из URL.
Плагин считывает куки refcode и выполняет проверку кода пользователя также, как и в случае реферальной ссылки. 
Если код корректен, он запоминется в сессии PHP, чтобы повторно не выполнять проверку на каждый запрос страницы.  
Реализация: класс INRG_Referral.

### Передача данных их куки в переменную dataLayer
При формировании страницы проверяется переменная сессии с кодом реферала. Если она существует, то в HEAD выводится скриптовой блок с dataLayer, в который помещается переменная refcode (Код реферала). Имя переменной может быть изменено в настройках планина. 
Реализация: класс INRG_DataLayer.

### Интеграция с плагином [DuracellTomi's Google Tag Manager for WordPress](https://ru.wordpress.org/plugins/duracelltomi-google-tag-manager/)
При формировании страницы проверяется переменная сессии с кодом реферала. Если она существует, то добавляется фильтр плагина [DuracellTomi's Google Tag Manager for WordPress](https://ru.wordpress.org/plugins/duracelltomi-google-tag-manager) GTM4WP_WPFILTER_EEC_PRODUCT_ARRAY. 
Реализация: класс INRG_GTM4WP.

### Другие классы
* INRG_Base_Settings -- базовый класс, реализует функцию вывода части страницы настроек
* INRG_Base_Handler -- базовый класс обработчика кода реферала. Реализует проверку сессионной переменной и дальнейшую орбработку. От него насдедуюются INRG_DataLayer и INRG_GTM4WP
* INRG_Settings -- класс настроек. Реализует чтение, сохранение настроек как своих свойств, формирование страницы настроек
* INRG_Plugin -- класс плагина, объединяет все остальные объекты, как свои свойства.

