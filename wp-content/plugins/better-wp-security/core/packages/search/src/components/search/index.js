/**
 * External dependencies
 */
import { Link, useHistory } from 'react-router-dom';
import { createLocation } from 'history';
import { isEmpty, map, noop } from 'lodash';

/**
 * WordPress dependencies
 */
import { VisuallyHidden } from '@wordpress/components';
import { __, _n, sprintf } from '@wordpress/i18n';
import {
	useState,
	useCallback,
	useRef,
	forwardRef,
	useImperativeHandle,
} from '@wordpress/element';
import { useRegistry } from '@wordpress/data';
import {
	useInstanceId,
	useDebounce,
	useKeyboardShortcut,
} from '@wordpress/compose';
import { ENTER, SPACE, DOWN } from '@wordpress/keycodes';
import { speak } from '@wordpress/a11y';

/**
 * iThemes dependencies
 */
import { SearchControl } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { useFocusOutside, useMergeRefs } from '@ithemes/security-hocs';
import {
	ActiveDescendantContainer,
	Markup,
} from '@ithemes/security-components';
import Engine from '../../engine';
import './style.scss';

export default forwardRef( function Search(
	{ onPick = noop, showResults },
	ref
) {
	// UI State
	const [ query, setQuery ] = useState( '' );
	const [ isSearching, setIsSearching ] = useState( false );
	const searchRef = useRef();
	const resultsRef = useRef();

	// Results handling
	const [ results, setResults ] = useState( [] );
	const registry = useRegistry();
	const search = useCallback(
		( searchQuery ) => {
			const searchResults = new Engine(
				searchQuery,
				registry
			).getResults();
			setResults( searchResults[ 0 ] );
			speak(
				sprintf(
					/* translators: 1. Number of results. */
					_n(
						'%d result found.',
						'%d results found.',
						searchResults[ 1 ],
						'better-wp-security'
					),
					searchResults[ 1 ]
				)
			);
		},
		[ registry ]
	);
	const searchDebounced = useDebounce( search, 50 );

	// Event handlers
	const onChange = ( nextQuery ) => {
		setQuery( nextQuery );
		searchDebounced( nextQuery );
	};
	const onKeyDown = ( e ) => {
		if ( e.keyCode === DOWN ) {
			e.preventDefault();
			resultsRef.current.focus();
		}
	};
	const onSlash = useCallback(
		( e ) => {
			if ( searchRef.current ) {
				e.preventDefault();
				searchRef.current.focus();
			}
		},
		[ searchRef ]
	);

	useKeyboardShortcut( '/', onSlash );

	return (
		<div
			className="itsec-search"
			{ ...useFocusOutside( () => setIsSearching( false ) ) }
		>
			<div>
				<SearchControl
					value={ query }
					onChange={ onChange }
					onFocus={ () => setIsSearching( true ) }
					onKeyDown={ onKeyDown }
					ref={ useMergeRefs( [ ref, searchRef ] ) }
					placeholder={ __( 'Search for features, settings, and more', 'better-wp-security' ) }
					omitSeparators
					size="large"
				/>
				{ ( isSearching || showResults ) && query.length >= 3 && (
					<SearchResults
						results={ results }
						exitSearch={ ( result ) => {
							onPick( result );
							setIsSearching( false );
						} }
						ref={ resultsRef }
						onPick={ onPick }
					/>
				) }
			</div>
		</div>
	);
} );

const SearchResults = forwardRef( function(
	{ results, exitSearch, onPick },
	ref
) {
	const containerRef = useRef();
	useImperativeHandle( ref, () => ( {
		focus() {
			containerRef.current.focus();
		},
	} ) );
	const id = useInstanceId( SearchResults, 'itsec-search' );
	const idPrefix = id + '__result__';

	const navigateTo = useNavigateTo();
	const [ active, setActive ] = useState( '' );

	const onKeyDown = ( { keyCode } ) => {
		if ( active && ( keyCode === ENTER || keyCode === SPACE ) ) {
			onPick( active );
			navigateTo( active );
			exitSearch();
		}
	};
	const onFocus = () => {
		if ( ! active && ! isEmpty( results ) ) {
			const [ , firstKind ] = Object.entries( results )[ 0 ];
			if ( firstKind.items?.length ) {
				setActive( firstKind.items[ 0 ].route );
			} else if ( ! isEmpty( firstKind.groups ) ) {
				const [ , firstGroup ] = Object.entries(
					firstKind.groups
				)[ 0 ];
				setActive( firstGroup.items[ 0 ].route );
			}
		}
	};

	if ( isEmpty( results ) ) {
		return null;
	}

	return (
		<>
			<VisuallyHidden id={ id + '__label' }>
				{ __( 'Search Results', 'better-wp-security' ) }
			</VisuallyHidden>
			<ActiveDescendantContainer
				className="itsec-search__results"
				id={ id }
				active={ active && idPrefix + active }
				onNavigate={ ( result ) =>
					setActive( result.substring( idPrefix.length ) )
				}
				onKeyDown={ onKeyDown }
				onFocus={ onFocus }
				role="listbox"
				descendantRoles="option"
				ref={ containerRef }
				aria-labelledby={ id + '__label' }
			>
				{ map( results, ( kind, slug ) => (
					<KindResults
						key={ slug }
						{ ...kind }
						active={ active }
						idPrefix={ idPrefix }
						exitSearch={ exitSearch }
					/>
				) ) }
			</ActiveDescendantContainer>
		</>
	);
} );

function KindResults( { title, items, groups, ...rest } ) {
	const id = useInstanceId( SearchResults, 'itsec-search__kind' );

	return (
		<ul className="itsec-search__kind" role="group" aria-labelledby={ id }>
			<li role="presentation" id={ id }>
				{ title }
			</li>
			{ ( items || [] ).map( ( item ) => (
				<Result key={ item.route } { ...item } { ...rest } />
			) ) }
			{ map( groups, ( group, slug ) => (
				<GroupResults key={ slug } { ...group } { ...rest } />
			) ) }
		</ul>
	);
}

function GroupResults( { title, items, ...rest } ) {
	const id = useInstanceId( SearchResults, 'itsec-search__group' );

	return (
		<ul className="itsec-search__group" role="group" aria-labelledby={ id }>
			<li role="presentation" id={ id }>
				<span>{ title }</span>
			</li>
			{ ( items || [] ).map( ( item ) => (
				<Result key={ item.route } { ...item } { ...rest } />
			) ) }
		</ul>
	);
}

function Result( { title, description, route, active, idPrefix, exitSearch } ) {
	return (
		<li
			className="itsec-search__result"
			role="option"
			aria-selected={ active === route ? true : undefined }
			id={ idPrefix + route }
			aria-label={ title }
		>
			<Link
				to={ route }
				tabIndex={ -1 }
				onClick={ () => exitSearch( route ) }
			>
				<span>{ title }</span>
				<Markup content={ description } noHtml tagName="p" />
			</Link>
		</li>
	);
}

function useNavigateTo() {
	const history = useHistory();

	return ( route, mode = 'push' ) =>
		history[ mode ]( createLocation( route ) );
}
