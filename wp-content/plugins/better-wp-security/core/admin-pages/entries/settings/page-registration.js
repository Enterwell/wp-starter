/**
 * External dependencies
 */
import { sortBy, omit, flatMap } from 'lodash';
import { useHistory, useParams } from 'react-router-dom';
import { createLocation } from 'history';
import useDeepCompareEffect from 'use-deep-compare-effect';

/**
 * WordPress dependencies
 */
import {
	createContext,
	useCallback,
	useContext,
	useEffect,
	useState,
	useMemo,
} from '@wordpress/element';
import { useInstanceId } from '@wordpress/compose';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { CORE_STORE_NAME } from '@ithemes/security.packages.data';

const Context = createContext( {
	pages: [],
	childPages: {},
	addPage: () => {},
	removePage: () => {},
	addChildPages: () => {},
	removeChildPages: () => {},
} );
Context.displayName = 'PageRegistration';

export { Context };

export default function PageRegistration( { children } ) {
	const [ pages, setPages ] = useState( [] );
	const [ childPages, setChildPages ] = useState( {} );

	const addPage = useCallback( ( page ) => {
		setPages( ( latestPages ) => {
			const i = latestPages.findIndex(
				( maybe ) => maybe.id === page.id
			);
			let next;

			if ( i === -1 ) {
				next = [ ...latestPages, page ];
			} else {
				next = [ ...latestPages ];
				next[ i ] = page;
			}

			return sortBy( next, 'priority' );
		} );
	}, [] );

	const removePage = useCallback( ( id ) => {
		setPages( ( latestPages ) =>
			latestPages.filter( ( page ) => page.id !== id )
		);
	}, [] );

	const addChildPages = useCallback( ( id, newChildPages ) => {
		setChildPages( ( latestPages ) => ( {
			...latestPages,
			[ id ]: newChildPages,
		} ) );
	}, [ setChildPages ] );
	const removeChildPages = useCallback( ( id ) => {
		setChildPages( ( latestPages ) => omit( latestPages, id ) );
	}, [ setChildPages ] );

	return (
		<Context.Provider
			value={ {
				pages,
				childPages,
				addPage,
				removePage,
				addChildPages,
				removeChildPages,
			} }
		>
			{ children }
		</Context.Provider>
	);
}

export function Page( {
	id,
	title,
	icon,
	roots = [ 'settings' ],
	priority = 90,
	location = 'primary',
	featureFlag,
	ignore,
	hideFromNav,
	children,
} ) {
	const context = useContext( Context );

	useEffect( () => {
		context.addPage( {
			id,
			title,
			icon,
			roots,
			priority,
			location,
			featureFlag,
			ignore,
			hideFromNav,
			render: children,
		} );

		return () => {
			context.removePage( id );
		};
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ id, title ] );

	return null;
}

/**
 * Register child pages.
 *
 * @param {Object}                 props       Props.
 * @param {Array<{title, id, to}>} props.pages The pages to register.
 * @return {null} No component rendered.
 */
export function ChildPages( props ) {
	const { pages } = props;
	const context = useContext( Context );
	const id = useInstanceId( ChildPages, '' );

	useDeepCompareEffect( () => {
		context.addChildPages( id, pages );
		return () => {
			context.removeChildPages( id );
		};
	}, [ pages ] );

	return null;
}

export function usePages( { root, location } = {} ) {
	const { featureFlags } = useSelect(
		( select ) => ( {
			featureFlags: select( CORE_STORE_NAME ).getFeatureFlags(),
		} ),
		[]
	);
	const { root: matchedRoot } = useParams();
	const { pages } = useContext( Context );

	return pages.filter(
		( page ) =>
			page.roots.includes( root || matchedRoot ) &&
			( ! location || page.location === location ) &&
			( ! page.featureFlag || featureFlags.includes( page.featureFlag ) )
	);
}

export function useCurrentPage() {
	const pages = usePages();
	const { page: currentPage } = useParams();

	return pages.find( ( page ) => page.id === currentPage );
}

export function useCurrentChildPages() {
	const { childPages } = useContext( Context );

	return useMemo( () => flatMap( childPages ), [ childPages ] );
}

export function usePreviousPage( currentPage ) {
	const pages = usePages();

	if ( ! pages.length ) {
		return undefined;
	}

	if ( ! currentPage ) {
		return undefined;
	}

	const index = pages.findIndex( ( page ) => page.id === currentPage );

	return pages[ index - 1 ]?.id;
}

export function useNextPage( currentPage ) {
	const pages = usePages();

	if ( ! pages.length ) {
		return undefined;
	}

	if ( ! currentPage ) {
		return pages[ 0 ];
	}

	const index = pages.findIndex( ( page ) => page.id === currentPage );

	return pages[ index + 1 ]?.id;
}

/**
 * Gets navigation helpers based on the current page.
 *
 * @param {Array<string>} [tabs] Ordered list of tab ids.
 * @return {Object} An object with slugs and goto functions for the previous and next pages.
 */
export function useNavigation( tabs ) {
	const {
		root: base,
		page: currentPage,
		child: currentChildPage,
		tab: currentTab,
	} = useParams();
	const nextPage = useNextPage( currentPage );
	const childPages = useCurrentChildPages().map(
		( childPage ) => childPage.id
	);
	const history = useHistory();

	let previous, next;

	if ( tabs ) {
		let prevTab, nextTab;

		for ( let i = 0; i < tabs.length; i++ ) {
			if ( tabs[ i ] === currentTab ) {
				prevTab = tabs[ i - 1 ];
				nextTab = tabs[ i + 1 ];
				break;
			}
		}

		previous =
			prevTab &&
			`/${ base }/${ currentPage }/${ currentChildPage }/${ prevTab }`;
		next =
			nextTab &&
			`/${ base }/${ currentPage }/${ currentChildPage }/${ nextTab }`;
	}

	if ( ( ! previous || ! next ) && childPages ) {
		let prevChild, nextChild;

		for ( let i = 0; i < childPages.length; i++ ) {
			if ( childPages[ i ] === currentChildPage ) {
				prevChild = childPages[ i - 1 ];
				nextChild = childPages[ i + 1 ];
				break;
			}
		}

		previous =
			previous ||
			( prevChild && `/${ base }/${ currentPage }/${ prevChild }` );
		next =
			next ||
			( nextChild && `/${ base }/${ currentPage }/${ nextChild }` );
	}

	if ( ! next && nextPage ) {
		next = `/${ base }/${ nextPage }`;
	}

	return {
		previous,
		goPrevious() {
			if ( previous ) {
				history.push( createLocation( previous ) );
			}
		},
		next,
		goNext() {
			if ( next ) {
				history.push( createLocation( next ) );
			}
		},
		nextPage: nextPage && `/${ base }/${ nextPage }`,
		goNextPage() {
			if ( nextPage ) {
				history.push( createLocation( `/${ base }/${ nextPage }` ) );
			}
		},
	};
}
