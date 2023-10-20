<div class="itsec-sync-connect-wrap">
    <img class="itsec-sync-connect__logo" height="116" src="<?php echo esc_url( plugin_dir_url( __DIR__ ) . 'img/central-logo.svg' ); ?>" alt="">
    <p class="itsec-sync-connect__title"><?php esc_html_e( 'Connecting Solid Central', 'better-wp-security' ); ?></p>
    <p class="itsec-sync-connect__description"><?php esc_html_e( 'This will install the Solid Central plugin and connect the plugin to your Central account.', 'better-wp-security' ); ?></p>

	<?php require( __DIR__ . '/prompt-link.php' ); ?>

	<?php require( __DIR__ . '/fallback.php' ); ?>
</div>
