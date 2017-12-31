<?php
/**
 * Класс реализует логику чтения и сохранения реферальной ссылки
 */
class INRG_Referral extends INRG_Base_Settings
{	

	/**
	 * Конструктор
	 * @param INRG_Plugin	$plugin			Ссылка на основной объект плагина
	 */
	public function __construct( $plugin )
	{
		parent::__construct( $plugin );
		$this->title = __('Параметры реферальной ссылки', INRG);
	}

	/**
	 * Инициализация объекта по хуку init
	 */
	public function init()
	{

	}

  /**
   * Показ настроек класса на странице настроек
   */
  public function showSettings()
	{
		
	}
  
  /**
   * Сохранение настроек класса на странице настроек
   */  
  public function saveSettings()
	{
		
	}

}	