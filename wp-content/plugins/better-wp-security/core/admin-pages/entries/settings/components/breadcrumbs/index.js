/**
 * External dependencies
 */
import { Link, useLocation, useRouteMatch } from 'react-router-dom';
import { createPath } from 'history';
import { identity } from 'lodash';

/**
 * WordPress dependencies
 */
import { Dropdown, Button, NavigableMenu } from '@wordpress/components';
import { DOWN } from '@wordpress/keycodes';
import { useFocusOnMount } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';
import { useMemo } from '@wordpress/element';

/**
 * Internal dependencies
 */
import {
	useCurrentChildPages,
	useCurrentPage,
	usePages,
} from '../../page-registration';
import './style.scss';

export default function Breadcrumbs( { title, trail, match } ) {
	const localTrail = useBreadcrumbTrail( title, match );
	trail = trail || localTrail;

	if ( trail.length <= 1 ) {
		return null;
	}

	return (
		<nav
			className="itsec-breadcrumbs"
			aria-label={ __( 'Breadcrumbs', 'better-wp-security' ) }
		>
			<ul className="itsec-breadcrumbs">
				{ trail
					.map( ( crumb, i ) => {
						const current = i === trail.length - 1 ? 'page' : null;

						if ( crumb.to ) {
							return (
								<li key={ i }>
									<Link
										to={ crumb.to }
										aria-current={ current }
									>
										{ crumb.title }
									</Link>
								</li>
							);
						}

						if ( crumb.childPages ) {
							return (
								<li key={ i }>
									<Menu
										selected={ crumb.selected }
										childPages={ crumb.childPages }
										aria-current={ current }
									/>
								</li>
							);
						}

						return null;
					} )
					.filter( identity ) }
			</ul>
		</nav>
	);
}

export function useBreadcrumbTrail( title, match ) {
	const localMatch = useRouteMatch();
	const location = useLocation();
	const {
		url,
		params: { root, page: currentPageId, child },
	} = match || localMatch;

	const pages = usePages( { root } );
	let childPages = useCurrentChildPages();

	if ( useCurrentPage()?.id !== currentPageId ) {
		childPages = [];
	}

	const currentPage = pages.find( ( page ) => page.id === currentPageId );
	const currentChildPage = childPages.find( ( page ) => page.id === child );

	return useMemo( () => {
		const crumbs = [];
		crumbs.push( {
			title: currentPage.title,
			to: `/${ root }/${ currentPage.id }`,
		} );

		if ( currentChildPage && childPages.length ) {
			crumbs.push( {
				title: currentChildPage.title,
				selected: currentChildPage,
				childPages,
			} );
		}

		if (
			title &&
			title !== currentChildPage?.title &&
			title !== currentPage.title
		) {
			crumbs.push( {
				title,
				to: location.pathname.startsWith( url )
					? createPath( location )
					: url,
			} );
		}

		return crumbs;
	}, [ title, root, currentPage, currentChildPage, location ] );
}

export function useHelpBreadcrumbTrail( title ) {
	const { url } = useRouteMatch();
	const trail = useBreadcrumbTrail( __( 'Help', 'better-wp-security' ) );

	return useMemo( () => {
		const withModule = [ ...trail ];

		if ( title ) {
			withModule.splice( withModule.length - 1, 0, {
				to: url,
				title,
			} );
		}

		return withModule;
	}, [ trail, title ] );
}

function Menu( { childPages, selected, 'aria-current': ariaCurrent } ) {
	const focusRef = useFocusOnMount();

	return (
		<Dropdown
			className="itsec-breadcrumbs__menu"
			popoverProps={ {
				position: 'bottom center',
				className: 'itsec-breadcrumbs__menu-popover',
				focusOnMount: 'container',
			} }
			renderToggle={ ( { isOpen, onToggle } ) => {
				const openOnArrowDown = ( event ) => {
					if ( ! isOpen && event.keyCode === DOWN ) {
						event.preventDefault();
						event.stopPropagation();
						onToggle();
					}
				};

				return (
					<Button
						aria-haspopup
						aria-expanded={ isOpen }
						onClick={ onToggle }
						onKeyDown={ openOnArrowDown }
						text={ selected.title }
						icon={ isOpen ? 'arrow-up' : 'arrow-down' }
						iconPosition="right"
					/>
				);
			} }
			renderContent={ () => (
				<NavigableMenu role="menu">
					{ childPages.map( ( { id, to, title } ) => (
						<Link
							key={ to }
							to={ to }
							ref={ selected.id === id ? focusRef : null }
							aria-current={
								selected.id === id ? ariaCurrent : null
							}
						>
							{ title }
						</Link>
					) ) }
				</NavigableMenu>
			) }
		/>
	);
}
