<?php
/**
 * Класс реализует логику чтения и сохранения реферальной ссылки
 */
class INRG_Referral extends INRG_Base_Settings
{
	/**
	 * Параметр переменной в сессии, хранит текущий код пользователя
	 */ 
	const CURRENT_CODE 		= 'ref_current_code';	
	
	/**
	 * Параметр реферальной ссылки
	 */ 
	const REF_LINK 			= 'ref_link';
	protected $refLink;
	
	/**
	 * Параметр реферальной куки
	 */ 
	const REF_COOKIE 		= 'ref_cookie';
	protected $refCookie;
	
	/**
	 * Параметр время жизни реферальной куки
	 */ 
	const REF_COOKIE_EXPIRE	= 'ref_cookie_expire';
	protected $refCookieExpire;

	/**
	 * Параметр вывод в dataLayer
	 */ 
	const DATALAYER_ENABLED	= 'datalayer_enabled';
	protected $dataLayerEnabled;	
	
	/**
	 * Параметр переменная dataLayer
	 */ 
	const DATALAYER_VAR	= 'datalayer_var';
	protected $dataLayerVar;	
	
	/**
	 * Объект текущего реферала
	 */ 
	protected $currentReferral;	
	
	/**
	 * Код  текущего реферала
	 */ 
	protected $currentReferralCode;	
	
	/**
	 * Конструктор
	 * @param INRG_Plugin	$plugin			Ссылка на основной объект плагина
	 */
	public function __construct( $plugin )
	{
		parent::__construct( $plugin );
		$this->title = __('Параметры реферальной ссылки', INRG);
		
		// Инициализируем параметры с дефолтовыми значениями
		$this->refLink 			= $this->plugin->settings->get( self::REF_LINK, 'ref' );
		$this->refCookie 		= $this->plugin->settings->get( self::REF_COOKIE, 'refcode' );
		$this->refCookieExpire 	= $this->plugin->settings->get( self::REF_COOKIE_EXPIRE, 30 );
		$this->dataLayerEnabled = $this->plugin->settings->get( self::DATALAYER_ENABLED, true );
		$this->dataLayerVar 	= $this->plugin->settings->get( self::DATALAYER_VAR, 'ref' );
		$this->currentReferral = false;
		$this->currentReferralCode = false;
		
		// Вывод dataLayer
		if ( $this->dataLayerEnabled )
			add_action( 'wp_head', array( $this, 'showDataLayer' ) );
		
	}

	/**
	 * Инициализация объекта по хуку init
	 */
	public function init()
	{
		// Чмитаем реферала в свойства объекта
		$this->getCurrentReferral();
	}
	
	/**
	 * Возвращает объект WP_User теекущего реферала или false, если его нет 
	 * @return WP_User
	 */
	public function getCurrentReferral()
	{
		// Проверка перехода по реферальной ссылке
		if ( isset( $_GET[ $this->refLink ] ) )
		{
			// Проверяем наличие такого кода
			$currentCode = sanitize_key( $_GET[ $this->refLink ] );
			$this->currentReferral = $this->plugin->user->getUserByRefCode( $currentCode );
			if ( $this->currentReferral )
			{
				// Передан код реферала по ссылке, сохраняем его
				setcookie( $this->refCookie, $currentCode, time() + $this->refCookieExpire * DAY_IN_SECONDS );
				// Формируем новый адрес для переадресации
				$url = $_SERVER[ 'REQUEST_URI' ];
				$url = str_replace( $this->refLink . '=' . $_GET[ $this->refLink ], '', $url );
				$url = preg_replace( '/([\?&])$/', '', $url );
				wp_redirect( $url, 303 );
				exit;
			}
		}
		
		// Проверка реферального куки
		if ( isset( $_COOKIE[ $this->refCookie ] ) )
		{
			// Проверяем наличие такого кода
			$currentCode = sanitize_key( $_COOKIE[ $this->refCookie ] );
			$this->currentReferral = $this->plugin->user->getUserByRefCode( $currentCode );
			if ( $this->currentReferral )
				$this->currentReferralCode = $currentCode;
		}
		
		return $this->currentReferral;
	}
	
	/**
	 * Возвращает код теекущего реферала или false, если его нет 
	 * @return string
	 */
	public function getCurrentReferralCode()
	{
		return $this->currentReferralCode;
	}
	
	/**
	 * Выводит dataLayer
	 * @return string
	 */
	public function showDataLayer()
	{
		if ( $this->currentReferral )
		{
			echo "<script>dataLayer=dataLayer||[];dataLayer.push({'{$this->dataLayerVar}':{'code':'{$this->currentReferralCode}','name':'{$this->currentReferral->display_name}'}})</script>";
		}
	}	
	
