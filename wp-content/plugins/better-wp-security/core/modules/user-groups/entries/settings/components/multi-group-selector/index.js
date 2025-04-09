/**
 * External dependencies
 */
import { without } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { VisuallyHidden } from '@wordpress/components';
import { closeSmall as checkedIcon, plus as uncheckedIcon } from '@wordpress/icons';

/**
 * Solid dependencies
 */
import { SurfaceVariant, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { store as uiStore } from '@ithemes/security.user-groups.ui';
import { store as userGroupsStore } from '@ithemes/security.user-groups.api';
import {
	StyledGroupControl,
	StyledGroupControlInput,
	StyledGroupControlLabel,
	StyledGroupSelector,
} from './styles';

export default function MultiGroupSelector( { selected, setSelected } ) {
	const { matchables } = useSelect(
		( select ) => ( {
			matchables:
				select( userGroupsStore ).getMatchables() || [],
		} ),
		[]
	);

	return (
		<div className="itsec-user-groups-multi-group-selector">
			<StyledGroupSelector>
				<VisuallyHidden as="legend">
					{ __( 'User Groups', 'better-wp-security' ) }
				</VisuallyHidden>
				{ matchables.map( ( matchable ) => (
					<Group
						key={ matchable.id }
						id={ matchable.id }
						selected={ selected }
						setSelected={ setSelected }
					/>
				) ) }
			</StyledGroupSelector>
		</div>
	);
}

function Group( { id, selected, setSelected } ) {
	const { label } = useSelect(
		( select ) => ( {
			label: select( uiStore ).getEditedMatchableLabel( id ),
		} ),
		[ id ]
	);
	const onChange = ( checked ) => {
		if ( checked ) {
			setSelected( [ ...selected, id ], 'replaceIn' );
		} else {
			setSelected( without( selected, id ), 'replaceIn' );
		}
	};

	const checked = selected.includes( id );

	return (
		<StyledGroupControl variant={ checked ? SurfaceVariant.SECONDARY : SurfaceVariant.PRIMARY }>
			<StyledGroupControlInput
				type="checkbox"
				checked={ checked }
				onChange={ ( e ) => onChange( e.target.checked ) }
				id={ `itsec-multi-group-selector-group-${ id }` }
			/>
			<StyledGroupControlLabel
				text={ label }
				variant={ TextVariant.DARK }
				weight={ TextWeight.HEAVY }
				as="label"
				htmlFor={ `itsec-multi-group-selector-group-${ id }` }
				icon={ checked ? checkedIcon : uncheckedIcon }
				iconColor={ ! checked && '#6817C5' }
			/>
		</StyledGroupControl>
	);
}
