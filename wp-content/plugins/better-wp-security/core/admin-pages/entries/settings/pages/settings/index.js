/**
 * External dependencies
 */
import { Redirect, Route, Switch, useRouteMatch } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { createSlotFill } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { NoticeList } from '@ithemes/security-components';
import { usePages } from '../../page-registration';
import {
	Navigation,
	AdvancedNavigation,
	Main,
	Sidebar,
} from '../../components';

const { Fill: BeforeSettingsFill, Slot } = createSlotFill( 'BeforeSettings' );

export { BeforeSettingsFill };
export default function Settings() {
	const pages = usePages();
	const { url, path } = useRouteMatch();

	return (
		<Switch>
			{ pages.map( ( { id, render: Component } ) => (
				<Route path={ `${ path }/:page(${ id })` } key={ id }>
					<Sidebar>
						<Navigation />
						<AdvancedNavigation />
					</Sidebar>
					<Main>
						<Slot />
						<NoticeList />
						<Component />
					</Main>
				</Route>
			) ) }

			<Route path={ url }>
				{ pages.length > 0 && (
					<Redirect to={ `${ url }/${ pages[ 0 ].id }` } />
				) }
				<Sidebar>
					<Navigation />
					<AdvancedNavigation />
				</Sidebar>
				<Main />
			</Route>
		</Switch>
	);
}
