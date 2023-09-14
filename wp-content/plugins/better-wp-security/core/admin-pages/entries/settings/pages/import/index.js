/**
 * External dependencies
 */
import { Redirect, Route, Switch, useRouteMatch } from 'react-router-dom';

/**
 * Internal dependencies
 */
import { NoticeList } from '@ithemes/security-components';
import { Main, Navigation, Sidebar } from '../../components';
import { usePages } from '../../page-registration';

export default function Import() {
	const pages = usePages();
	const { url, path } = useRouteMatch();

	if (
		pages.length > 0 &&
		! pages.find( ( page ) => page.id === 'select-export' )
	) {
		return <Redirect to="/" />;
	}

	return (
		<Switch>
			{ pages.map( ( { id, render: Component } ) => (
				<Route path={ `${ path }/:page(${ id })` } key={ id }>
					<Sidebar>
						<Navigation
							guided
							allowBack
							allowForward={ id !== 'select-export' }
						/>
					</Sidebar>
					<Main>
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
					<Navigation guided allowBack />
				</Sidebar>
				<Main />
			</Route>
		</Switch>
	);
}
