/**
 * External dependencies
 */
import { capitalize } from 'lodash';

/**
 * WordPress dependencies
 */
import { Slot, Fill } from '@wordpress/components';

import './dashboard/store';

export function AdminBarFill( { type = 'secondary', ...props } ) {
	return <Fill name={ `AdminBar${ capitalize( type ) }` } { ...props } />;
}

export function AdminBarSlot( { type = 'secondary', ...props } ) {
	return <Slot name={ `AdminBar${ capitalize( type ) }` } { ...props } />;
}

export function BelowToolbarFill( props ) {
	return <Fill name="BelowToolbar" { ...props } />;
}

export function BelowToolbarSlot( props ) {
	return <Slot name="BelowToolbar" { ...props } />;
}

export function EditCardsFill( props ) {
	return <Fill name="EditCards" { ...props } />;
}

export function EditCardsSlot( props ) {
	return <Slot name="EditCards" { ...props } />;
}
