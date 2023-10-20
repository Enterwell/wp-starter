/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { ToggleControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import SettingField from '../setting-field';
import store from '../../store';

export default function Field( {
	groupId,
	module,
	setting,
	definition,
	className,
	isHighlighted,
	disabled = false,
} ) {
	const { value } = useSelect( ( select ) => ( {
		value: select( store ).getEditedGroupSetting( groupId, module, setting ),
	} ), [ groupId, module, setting ] );
	const { editGroupSetting } = useDispatch( store );

	return (
		<SettingField definition={ definition }>
			<ToggleControl
				className={ classnames( className, {
					'itsec-highlighted-search-result': isHighlighted,
				} ) }
				checked={ value === true }
				label={ definition.title }
				disabled={ disabled }
				onChange={ ( checked ) => editGroupSetting( groupId, module, setting, checked ) }
				__nextHasNoMarginBottom
			/>
		</SettingField>
	);
}

