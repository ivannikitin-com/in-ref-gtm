<?php
/**
 * Класс реализует загрузку и сохранение любых параметров
 */
class INRG_Settings
{
	/**
	 * Основной класс плагина
	 * @var Plugin
	 */
	protected $plugin;	
	
	/**
	 * Название опции в Wordpress
	 * @var string
	 */
	protected $_name;	
	
	/**
	 * Массив хранения параметров
	 * @var mixed
	 */
	protected $_params;

	/**
	 * Массив хранения объектов с настройками 
	 * @var mixed
	 */
	public $_modules;	
	
	/**
	 * Конструктор
	 * инициализирует параметры и загружает данные
	 * @param INRG_Plugin	$plugin			Ссылка на основной объект плагина
	 */
	public function __construct( $plugin )
	{
		$this->_name = get_class( $this );
		$this->plugin = $plugin;
		$this->_modules = array();
		
		// Загружаем параметры
		$this->load();
		
		// Если это работа в админке
		if ( is_admin() )
		{
			// Стили для админки
			add_action( 'admin_enqueue_scripts', array( $this, 'loadСSS' ) );
			
			// Страница настроек
			add_action( 'admin_menu', array( $this, 'addSettingsPage' ) );
		}
	}
	
	/**
	 * Загрузка CSS
	 */
	public function loadСSS( $hook )
	{
		// Load only on /wp-admin/options-general.php?page=in-ref-gtm
		if ( $hook != 'settings_page_' . INRG)
						return;
			
		wp_enqueue_style( INRG . '-admin', $this->plugin->url . 'assets/css/admin.css' );		
	}	
	

	/**
	 * Загрузка параметров в массив из БД Wordpress
	 */
	public function load()
	{
		$this->_params = get_option( $this->_name, array() );	
	}	

	
	/**
	 * Сохранение параметров в БД Wordpress
	 */
	public function save()
	{
		update_option( $this->_name, $this->_params );
	}

	/**
	 * Чтение параметра
	 * @param string	$param		Название параметра
	 * @param mixed 	$default	Значение параметра по умолчанию, если его нет или он пустой
	 * @return mixed				Возвращает параметр
	 */
	public function get( $param, $default = false )
	{
		if ( ! isset( $this->_params[ $param ] ) )
			return $default;
		
		if ( empty( $this->_params[ $param ] ) )
			return $default;
		
		return $this->_params[ $param ];
	}
	
	/**
	 * Сохранение параметра
	 * @param string	$param		Название параметра
	 * @param mixed 	$value		Значение параметра
	 */
	public function set( $param, $value )
	{
		$this->_params[ $param ] = $value;
	}
	
	/**
	 * Чтение свойства
	 * @param string	$param		Название параметра
	 */
	public function __get( $param )
	{
		return $this->get( $param );
	}
	/**
	 * Запись свойства
	 * @param string	$param		Название параметра
	 */
	public function __set( $param, $value )
	{
		return $this->set( $param, $value );
	}	
	
	/**
	 * Регистрирует объект, которому требуется вывод настроек
	 * @paran INRG_Base_Settings $module	Экземпляр класса с настройками
	 */
	public function registerSettings( $module )
	{
		$this->_modules[] = $module;
	}
	
	
	
	/**
	 * Добавляет страницу настроект плагина в меню типа данных
	 */
	public function addSettingsPage()
	{
		add_options_page(
			__( 'Код реферала в Google Tag Manager', INRG), 
			__( 'Коды рефералов', INRG), 
			'manage_options', 
			INRG, 
			array( $this, 'showSettingsPage' )
			);	
	}
	
	/** 
	 * Выводит страницу настроект плагина
	 */
	public function showSettingsPage( )
	{	
		$nonceField = INRG;
		$nonceAction = 'save-settings';
		$nonceError = false;
		
		// Обработка формы
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' )
		{
			if ( ! isset( $_POST[$nonceField] ) || ! wp_verify_nonce( $_POST[$nonceField], $nonceAction ) ) 
			{
				$nonceError = true;
			} 
			else 
			{
				// Сохранение модулей	
				foreach ($this->_modules as $module )
					$module->saveSettings();
				
				// Save all data
				$this->save();
			}		
		}
		
?>
<h1><?php _e('Код реферала в Google Tag Manager', INRG) ?></h1>
<?php if ( $nonceError ) echo '<p class="error">',  __('Ошибка поля nonce', INRG), '</p>'; ?>

<p><?php _e( 'Инструкция по использованию и настройке плагина <a href="https://github.com/ivannikitin-com/in-ref-gtm/blob/master/USER_MANUAL.md" target="_blank">здесь</a>.', INRG ) ?></p>


<form id="inrg-settings" action="<?php echo $_SERVER['REQUEST_URI']?>" method="post">
	<?php wp_nonce_field( $nonceAction, $nonceField ) ?>
	
	<?php foreach ($this->_modules as $module ): ?>
		<fieldset>
			<legend><?php echo $module->title ?></legend>
			<?php $module->showSettings() ?>
		</fieldset>
	<?php endforeach ?>
	
	<?php submit_button() ?>
</form>
<?php	
	}
	

	
}