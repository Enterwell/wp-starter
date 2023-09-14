/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import { ToggleControl } from '@wordpress/components';

function Field( {
	definition,
	value,
	edit,
	className,
	isHighlighted,
	disabled = false,
} ) {
	return (
		<ToggleControl
			className={ classnames( className, {
				'itsec-highlighted-search-result': isHighlighted,
			} ) }
			checked={ value === true }
			label={ definition.title }
			help={ definition.description }
			disabled={ disabled }
			onChange={ ( checked ) => edit( checked ) }
		/>
	);
}

export default compose( [
	withSelect( ( select, { groupId, module, setting } ) => ( {
		value: select(
			'ithemes-security/user-groups-editor'
		).getEditedGroupSetting( groupId, module, setting ),
	} ) ),
	withDispatch( ( dispatch, { groupId, module, setting } ) => ( {
		edit( value ) {
			return dispatch(
				'ithemes-security/user-groups-editor'
			).editGroupSetting( groupId, module, setting, value );
		},
	} ) ),
] )( Field );
