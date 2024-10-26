<?php
namespace EwStarter\Main\Interfaces;

/**
 * Plugin loader interface.
 */
interface Plugin_Loader_Interface {
	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @param string $hook The name of the WordPress action that is being registered.
	 * @param object|string $component A reference to the instance of the object on which the action is defined.
	 * @param string $callback The name of the function definition on the $component.
	 * @param int $priority Optional. The priority at which the function should be fired. Default is 10.
	 * @param int $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 *
	 * @since    1.0.0
	 */
	function add_action( string $hook, object|string $component, string $callback, int $priority = 10, int $accepted_args = 1 ): void;

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @param string $hook The name of the WordPress filter that is being registered.
	 * @param object|string $component A reference to the instance of the object on which the filter is defined.
	 * @param string $callback The name of the function definition on the $component.
	 * @param int $priority Optional. The priority at which the function should be fired. Default is 10.
	 * @param int $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1
	 *
	 * @since    1.0.0
	 */
	function add_filter( string $hook, object|string $component, string $callback, int $priority = 10, int $accepted_args = 1 ): void;

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	function run(): void;
}
