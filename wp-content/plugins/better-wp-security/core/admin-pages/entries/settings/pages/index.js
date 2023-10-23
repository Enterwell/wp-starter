/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Page } from '../page-registration';
import SiteType from './site-type';
import { ModulesOfTypePage, SingleModulePage, TabbedModulesPage } from './configure';
import SecureSite from './secure-site';
import Summary from './summary';

export default function Pages() {
	return (
		<>
			<Page
				id="site-type"
				title={ __( 'Website', 'better-wp-security' ) }
				priority={ 0 }
				roots={ [ 'onboard' ] }
			>
				{ () => <SiteType /> }
			</Page>
			<Page
				id="global"
				title={ __( 'Global Settings', 'better-wp-security' ) }
				priority={ 4 }
				roots={ [ 'onboard', 'settings', 'import' ] }
			>
				{ () => <SingleModulePage module="global" /> }
			</Page>
			<Page
				id="configure"
				title={ __( 'Features', 'better-wp-security' ) }
				priority={ 6 }
				roots={ [ 'onboard', 'settings', 'import' ] }
			>
				{ () => <TabbedModulesPage exclude={ [ 'advanced' ] } /> }
			</Page>

			<Page
				id="advanced"
				title={ __( 'Advanced', 'better-wp-security' ) }
				priority={ 25 }
				roots={ [ 'settings' ] }
			>
				{
					() => (
						<ModulesOfTypePage
							type="advanced"
							title={ __( 'Advanced', 'better-wp-security' ) }
							description={ __( 'Configure advanced Solid Security settings.', 'better-wp-security' ) }
						/>
					)
				}
			</Page>
			<Page
				id="secure-site"
				title={ __( 'Secure Site', 'better-wp-security' ) }
				priority={ 95 }
				roots={ [ 'onboard', 'import' ] }
				hideFromNav
			>
				{ () => <SecureSite /> }
			</Page>
			<Page
				id="summary"
				title={ __( 'Summary', 'better-wp-security' ) }
				priority={ 100 }
				roots={ [ 'onboard', 'import' ] }
				hideFromNav
			>
				{ () => <Summary /> }
			</Page>
		</>
	);
}

export { default as Onboard } from './onboard';
export { default as Settings } from './settings';
export { default as Import } from './import';
