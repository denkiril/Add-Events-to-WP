<?php
/*
 * Plugin Name: Add Events to WP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function aetwp_setup_post_types() {
    // post_type => 'events'
	register_post_type('events', array(
		'label'  => null,
		'labels' => array(
			'name'               => 'События', // основное название для типа записи
			'singular_name'      => 'Событие', // название для одной записи этого типа
			'add_new'            => 'Добавить событие', // для добавления новой записи
			'add_new_item'       => 'Добавление события', // заголовка у вновь создаваемой записи в админ-панели.
			'edit_item'          => 'Редактировать событие', // для редактирования типа записи
			'new_item'           => 'Новое событие', // текст новой записи
			'view_item'          => 'Смотреть событие', // для просмотра записи этого типа.
			'search_items'       => 'Искать события', // для поиска по этим типам записи
			'not_found'          => 'Не найдено', // если в результате поиска ничего не было найдено
			'not_found_in_trash' => 'Не найдено в корзине', // если не было найдено в корзине
			'parent_item_colon'  => '', // для родителей (у древовидных типов)
			'menu_name'          => 'События', // название меню
		),
		'description'         => 'События – дата, название, описание, + привязка к тегам.',
		'public'              => true,
		'publicly_queryable'  => true, // зависит от public
		'exclude_from_search' => false, // зависит от public
		'show_ui'             => true, // зависит от public
		'show_in_menu'        => true, // показывать ли в меню адмнки
		'show_in_admin_bar'   => true, // по умолчанию значение show_in_menu
		'show_in_nav_menus'   => true, // зависит от public
		'show_in_rest'        => true, // добавить в REST API. C WP 4.7
		'rest_base'           => null, // $post_type. C WP 4.7
		'menu_position'       => 4,
		'menu_icon'           => 'dashicons-calendar-alt', 
		//'capability_type'   => 'post',
		//'capabilities'      => 'post', // массив дополнительных прав для этого типа записи
		//'map_meta_cap'      => null, // Ставим true чтобы включить дефолтный обработчик специальных прав
		'hierarchical'        => false,
		'supports'            => array('title'), // 'title','editor','author','thumbnail','excerpt','trackbacks','custom-fields','comments','revisions','page-attributes','post-formats'
		'taxonomies'          => array('post_tag'),
		'has_archive'         => true,
		'rewrite'             => true,
		'query_var'           => true,
    ) );
    

    // подключаем функцию активации мета блока
    function aetwp_extra_fields() {
        add_meta_box( 'aetwp_extra_fields', 'Доп. поля события', 'aetwp_extra_fields_func', 'events', 'normal', 'high'  );
    }
    add_action('add_meta_boxes', 'aetwp_extra_fields', 1);
}
add_action( 'init', 'aetwp_setup_post_types' );

function aetwp_front_scripts() {
	wp_enqueue_style('jquery-ui', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
	wp_enqueue_style('aetwp-front', plugins_url('/_inc/front.css', __FILE__));
	wp_enqueue_script('aetwp-front-js', plugins_url('/_inc/front_script.js', __FILE__), array('jquery-ui-datepicker'), null, true);
	wp_localize_script( 'aetwp-front-js', 'myajax', array( 'url' => admin_url('admin-ajax.php') ) );	
}

function aetwp_plugin_admin_scripts() {
	if (isset($_GET['post']) && get_post($_GET['post'])->post_type == 'events') {
		$is_aetwp_post_type = true;
	} else {
		$is_aetwp_post_type = false;
	}

    if ($_GET['post_type'] == 'events' || $is_aetwp_post_type) {
		// wp_deregister_script( 'jquery' );
		// wp_register_script( 'jquery', '//code.jquery.com/jquery-3.3.1.min.js', array(), null, true);
		// wp_enqueue_script( 'jquery' );
		wp_enqueue_style('jquery-ui', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
        // wp_enqueue_script( 'jquery-ui-aetwp', '//code.jquery.com/ui/1.12.1/jquery-ui.js', array('jquery'));
        wp_enqueue_script('aetwp-plugin-script', plugins_url('/_inc/admin_script.js', __FILE__), array('jquery-ui-datepicker'), null, true);
		// wp_enqueue_script('aetwp-plugin-script', plugins_url('/_inc/main.js', __FILE__), array('jquery-ui-aetwp'), null, true);
    }
}

function aetwp_admin_menu() {
    add_action( 'load-post-new.php', 'aetwp_plugin_admin_scripts' );
    add_action( 'load-post.php', 'aetwp_plugin_admin_scripts' );
}
add_action( 'admin_menu', 'aetwp_admin_menu' );

function aetwp_extra_fields_func( $post ) {
	$date_str = get_post_meta($post->ID, 'date', 1);
	$parts = explode('-', $date_str);
	if (count($parts) == 3) {
		// "yy-mm-dd" => "dd.mm.yy"
		$date_str = $parts[2].'.'.$parts[1].'.'.$parts[0];
	}

	?>

	<p>Дата события: <input type="text" id="datepicker" value="<?=$date_str?>"></p>
	<input type="text" name="extra[date]" id="extradate">

    <p>Описание события (description):
		<textarea type="text" name="extra[description]" style="width:100%;height:100px;"><?php echo get_post_meta($post->ID, 'description', 1); ?></textarea>
	</p>

    <input type="hidden" name="extra_fields_nonce" value="<?php echo wp_create_nonce(__FILE__); ?>" />

<?php
}

// Сохраняем данные при сохранении поста
function aetwp_extra_fields_update( $post_id ) {
	// базовая проверка
	if (
		   empty( $_POST['extra'] )
		|| ! wp_verify_nonce( $_POST['extra_fields_nonce'], __FILE__ )
		|| wp_is_post_autosave( $post_id )
		|| wp_is_post_revision( $post_id )
	)
		return false;

	// Все ОК! Теперь нужно сохранить/удалить данные
	$_POST['extra'] = array_map( 'sanitize_text_field', $_POST['extra'] ); // чистим все данные от пробелов по краям
	foreach ( $_POST['extra'] as $key => $value ) {
		if ( empty($value) ) {
			delete_post_meta( $post_id, $key ); // удаляем поле если значение пустое
			continue;
		}

		update_post_meta( $post_id, $key, $value ); // add_post_meta() работает автоматически
	}

	return $post_id;
}
add_action( 'save_post', 'aetwp_extra_fields_update', 0 );


function aetwp_install() {
    // trigger our function that registers the custom post type
    aetwp_setup_post_types();
 
    // clear the permalinks after the post type has been registered
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'aetwp_install' );

// var_dump(__FILE__);

function aetwp_deactivation() {
    // our post type will be automatically removed, so no need to unregister it
 
    // clear the permalinks to remove our post type's rules
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'aetwp_deactivation' );

// Register widget area
// function aetwp_widgets_init() {
// 	register_sidebar( array(
// 		'name'          => esc_html__('Events sidebar'),
// 		'id'            => 'events-sidebar',
// 		'description'   => ('Add widgets here.'),
// 		'before_widget' => '<section id="%1$s" class="widget %2$s">',
// 		'after_widget'  => '</section>',
// 		'before_title'  => '<h2 class="widget-title">',
// 		'after_title'   => '</h2>',
// 	) );
// }
// add_action( 'widgets_init', 'aetwp_widgets_init' );

// [events_calendar] 
add_shortcode( 'events_calendar', 'events_calendar_func' );

function events_calendar_func() {

	$echo = '<div class="events_calendar">';
	$echo .= '<div id="datepicker"></div>';
	$echo .= '<div id="events_list"></div>';
	$echo .= '</div>';

	aetwp_front_scripts();

	return $echo;
}

// подключаем AJAX обработчики, только когда в этом есть смысл
if( wp_doing_ajax() ){
	add_action('wp_ajax_get_events', 'get_events');
	add_action('wp_ajax_nopriv_get_events', 'get_events');
}

function get_events() {	
	global $post;
	$events = array();

	$month = 0;
	if(isset($_GET['month'])){
		$month = (int)$_GET['month'];
	}

	$args = array( 
		'post_type' 	=> 'events', 
		'orderby' 		=> 'meta_value',
		'meta_key' 		=> 'date',
		'order'     	=> 'ASC',
		'numberposts' 	=> -1, 
	);
	$myposts = get_posts( $args );

	if( $myposts ){
		// foreach( $myposts as $counter => $post ){
		foreach( $myposts as $post ){
			setup_postdata( $post );
			$date = get_post_meta($post->ID, 'date', 1);
			$parts = explode('-', $date);

			if (!$month || (count($parts) == 3 && $month == (int)$parts[1])) { // "yy-mm-dd" => "mm"
				// $post_id  = get_the_ID();
				$permalink 	 = get_the_permalink();
				$title 		 = esc_html( get_the_title() );
				$description = esc_html( get_post_meta($post->ID, 'description', 1) );
				
				$events[] = array(
					// 'post_id' 	=> $post_id, 
					'permalink' 	=> $permalink,
					'title' 		=> $title,
					'date' 			=> $date,
					'description' 	=> $description,
				);
			}
		}
		wp_reset_postdata();		
	}

	echo json_encode( $events );

	wp_die(); // выход нужен для того, чтобы в ответе не было ничего лишнего, только то что возвращает функция
}
