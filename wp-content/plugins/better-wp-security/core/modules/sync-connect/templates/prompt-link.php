<p class="itsec-sync-connect__link-wrap">
    <input type="submit" class="itsec-sync-connect__link" value="<?php esc_html_e( 'Connect Solid Central', 'better-wp-security' ) ?>">
</p>

<?php
if ( isset( $_REQUEST['itsec_sync_connect_token'] ) ) {
	echo '<input type="hidden" name="itsec_sync_connect_token" value="' . esc_attr( $_REQUEST['itsec_sync_connect_token'] ) . '">';
}
?>
