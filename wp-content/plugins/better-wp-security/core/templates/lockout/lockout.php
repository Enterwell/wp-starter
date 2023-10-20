<?php
/**
 * @var \iThemesSecurity\Lib\Lockout\Context $context
 * @var string                               $message
 * @var array[]                              $actions
 */
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="<?php echo plugin_dir_url( __FILE__ ) . 'lockout.css'; ?>" type="text/css" rel="stylesheet">
	<?php if ( isset( $GLOBALS['wp_query'] ) ): ?>
		<?php wp_robots(); ?>
	<?php endif; ?>
	<title><?php esc_html_e( 'Forbidden', 'better-wp-security' ); ?></title>
</head>
<body id="error-page">
<div id="lockout_screen">
	<div class="container">

		<div id="lockout-container-primary">
			<div class="lockout-content">
				<img src="<?php echo plugin_dir_url( __FILE__ ) . 'lockout.svg'; ?>" alt="" class="shield-icon"/>

				<div id="lockout-text">
					<h1><?php esc_html_e( 'You have been locked out of this site', 'better-wp-security' ) ?></h1>
					<p><?php echo $message; ?></p>
					<?php do_action( 'itsec_lockout_template_before_actions', $context ); ?>
					<?php foreach ( $actions as $action ): ?>
						<a class="btn <?php echo empty( $action['secondary'] ) ? '' : 'secondary' ?>" href="<?php echo esc_url( $action['uri'] ); ?>">
							<?php echo $action['label']; ?>
						</a>
					<?php endforeach; ?>
				</div>

			</div>
		</div>
		<div class="lockout-container-secondary"></div>
	</div>
</div>
</body>
</html>
