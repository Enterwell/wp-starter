/**
 * External dependencies
 */
import { isArray } from 'lodash';
import scrollIntoView from 'scroll-into-view-if-needed';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { useMemo, useState, useRef } from '@wordpress/element';
import { DOWN, ENTER, LEFT, RIGHT, SPACE, UP } from '@wordpress/keycodes';
import { BaseControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './style.scss';

export function walkTree( tree, apply, parent = undefined ) {
	for ( let i = 0; i < tree.length; i++ ) {
		const sigil = apply( tree[ i ], parent, i );

		if ( sigil === walkTree.skip ) {
			continue;
		}

		if ( sigil === walkTree.halt ) {
			return sigil;
		}

		if ( isArray( tree[ i ].children ) ) {
			if (
				walkTree.halt ===
				walkTree( tree[ i ].children, apply, tree[ i ] )
			) {
				return walkTree.halt;
			}
		}
	}
}

walkTree.halt = Symbol( 'halt' );
walkTree.skip = Symbol( 'skip' );

function findPrevious( tree, before, expandedIds ) {
	let previous;

	walkTree( tree, ( item ) => {
		if ( item.id === before ) {
			return walkTree.halt;
		}

		previous = item;

		if ( item.children !== false && ! expandedIds.includes( item.id ) ) {
			return walkTree.skip;
		}
	} );

	return previous;
}

function findNext( tree, after, expandedIds ) {
	let next,
		found = false;

	walkTree( tree, ( item ) => {
		next = item;

		if ( found ) {
			return walkTree.halt;
		}

		if ( item.id === after ) {
			found = true;
		}

		if ( item.children !== false && ! expandedIds.includes( item.id ) ) {
			return walkTree.skip;
		}
	} );

	return next;
}

export default function Tree( {
	id,
	tree,
	active,
	setActive,
	onActivate,
	onLoad,
	label,
	help,
	...props
} ) {
	const treeRef = useRef();
	const lookup = useMemo( () => {
		const map = {};

		walkTree( tree, ( item, parent, index ) => {
			map[ item.id ] = {
				item,
				index,
				parent: parent?.id,
			};
		} );

		return map;
	}, [ tree ] );

	const [ expandedIds, setExpandedIds ] = useState( [] );
	const [ loadingIds, setLoadingIds ] = useState( [] );
	const idBase = id + '__item__';

	const onToggle = async ( item ) => {
		if ( item.children === true && onLoad ) {
			setLoadingIds( ( ids ) => [ ...ids, item.id ] );
			await onLoad( item.id );
			setLoadingIds( ( ids ) =>
				ids.filter( ( maybeId ) => maybeId !== item.id )
			);
		}

		setExpandedIds( ( state ) => {
			const isExpanded = state.includes( item.id );

			return isExpanded
				? state.filter( ( maybe ) => maybe !== item.id )
				: [ ...state, item.id ];
		} );
	};
	const onKeyDown = async ( e ) => {
		if ( props.onKeyDown ) {
			props.onKeyDown( e );
		}

		const { keyCode } = e;

		if ( onActivate && [ ENTER, SPACE ].includes( keyCode ) ) {
			onActivate( active );
		}

		if ( ! [ UP, DOWN, RIGHT, LEFT ].includes( keyCode ) ) {
			return;
		}

		e.stopPropagation();
		e.preventDefault();

		const found = lookup[ active ];

		if ( ! found ) {
			setActive( tree[ 0 ].id );
			return;
		}

		const { item, parent } = found;

		let next;

		switch ( keyCode ) {
			case UP: {
				next = findPrevious( tree, item.id, expandedIds )?.id;
				break;
			}
			case DOWN: {
				next = findNext( tree, item.id, expandedIds )?.id;
				break;
			}
			case RIGHT:
				if ( item.children ) {
					if ( expandedIds.includes( item.id ) ) {
						next = item.children?.[ 0 ].id;
					} else {
						await onToggle( item );
					}
				}
				break;
			case LEFT:
				if ( item.children && expandedIds.includes( item.id ) ) {
					await onToggle( item );
				} else {
					next = parent;
				}
				break;
		}

		if ( next ) {
			setActive( next );

			if ( treeRef.current ) {
				const nextEl = treeRef.current.ownerDocument.getElementById(
					idBase + next
				);

				if ( nextEl.scrollIntoViewIfNeeded ) {
					nextEl.scrollIntoViewIfNeeded();
				} else {
					scrollIntoView( nextEl, {
						scrollMode: 'if-needed',
					} );
				}
			}
		}
	};

	return (
		<BaseControl help={ help } className="itsec-tree">
			<span
				className="components-base-control__label"
				id={ id + '__tree_label' }
			>
				{ label }
			</span>
			<ul
				ref={ treeRef }
				id={ id }
				role="tree"
				tabIndex={ 0 }
				onKeyDown={ onKeyDown }
				onFocus={ active ? undefined : () => setActive( tree[ 0 ].id ) }
				aria-activedescendant={ active ? idBase + active : undefined }
				aria-labelledby={ id + '__tree_label' }
				{ ...props }
			>
				{ tree.map( ( item ) => (
					<TreeItem
						key={ item.id }
						idBase={ idBase }
						active={ active }
						setActive={ setActive }
						expandedIds={ expandedIds }
						onToggle={ onToggle }
						loadingIds={ loadingIds }
						item={ item }
					/>
				) ) }
			</ul>
		</BaseControl>
	);
}

function TreeItem( props ) {
	const {
		idBase,
		item,
		expandedIds,
		loadingIds,
		onToggle,
		active,
		setActive,
	} = props;

	const hasChildren = !! item.children;
	const isExpanded = expandedIds.includes( item.id );
	const onClick = async () => {
		await onToggle( item );
		setActive( item.id );
	};

	// Disable reason: Keyboard interaction is handled by the Tree.
	return (
		<li
			id={ idBase + item.id }
			role="treeitem"
			aria-selected={ active === item.id ? 'true' : undefined }
			aria-expanded={ hasChildren ? isExpanded : undefined }
			className={ classnames( 'itsec-tree__item', {
				'itsec-tree__item--loading': loadingIds.includes( item.id ),
			} ) }
		>
			{ /* eslint-disable-next-line jsx-a11y/click-events-have-key-events,jsx-a11y/no-static-element-interactions */ }
			<span onClick={ onClick } aria-label={ item.label }>
				{ item.label }
			</span>

			{ hasChildren && item.children.length > 0 && (
				<ul role="group">
					{ item.children.map( ( child ) => (
						<TreeItem
							key={ child.id }
							{ ...props }
							item={ child }
						/>
					) ) }
				</ul>
			) }
		</li>
	);
}
