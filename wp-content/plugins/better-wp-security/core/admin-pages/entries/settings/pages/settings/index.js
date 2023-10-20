/**
 * External dependencies
 */
import { Redirect, Route, Switch, useRouteMatch } from 'react-router-dom';

/**
 * Internal dependencies
 */
import { ToolbarFill } from '@ithemes/security-ui';
import { Search } from '@ithemes/security-search';
import { usePages } from '../../page-registration';
import {
	Main,
	NoticeList,
} from '../../components';

export default function Settings() {
	const pages = usePages();
	const { url, path } = useRouteMatch();

	return (
		<>
			<ToolbarFill area="main">
				<Search />
			</ToolbarFill>
			<Switch>
				{ pages.map( ( { id, render: Component } ) => (
					<Route path={ `${ path }/:page(${ id })` } key={ id }>
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
					<Main />
				</Route>
			</Switch>
		</>
	);
}
