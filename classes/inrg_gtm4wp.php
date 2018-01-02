<?php
/**
 * Класс реализует логику чтения и сохранения реферальной ссылки
 */
class INRG_GTM4WP extends INRG_Base_Settings
{
	/**
	 * Объект текущего реферала
	 */ 
	protected $currentReferral;	
	
	/**
	 * Код  текущего реферала
	 */ 
	protected $currentReferralCode;		
	
	/**
	 * Режим перезаписи поля affiliation
	 */ 
	const AFFILIATION_ENABLED	= 'gtm4wp_affiliation_rewrite';
	protected $affiliationEnabled;		
	
	/**
	 * Конструктор
	 * @param INRG_Plugin	$plugin			Ссылка на основной объект плагина
	 */
	public function __construct( $plugin )
	{
		parent::__construct( $plugin );
		$this->title = __('Интеграция с плагином DuracellTomi\'s Google Tag Manager', INRG);
		
		// Установка параметров дефолтовыми значениями
		$this->affiliationEnabled = $this->plugin->settings->get( self::AFFILIATION_ENABLED, true );
		
		// Хук для плагина DuracellTomi's Google Tag Manager for WordPress
		add_filter( 'gtm4wp_compile_datalayer', array( $this, 'addRefCode' ) );
	}
	
	/**
	 * Инициализация объекта по хуку init
	 */
	public function init()
	{
		// Чмитаем реферала в свойства объекта
		$this->currentReferral = $this->plugin->referral->getCurrentReferral();
		$this->currentReferralCode = $this->plugin->referral->getCurrentReferralCode();
	}
	
	/**
	 * Добавляет код реферала в массив данных для плагина DuracellTomi's Google Tag Manager for WordPress
	 * @param mixed 	$productData	Данные о товарах
	 * @param string 	$operation		Операция в GTM dataLayer
	 */
	public function addRefCode( $dataLayer )
	{		
		// Если включена интеграфия и код партнера определен
		if ( $this->affiliationEnabled && $this->currentReferral )
		{
			// Стандартная электронная торговля
			if ( isset( $dataLayer['transactionAffiliation'] ) )
				$dataLayer['transactionAffiliation'] = $this->currentReferral->display_name;
			
			// Расширенная электронная торговля
			if ( isset( $dataLayer['ecommerce']['purchase']['actionField']['affiliation'] ) )
				$dataLayer['ecommerce']['purchase']['actionField']['affiliation'] = $this->currentReferral->display_name;			
			
		}
	
		//$this->plugin->activityLog( var_export( $dataLayer, true ) );
		return $dataLayer;
	}
	
  /**
   * Показ настроек класса на странице настроек
   */
	public function showSettings()
	{
?>
	<div class="inrg-field">
		<label for="inrg-affiliationEnabled"><?php esc_html_e( 'Перезапись поля affiliation', INRG ) ?></label>
		<div class="inrg-input">
			<input id="inrg-affiliationEnabled" name="inrg-affiliationEnabled" type="checkbox" <?php checked( $this->affiliationEnabled, 1 ); ?> />
			<p><?php esc_html_e( 'Установите, для того, чтобы перезаписать свойство affiliation именем партнера при регистрации заказа. По умолчанию плагин DuracellTomi\'s Google Tag Manager выводит в это поле название сайта, и это поле используется для построения отчетов Google Analytics "Код партнера"', INRG ) ?></p>
		</div>
	</div>
<?php
	}
	
  /**
   * Сохранение настроек класса на странице настроек
   */  
	public function saveSettings()
	{
		$this->affiliationEnabled = (bool) isset($_POST[ 'inrg-affiliationEnabled' ] ) && $_POST[ 'inrg-affiliationEnabled' ];
		$this->plugin->settings->set( self::AFFILIATION_ENABLED, $this->affiliationEnabled );
	}
}