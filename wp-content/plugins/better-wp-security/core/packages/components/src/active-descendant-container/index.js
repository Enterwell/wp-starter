/**
 * External dependencies
 */
import { includes, findIndex, noop } from 'lodash';

/**
 * WordPress dependencies
 */
import { useRef, forwardRef, useImperativeHandle } from '@wordpress/element';
import { DOWN, LEFT, RIGHT, UP } from '@wordpress/keycodes';

function cycleValue( value, total, offset ) {
	const nextValue = value + offset;
	if ( nextValue < 0 ) {
		return total + nextValue;
	} else if ( nextValue >= total ) {
		return nextValue - total;
	}

	return nextValue;
}

function eventToOffset( keyCode, orientation ) {
	let next = [ DOWN ];
	let previous = [ UP ];

	if ( orientation === 'horizontal' ) {
		next = [ RIGHT ];
		previous = [ LEFT ];
	}

	if ( orientation === 'both' ) {
		next = [ RIGHT, DOWN ];
		previous = [ LEFT, UP ];
	}

	if ( includes( next, keyCode ) ) {
		return 1;
	} else if ( includes( previous, keyCode ) ) {
		return -1;
	} else if ( includes( [ DOWN, UP, LEFT, RIGHT ], keyCode ) ) {
		// Key press should be handled, e.g. have event propagation and
		// default behavior handled by NavigableContainer but not result
		// in an offset.
		return 0;
	}
}

function queryDescendants( container, roles ) {
	const selector = roles
		.split( ' ' )
		.map( ( role ) => `[role="${ role }"]` )
		.join( ', ' );

	return container.querySelectorAll( selector );
}

function scrollIntoView( parent, element ) {
	if ( element.scrollIntoViewIfNeeded ) {
		element.scrollIntoViewIfNeeded();
		return;
	}

	const parentComputedStyle = parent.ownerDocument.defaultView.getComputedStyle(
			parent
		),
		parentBorderTopWidth = parseInt(
			parentComputedStyle.getPropertyValue( 'border-top-width' )
		),
		overTop = element.offsetTop - parent.offsetTop < parent.scrollTop,
		overBottom =
			element.offsetTop -
				parent.offsetTop +
				element.clientHeight -
				parentBorderTopWidth >
			parent.scrollTop + parent.clientHeight;

	if ( overTop || overBottom ) {
		parent.scrollTop =
			element.offsetTop -
			parent.offsetTop -
			( parent.clientHeight / 2 ) -
			parentBorderTopWidth +
			( element.clientHeight / 2 );
		parent.scrollLeft = 0;
	}
}

const ROLES = [ 'group', 'treeitem', 'option', 'menuitem', 'tab' ].join( ' ' );

function ActiveDescendantContainer(
	{
		active,
		onKeyDown = noop,
		onNavigate,
		orientation = 'vertical',
		cycle = true,
		descendantRoles = ROLES,
		as: Component = 'div',
		children,
		...props
	},
	ref
) {
	const containerRef = useRef();
	useImperativeHandle( ref, () => ( {
		focus() {
			containerRef.current.focus();
		},
	} ) );

	const keyDown = ( e ) => {
		const offset = eventToOffset( e.keyCode, orientation );
		onKeyDown( e, offset );

		if ( offset === undefined ) {
			return;
		}

		e.stopPropagation();
		e.preventDefault();

		const descendants = queryDescendants(
			containerRef.current,
			descendantRoles
		);
		const current = findIndex( descendants, { id: active } );

		const next = cycle
			? cycleValue( current, descendants.length, offset )
			: current + offset;

		if ( next >= 0 && next < descendants.length ) {
			const nextElement = descendants[ next ];
			scrollIntoView( containerRef.current, nextElement );
			onNavigate( nextElement.id );
		}
	};

	return (
		// Disable Reason: role is passed by the parent
		// eslint-disable-next-line jsx-a11y/no-static-element-interactions
		<Component
			ref={ containerRef }
			tabIndex={ 0 }
			aria-activedescendant={ active }
			aria-orientation={ orientation }
			onKeyDown={ keyDown }
			{ ...props }
		>
			{ children }
		</Component>
	);
}

export default forwardRef( ActiveDescendantContainer );
