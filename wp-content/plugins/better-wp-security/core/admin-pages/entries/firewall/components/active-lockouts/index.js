/**
 * External dependencies
 */
import { find, isEmpty } from 'lodash';

/**
 * WordPress dependencies
 */
import { Flex, FlexBlock } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

/**
 * SolidWP dependencies
 */
import { PageHeader, Surface } from '@ithemes/ui';

/**
 * Internal  dependencies
 */
import {
	List as LockoutsList,
	ActiveLockoutActions,
} from '@ithemes/security.core.active-lockouts';
import { modulesStore } from '@ithemes/security.packages.data';
import LockoutsAllClear from './all-clear';
import Search from './search';
import LockoutsError from './list-error';
import useActiveLockouts from './use-active-lockouts';
import { StyledActiveLockoutsContainer, StyledMasterDetailBackButton } from './styles';

export default function ActiveLockouts() {
	const {
		selectedId,
		searchTerm,
		setSearchTerm,
		banningIds,
		releasingIds,
		lockouts,
		getLockoutsError,
		isQuerying,
		select,
		getDetails,
		onBan,
		onRelease,
	} = useActiveLockouts( 'firewall' );
	const { banUsersActive } = useSelect( ( activeSelect ) => ( {
		banUsersActive: activeSelect( modulesStore ).isActive( 'ban-users' ),
	} ), [] );

	const selectedLockout = find( lockouts, [ 'id', selectedId ] );

	const isBannable = selectedLockout?.bannable && banUsersActive;
	const isReleaseAvailable = selectedLockout?.active;
	return (
		<FlexBlock>
			<Surface>
				<PageHeader
					title={ __( 'Active Lockouts', 'better-wp-security' ) }
					description={ __( 'View, ban, or release lockout out users and IP addresses.', 'better-wp-security' ) }
					fullWidth
					hasBorder
				/>
				<FlexBlock>
					<Flex><StyledMasterDetailBackButton isSinglePane onSelect={ select } selectedId={ selectedLockout?.id || 0 } /></Flex>
				</FlexBlock>
				{ ! selectedLockout?.id && (
					<Search
						searchTerm={ searchTerm }
						setSearchTerm={ setSearchTerm }
						isQuerying={ isQuerying }
					/>
				) }
				{ ! isEmpty( getLockoutsError ) && (
					<LockoutsError error={ getLockoutsError } />
				) }
				{ isEmpty( lockouts ) ? (
					<LockoutsAllClear />
				) : (
					<StyledActiveLockoutsContainer>
						<LockoutsList
							lockouts={ lockouts }
							select={ select }
							selectedLockout={ selectedLockout }
							fetchLockoutDetails={ getDetails }
						/>
					</StyledActiveLockoutsContainer>
				) }
				{ selectedLockout?.id > 0 && ( isReleaseAvailable || isBannable ) && (
					<ActiveLockoutActions
						isReleaseAvailable={ isReleaseAvailable }
						selectedId={ selectedLockout }
						releasingIds={ releasingIds }
						banningIds={ banningIds }
						onRelease={ onRelease }
						onBan={ onBan }
					/>
				) }
			</Surface>
		</FlexBlock>
	);
}
