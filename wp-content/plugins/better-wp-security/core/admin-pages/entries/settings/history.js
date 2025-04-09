/**
 * External dependencies
 */
import { createBrowserHistory, parsePath } from 'history';
import { parse, stringify } from 'query-string';
import { omit } from 'lodash';

/**
 * Recreate `history` to coerce React Router into accepting path arguments found in query
 * parameter `path`, allowing a url hash to be avoided. Since hash portions of the url are
 * not sent server side, full route information can be detected by the server.
 *
 * `<Router />` and `<Switch />` components use `history.location()` to match a url with a route.
 * Since they don't parse query arguments, recreate `get location` to return a `pathname` with the
 * query path argument's value.
 *
 * @param {Location} initialLocation The initial document.location.
 * @param {Object}   fixedQuery      The query parameters that should be fixed.
 *
 * @return {Object} React-router history object with `get location` modified.
 */
export function createHistory( initialLocation, fixedQuery ) {
	const browserHistory = createBrowserHistory();

	const getNextTo = ( to ) => {
		const parsed = typeof to === 'string' ? parsePath( to ) : to;
		const search = parse( parsed.search?.substring( 1 ) ) || {};

		return {
			...parsed,
			pathname: initialLocation.pathname,
			search:
				'?' +
				stringify( {
					...search,
					path: parsed.pathname,
					...fixedQuery,
				} ),
		};
	};

	return {
		get length() {
			return browserHistory.length;
		},
		get action() {
			return browserHistory.action;
		},
		get location() {
			const query = parse(
				browserHistory.location.search.substring( 1 )
			);
			const pathname = query.path || '/';

			return {
				...browserHistory.location,
				pathname,
				search:
					'?' +
					stringify(
						omit( query, [ 'path', Object.keys( fixedQuery ) ] )
					),
			};
		},
		createHref: ( location ) => {
			return browserHistory.createHref( getNextTo( location ) );
		},
		push: ( to, state ) => {
			browserHistory.push( getNextTo( to ), state );
		},
		replace: ( to, state ) => {
			browserHistory.replace( getNextTo( to ), state );
		},
		go: ( ...args ) => browserHistory.go.apply( browserHistory, args ),
		goBack: ( ...args ) =>
			browserHistory.goBack.apply( browserHistory, args ),
		goForward: ( ...args ) =>
			browserHistory.goForward.apply( browserHistory, args ),
		block: ( ...args ) =>
			browserHistory.block.apply( browserHistory, args ),
		listen( listener ) {
			return browserHistory.listen( () => {
				listener( this.location, this.action );
			} );
		},
	};
}
