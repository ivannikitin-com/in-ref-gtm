<?php
/**
 * Базовый класс, реализует базовый функционал и определяет функцию вывода части страницы настроек
 */
class INRG_Base_Settings
{
	/**
	 * Основной класс плагина
	 * @var INRG_Plugin
	 */
	protected $plugin;

	/**
	 * Название этого класса для страниц настроек
	 * @var string
	 */
	public $title;
	
	
	/**
	 * Конструктор
	 * @param INRG_Plugin	$plugin			Ссылка на основной объект плагина
	 */
	public function __construct( $plugin )
	{
		$this->plugin = $plugin;
		// Регистрируем модуль настрое
		$this->plugin->settings->registerSettings( $this );
	}

	/**
	 * Инициализация объекта по хуку init
	 */
	public function init()
	{
    // Ничего, определяется в базовом классе
	}

  /**
   * Показ настроек класса на странице настроек
   */
  public function showSettings()
	{
		// Ничего, определяется в базовом классе
	}
  
  /**
   * Сохранение настроек класса на странице настроек
   */  
  public function saveSettings()
	{
		// Ничего, определяется в базовом классе
	}
}