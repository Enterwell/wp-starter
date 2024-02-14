/**
 * External dependencies
 */
import { NavLink, useParams } from 'react-router-dom';
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { plus as createIcon } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';

/**
 * Solid dependencies
 */
import { SecondaryNavigation, SecondaryNavigationItem, Button } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { withNavigate } from '@ithemes/security-hocs';
import { store as uiStore } from '@ithemes/security.user-groups.ui';
import { useNavigateTo } from '@ithemes/security.pages.settings';

const StyledNavigation = styled( SecondaryNavigation )`
	padding: .5rem 1.5rem 1rem;
	align-items: center;
	flex-wrap: wrap;
`;

export default function GroupsNavigation() {
	const { navIds } = useSelect( ( select ) => ( {
		navIds: select( uiStore ).getMatchableNavIds(),
	} ), [] );
	const { createLocalGroup } = useDispatch( uiStore );
	const navigateTo = useNavigateTo();
	const { root } = useParams();

	const onCreate = async () => {
		const { id } = await createLocalGroup();
		navigateTo( `/${ root }/user-groups/${ id }` );
	};

	if ( ! navIds ) {
		return null;
	}

	return (
		<StyledNavigation orientation="horizontal">
			{ navIds.map( ( groupId ) => (
				<GroupNavItem key={ groupId } groupId={ groupId } root={ root } />
			) ) }
			<Button
				variant="tertiary"
				icon={ createIcon }
				label={ __( 'Create User Group', 'better-wp-security' ) }
				onClick={ onCreate }
			/>
		</StyledNavigation>
	);
}

function GroupNavItem( { groupId, root } ) {
	const { label } = useSelect( ( select ) => ( {
		label: select(
			uiStore
		).getEditedMatchableLabel( groupId ),
	} ), [ groupId ] );

	return (
		<NavLink
			to={ `/${ root }/user-groups/${ groupId }` }
			component={ withNavigate( SecondaryNavigationItem ) }
		>
			{ label || __( 'Untitled', 'better-wp-security' ) }
		</NavLink>
	);
}
