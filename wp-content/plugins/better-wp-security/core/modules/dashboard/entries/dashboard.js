/**
 * WordPress dependencies
 */
import { setLocaleData } from '@wordpress/i18n';
import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

setLocaleData( { '': {} }, 'better-wp-security' );

/**
 * Internal dependencies
 */
import App from './dashboard/app.js';

domReady( () => {
	const el = document.getElementById( 'itsec-dashboard-root' );

	if ( el ) {
		const canManage = el.dataset.canManage === '1';
		const installType = el.dataset.installType;

		render( <App context={ { canManage, installType } } />, el );
	}
} );

export * from './dashboard/utils';
export { useRegisterCards } from './dashboard/cards';
export Card from './dashboard/components/card';
export CardHeader, {
	Date as CardHeaderDate,
	Status as CardHeaderStatus,
	Title as CardHeaderTitle,
} from './dashboard/components/card/header';
export CardFooter, {
	FooterSchemaActions as CardFooterSchemaActions,
} from './dashboard/components/card/footer';
export PromoCard from './dashboard/components/edit-cards/promo-card';
export MasterDetail, {
	Back as MasterDetailBack,
} from './dashboard/components/master-detail';