  /**
   * Показ настроек класса на странице настроек
   */
	public function showSettings()
	{
?>
	<div class="inrg-field">
		<label for="inrg-RefLink"><?php esc_html_e( 'Параметр реферала в ссылке', INRG ) ?></label>
		<div class="inrg-input">
			<input id="inrg-RefLink" name="inrg-RefLink" type="text" value="<?php echo esc_attr( $this->refLink ) ?>" />
			<p><?php esc_html_e( 'Укажите параметр реферала в реферальной ссылке', INRG ) ?></p>
		</div>
	</div>
	<div class="inrg-field">
		<label for="inrg-RefCookie"><?php esc_html_e( 'Имя куки реферала', INRG ) ?></label>
		<div class="inrg-input">
			<input id="inrg-RefCookie" name="inrg-RefCookie" type="text" value="<?php echo esc_attr( $this->refCookie ) ?>" />
			<p><?php esc_html_e( 'Укажите имя куки для сохраненичя кода реферала. Не меняйте эту настройку, если не понимаете для чего она!', INRG ) ?></p>
		</div>
	</div>
	<div class="inrg-field">
		<label for="inrg-RefCookieExpire"><?php esc_html_e( 'Время жизни куки, дней', INRG ) ?></label>
		<div class="inrg-input">
			<input id="inrg-RefCookieExpire" name="inrg-RefCookieExpire" type="text" value="<?php echo esc_attr( $this->refCookieExpire ) ?>" />
			<p><?php esc_html_e( 'Укажите время жизни куки в днях. Не меняйте эту настройку, если не понимаете для чего она!', INRG ) ?></p>
		</div>
	</div>
	<div class="inrg-field">
		<label for="inrg-dataLayerEnabled"><?php esc_html_e( 'Вывод кода реферала в переменную dataLayer', INRG ) ?></label>
		<div class="inrg-input">
			<input id="inrg-dataLayerEnabled" name="inrg-dataLayerEnabled" type="checkbox" <?php checked( $this->dataLayerEnabled, 1 ); ?> />
			<p><?php esc_html_e( 'Установите, если необходим вывод данных реферала в переменную dataLayer.', INRG ) ?></p>
		</div>
	</div>
	<div class="inrg-field">
		<label for="inrg-dataLayerVar"><?php esc_html_e( 'Переменная dataLayer', INRG ) ?></label>
		<div class="inrg-input">
			<input id="inrg-dataLayerVar" name="inrg-dataLayerVar" type="text" value="<?php echo esc_attr( $this->dataLayerVar ) ?>" />
			<p><?php esc_html_e( 'Укажите имя переменной dataLayer.', INRG ) ?></p>
		</div>
	</div>
<?php	
	}
  
  /**
   * Сохранение настроек класса на странице настроек
   */  
	public function saveSettings()
	{
		// Чтение настроек
		$this->refLink = ( isset($_POST[ 'inrg-RefLink' ] ) ? sanitize_text_field( $_POST[ 'inrg-RefLink' ] ) : $this->refLink );
		$this->refCookie = ( isset($_POST[ 'inrg-RefCookie' ] ) ? sanitize_text_field( $_POST[ 'inrg-RefCookie' ] ) : $this->refCookie );
		$this->refCookieExpire = ( isset($_POST[ 'inrg-RefCookieExpire' ] ) ? sanitize_text_field( $_POST[ 'inrg-RefCookieExpire' ] ) * 1 : $this->refCookieExpire );
		$this->dataLayerEnabled = (bool) isset($_POST[ 'inrg-dataLayerEnabled' ] ) && $_POST[ 'inrg-dataLayerEnabled' ];
		$this->dataLayerVar = ( isset($_POST[ 'inrg-dataLayerVar' ] ) ? sanitize_text_field( $_POST[ 'inrg-dataLayerVar' ] ) : $this->dataLayerVar );
		
		// Сохранение настроек
		$this->plugin->settings->set( self::REF_LINK, $this->refLink );
		$this->plugin->settings->set( self::REF_COOKIE, $this->refCookie );
		$this->plugin->settings->set( self::REF_COOKIE_EXPIRE, $this->refCookieExpire );
		$this->plugin->settings->set( self::DATALAYER_ENABLED, $this->dataLayerEnabled );
		$this->plugin->settings->set( self::DATALAYER_VAR, $this->dataLayerVar );
	}

}	