<?php

namespace Ew;

/**
 * Class Event
 */
class Event {
	/**
	 * Event post type
	 *
	 * @var string
	 */
	public static $POST_TYPE = 'ew-event';

	/**
	 * Post type label
	 *
	 * @var string
	 */
	public static $LABEL = 'Event';

	/**
	 * @var \WP_Post
	 */
	public $post;

	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var \DateTime
	 */
	public $start_date;

	/**
	 * @var \DateTime
	 */
	public $end_date;

	/**
	 * Event constructor.
	 *
	 * @param $wp_post
	 * @param array $row (from wp_ew_events table in database)
	 *
	 * @throws \Exception
	 */
	public function __construct( $wp_post, $row = [] ) {
		if ( empty( $wp_post ) ) {
			throw new \Exception( 'No post in event constructor' );
		}

		// Get variables from $wp_post and initialize ones that don't depend on $wp_post
		$this->post       = $wp_post;
		$this->id         = $wp_post->ID;
		$this->start_date = new \DateTime();
		$this->end_date   = new \DateTime();

		// If row is empty, we're done - event is filled with initial data
		if ( empty( $row ) ) {
			return;
		}

		// If row is non empty, assign values from the database
		$this->start_date = \DateTime::createFromFormat( EW_DATE_FORMAT, $row['start_date'] );
		$this->end_date   = \DateTime::createFromFormat( EW_DATE_FORMAT, $row['end_date'] );
	}

	/**
	 * Load Event class
	 *
	 * @param $loader
	 */
	public static function load_class( $loader ) {
		// Init post type action
		$loader->add_action( 'init', static::class, 'init_post_type' );
		// Add event meta boxes
		$loader->add_action( 'add_meta_boxes', static::class, 'add_meta_boxes' );
		// Add action on save event
		$loader->add_action( 'save_post', static::class, 'save_event', 10, 3 );
	}

	/**
	 * Register event custom post type
	 */
	public static function init_post_type() {
		$labels = [
			'name'               => _x( 'Events', 'post type general name', PLUGIN_TEXTDOMAIN ),
			'singular_name'      => _x( 'Event', 'post type singular name', PLUGIN_TEXTDOMAIN ),
			'menu_name'          => _x( 'Events', 'admin menu', PLUGIN_TEXTDOMAIN ),
			'name_admin_bar'     => _x( 'Events', 'add new on admin bar', PLUGIN_TEXTDOMAIN ),
			'add_new'            => _x( 'Add new', static::$POST_TYPE, PLUGIN_TEXTDOMAIN ),
			'add_new_item'       => __( 'Add new event', PLUGIN_TEXTDOMAIN ),
			'new_item'           => __( 'New event', PLUGIN_TEXTDOMAIN ),
			'edit_item'          => __( 'Edit event', PLUGIN_TEXTDOMAIN ),
			'view_item'          => __( 'View event', PLUGIN_TEXTDOMAIN ),
			'all_items'          => __( 'All events', PLUGIN_TEXTDOMAIN ),
			'search_items'       => __( 'Search events', PLUGIN_TEXTDOMAIN ),
			'parent_item_colon'  => __( 'Parent event:', PLUGIN_TEXTDOMAIN ),
			'not_found'          => __( 'No event found', PLUGIN_TEXTDOMAIN ),
			'not_found_in_trash' => __( 'No event found in trash', PLUGIN_TEXTDOMAIN )
		];

		// Args
		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'events' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'menu_icon'          => 'dashicons-calendar-alt',
			'supports'           => array(
				'title',
				'editor'
			)
		];

		// Register post type
		register_post_type( static::$POST_TYPE, $args );
	}

	/**
	 * Adds event meta box
	 */
	public static function add_meta_boxes() {
		add_meta_box( static::$POST_TYPE . '-editor', 'Set the event start and end date', [
			static::class,
			'render_event_dates_editor'
		], static::$POST_TYPE, 'advanced', 'default' );
	}

	public static function render_event_dates_editor( $post ) {
		$eventsRepository = new Events_Repository();
		$event            = $eventsRepository->get_event_by_id( $post->ID );
		require_once PLUGIN_DIR . 'admin/views/events/event-dates-meta-box.php';
	}

	/**
	 * On event save function
	 * IMPORTANT: NEVER call wp_update_post in function that is called on the save_post hook:
	 * since wp_update_post includes save_post hook it creates an infinite loop
	 *
	 * @param $post_id
	 * @param $post
	 */
	public static function save_event( $post_id, $post ) {
		// If post is revision or is not event post type return
		if ( wp_is_post_revision( $post_id ) || $post->post_type !== static::$POST_TYPE || defined( 'DOING_AJAX' ) ) {
			return;
		}

		// Call the save function from the events service
		$events_service = new Events_Service();
		$events_service->on_save_event( $post, $_POST );
	}
}