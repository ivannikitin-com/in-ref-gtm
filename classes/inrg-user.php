<?php
/**
 * Класс реализует логику чтения и сохранения реферальной ссылки
 */
class INRG_User extends INRG_Base_Settings
{
	/**
	 * Новая роль
	 * @static
	 */
	const ROLE 			= 'referral';
	const ROLE_BASE 	= 'subscriber';
	
	/**
	 * Мета-поле Код реферала
	 * @static
	 */
	const REFCODE 			= 'refcode';	
	const REFCODE_HTML_ID 	= 'inrg-refcode';
	
	/**
	 * Кэш объетов пользователей
	 * @static
	 */
	const USERS_CACHE 		= 'inrg-users';	
	
	
	
	/**
	 * Конструктор
	 * @param INRG_Plugin	$plugin			Ссылка на основной объект плагина
	 */
	public function __construct( $plugin )
	{
		parent::__construct( $plugin );
		$this->title = '';	// Ничего не выводим
	}
	
	/**
	 * Инициализация роли по хуку активации плагина
	 */
	public static function addRole()
	{
		$baseRole = get_role( self::ROLE_BASE );
		add_role( self::ROLE, __('Партнёр', INRG), $baseRole->capabilities );
	}	
	

	/**
	 * Инициализация объекта по хуку init
	 */
	public function init()
	{
		// Показ Метабокса в профиле пользователя
		add_action( 'show_user_profile', array( $this, 'showMetabox' ) );
		add_action( 'edit_user_profile', array( $this, 'showMetabox' ) );
		
		// Сохранение метабокса в профиле пользователя
		add_action( 'personal_options_update', array( $this, 'saveMetabox' ) );
		add_action( 'edit_user_profile_update', array( $this, 'saveMetabox' ) );
		
		// Добавим колонку в таблицу при показе пользователей в админке с ролью рефералы
		add_filter('manage_users_columns' , 		array( $this, 'addRefCodeColumn' ) );
		add_filter('manage_users_custom_column' , 	array( $this, 'showRefCodeColumn' ), 10, 3 );
		
	}
	
	/**
	 * Мета-поле пользователя
	 */
	public function showMetabox( $user ) 
	{ 
		$refCode = get_the_author_meta( self::REFCODE, $user->ID );

?>
		<h3><?php _e('Партнёрская программа', INRG)?></h3>
		<table class="form-table">
			<tr>
				<th><label for="<?php echo self::REFCODE_HTML_ID ?>"><?php _e('Код партнёра', INRG)?></label></th>
				<td>
					<input type="text" name="<?php echo self::REFCODE_HTML_ID ?>" id="<?php echo self::REFCODE_HTML_ID ?>" 
						   value="<?php echo esc_attr( $refCode ) ?>" class="regular-text" /><br />
					<span class="description"><?php _e('Укажите свой код партнёра. он должен быть уникальным', INRG)?></span>
				</td>
			</tr>
		</table>
	<?php }	
	 
	/**
	 * Сохранение метабокса пользователя
	 */
	public function saveMetabox( $userId ) 
	{ 
		if ( ! current_user_can( 'edit_user', $userId ) )
			return false;
		
		$refCode = sanitize_text_field( $_POST[ self::REFCODE_HTML_ID ] );
		update_usermeta( $userId, self::REFCODE, $refCode );
		delete_transient( self::USERS_CACHE );
	}
	
	/**
	 * Возвращает пользователя по коду партнера
	 * @param string $refcode	Код партнера
	 * @return WP_User
	 */
	public function getUserByRefCode( $refcode ) 
	{ 
		if ( empty( $refcode ) )
			return false;
		
		// Читаем массив объектов из кэша
		$users = get_transient( self::USERS_CACHE );
		
		// Если чтение не удалось, создаем пустой массив
		if ( $users === false )
		{
			$users = array();
		}		
		
		// Проверяем наличие пользователя с этим кодом в массиве
		if ( array_key_exists( $refcode, $users) )
		{
			// Возвращаем найденного пользователя
			return $users[ $refcode ];
		}
		
		// Находим пользователя в БД
		$wpUsers = get_users( array( 'meta_key' => self::REFCODE, 'meta_value' => $refcode ) );
		if ( count( $wpUsers ) > 0 )
		{
			// Сохраняем пользователя в кэше и возвращаем его
			$users[ $refcode ] = $wpUsers[ 0 ];
			set_transient( self::USERS_CACHE, $users, 2 * DAY_IN_SECONDS );
			return $users[ $refcode ];
		}

		// Ничего не найдено
		return false;
	}
	
	/**
	 * Добавляет колонку в тбалице пользователей
	 * @param mixed $columns	Массив колонок
	 * @return mixed
	 */
	public function addRefCodeColumn( $columns ) 
	{ 
		$columns[ self::REFCODE ] = __('Код партнёра', INRG);
		return $columns; 
	}
	/**
	 * Выводит колонку в тбалице пользователей
	 * @param string $output		Custom column output. Default empty.
	 * @param string $columnName	Column name.
	 * @param string $column_name	Column name.
	 * @param int $userId 			ID of the currently-listed user.
	 * @return mixed
	 */
	public function showRefCodeColumn( $output, $columnName, $userId  ) 
	{ 
		if ( $columnName == self::REFCODE )
		{
			return get_the_author_meta( self::REFCODE, $userId );
		}
		
		return $output;
	}	
	
}	