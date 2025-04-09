/**
 * External dependencies
 */
import { isEmpty, find } from 'lodash';
import memize from 'memize';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { compose, pure } from '@wordpress/compose';

/**
 * iThemes dependencies
 */
import { MasterDetailBackButton } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { withDebounceHandler } from '@ithemes/security-hocs';
import { CardHeader, CardHeaderTitle, CardHappy } from '@ithemes/security.dashboard.dashboard';
import {
	ActiveLockoutActions,
	List,
	Search,
	useActiveLockouts,
} from '@ithemes/security.core.active-lockouts';
import { StyledSurface } from './styles';

function ActiveLockouts( { card, config } ) {
	const {
		selectedId,
		searchTerm,
		setSearchTerm,
		isQuerying,
		query,
		select,
		getDetails,
		onBan,
		onRelease,
		isBanAvailable,
		isReleaseAvailable,
		releasingIds,
		banningIds,
	} = useActiveLockouts( card );

	const selectedLockout = find( card.data.lockouts, [ 'id', selectedId ] );
	const isBannable = selectedLockout?.bannable && isBanAvailable;
	return (
		<StyledSurface className="itsec-card--type-active-lockouts">
			<CardHeader align="left">
				<MasterDetailBackButton
					isSinglePane
					onSelect={ select }
					selectedId={ selectedLockout?.id || 0 }
				/>
				<CardHeaderTitle card={ card } config={ config } />
			</CardHeader>
			{ ! selectedLockout?.id && (
				<Search
					searchTerm={ searchTerm }
					setSearchTerm={ setSearchTerm }
					isQuerying={ isQuerying }
					query={ query }
					queryId={ card.id }
				/>
			) }
			{ isEmpty( card.data.lockouts ) ? (
				<CardHappy
					title={ __( 'All Clear!', 'better-wp-security' ) }
					text={ __(
						'No users are currently locked out of your site.',
						'better-wp-security'
					) }
				/>
			) : (
				<List
					lockouts={ withLinks( card.data.lockouts, card._links ) }
					select={ select }
					selectedLockout={ selectedLockout }
					fetchLockoutDetails={ getDetails }
				/>
			) }
			{ selectedLockout?.id > 0 && ( isReleaseAvailable || isBannable ) && (
				<ActiveLockoutActions
					isReleaseAvailable={ isReleaseAvailable }
					selectedId={ selectedId }
					releasingIds={ releasingIds }
					onRelease={ onRelease }
					isBannable={ isBannable }
					banningIds={ banningIds }
					onBan={ onBan }
				/>
			) }
		</StyledSurface>
	);
}

const withLinks = memize( function( lockouts, links ) {
	return lockouts.map( ( lockout ) => ( {
		...lockout,
		links,
	} ) );
} );

export const slug = 'active-lockouts';
export const settings = {
	render: compose( [
		withDebounceHandler( 'query', 500, { leading: true } ),
		pure,
	] )( ActiveLockouts ),
};
