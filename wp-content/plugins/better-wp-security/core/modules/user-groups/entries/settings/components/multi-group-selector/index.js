/**
 * External dependencies
 */
import { useParams } from 'react-router-dom';
import { useQueryParam, ArrayParam, withDefault } from 'use-query-params';
import { without } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { CheckboxControl, VisuallyHidden } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { useNavigateTo } from '@ithemes/security.pages.settings';
import { ChipControl, HelpPopover } from '@ithemes/security-components';
import './style.scss';

export default function MultiGroupSelector() {
	const [ selected, setSelected ] = useQueryParam(
		'id',
		withDefault( ArrayParam, [] )
	);
	const [ back ] = useQueryParam( 'back' );
	const { root, child: groupId } = useParams();
	const navigateTo = useNavigateTo();
	const { matchables, isLocal } = useSelect(
		( select ) => ( {
			matchables:
				select( 'ithemes-security/user-groups' ).getMatchables() || [],
			isLocal:
				groupId &&
				select( 'ithemes-security/user-groups-editor' ).isLocalGroup(
					groupId
				),
		} ),
		[ groupId ]
	);

	if ( 'settings' !== root ) {
		return null;
	}

	const onChange = ( checked ) => {
		if ( checked ) {
			navigateTo(
				`/${ root }/user-groups/multi?id=${ groupId }&back=${ groupId }`,
				'replace'
			);
		} else if ( back ) {
			navigateTo( `/${ root }/user-groups/${ back }`, 'replace' );
		} else {
			navigateTo( `/${ root }/user-groups`, 'replace' );
		}
	};

	const onSelectAll = ( checked ) => {
		if ( checked ) {
			setSelected( matchables.map( ( matchable ) => matchable.id ) );
		} else {
			setSelected( [] );
		}
	};

	let label = __( 'Select multiple User Groups to edit together.', 'better-wp-security' );

	if ( isLocal ) {
		label = (
			<>
				{ label }
				<HelpPopover
					help={ __(
						'Save the User Group first to enable multi-editing.',
						'better-wp-security'
					) }
				/>
			</>
		);
	}

	return (
		<div className="itsec-user-groups-multi-group-selector">
			<CheckboxControl
				label={ label }
				onChange={ onChange }
				checked={ ! groupId }
				disabled={ isLocal }
			/>
			{ ! groupId && (
				<fieldset>
					<VisuallyHidden as="legend">
						{ __( 'User Groups', 'better-wp-security' ) }
					</VisuallyHidden>
					<ChipControl
						label={ __( 'Select All', 'better-wp-security' ) }
						checked={ selected.length === matchables.length }
						onChange={ onSelectAll }
						className="itsec-user-groups-multi-group-selector__select-all"
					/>
					{ matchables.map( ( matchable ) => (
						<Group
							key={ matchable.id }
							id={ matchable.id }
							selected={ selected }
							setSelected={ setSelected }
						/>
					) ) }
				</fieldset>
			) }
		</div>
	);
}

function Group( { id, selected, setSelected } ) {
	const { label } = useSelect(
		( select ) => ( {
			label: select(
				'ithemes-security/user-groups-editor'
			).getEditedMatchableLabel( id ),
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

	return (
		<ChipControl
			label={ label }
			checked={ selected.includes( id ) }
			onChange={ onChange }
		/>
	);
}
