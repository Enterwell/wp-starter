/**
 * External dependencies
 */
import styled from '@emotion/styled';
import { isEmpty, find } from 'lodash';
import memize from 'memize';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { compose, pure } from '@wordpress/compose';
import { useState, useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { dateI18n } from '@wordpress/date';
import { Button, Tooltip } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

/**
 * iThemes dependencies
 */
import { Heading, MasterDetail, MasterDetailBackButton, SearchControl, Surface, Text, TextSize, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { withDebounceHandler } from '@ithemes/security-hocs';
import { FlexSpacer } from '@ithemes/security-components';
import Header, { Title } from '../../components/card/header';
import Footer from '../../components/card/footer';
import { CardHappy } from '../../components/empty-states';
import Detail from './Detail';

export function ActiveLockout( { master } ) {
	return (
		<>
			<Tooltip text={ dateI18n( 'M d, Y g:s A', master.start_gmt ) }>
				<span>
					<Text
						as="time"
						size={ TextSize.SMALL }
						textTransform="capitalize"
						variant={ TextVariant.MUTED }
						text={ sprintf(
							/* translators: 1. Relative time from human_time_diff(). */
							__( '%s ago', 'better-wp-security' ),
							master.start_gmt_relative
						) }
					/>
				</span>
			</Tooltip>
			<Heading
				level={ 3 }
				size={ TextSize.NORMAL }
				variant={ TextVariant.DARK }
				weight={ TextWeight.HEAVY }
				text={ master.label }
			/>
			<Text variant={ TextVariant.DARK } text={ master.description } />
		</>
	);
}

const withLinks = memize( function( lockouts, links ) {
	return lockouts.map( ( lockout ) => ( {
		...lockout,
		links,
	} ) );
} );

/**
 * Hook that lets us manage releasing lockouts.
 *
 * @param {Object} card The Dashboard Card object.
 * @return {(number[]|(function(number): Promise<boolean>)|boolean)[]} A tuple of releasing ids, a callback to release a lockout and whether the feature is available.
 */
function useReleaseLockout( card ) {
	const [ releasingIds, setReleasingIds ] = useState( [] );
	const { createNotice, removeNotice } = useDispatch( 'core/notices' );

	const href = card._links[
		'ithemes-security:release-lockout'
	]?.[ 0 ].href;
	const isAvailable = !! href;
	const callback = useCallback( async ( lockoutId ) => {
		const url = href.replace( '{lockout_id}', lockoutId );
		const noticeId = `release-lockout-${ url }`;

		setReleasingIds( ( ids ) => [ ...ids, lockoutId ] );
		removeNotice( noticeId, 'ithemes-security' );

		try {
			await apiFetch( {
				url,
				method: 'DELETE',
			} );
			setTimeout( () => removeNotice( noticeId, 'ithemes-security' ), 5000 );
			createNotice(
				'success',
				__( 'Lockout Released', 'better-wp-security' ),
				{ id: noticeId, context: 'ithemes-security' }
			);

			return true;
		} catch ( e ) {
			createNotice(
				'error',
				sprintf(
					/* translators: 1. Error message */
					__( 'Error when releasing lockout: %s', 'better-wp-security' ),
					e.message || __( 'An unexpected error occurred.', 'better-wp-security' )
				),
				{ id: noticeId, context: 'ithemes-security' }
			);

			return false;
		} finally {
			setReleasingIds( ( ids ) => ids.filter( ( id ) => id !== lockoutId ) );
		}
	}, [ href, createNotice, removeNotice ] );

	return [ releasingIds, callback, isAvailable ];
}

/**
 * Hook that lets us create a lockout from ban.
 *
 * @param {Object} card The Dashboard Card object.
 * @return {(number[]|(function(number): Promise<boolean>)|boolean)[]} A tuple of banning ids, a callback to ban a lockout and whether the feature is available.
 */
function useBanLockout( card ) {
	const [ banningIds, setBanningIds ] = useState( [] );
	const { createNotice, removeNotice } = useDispatch( 'core/notices' );

	const href = card._links[ 'ithemes-security:ban-lockout' ]?.[ 0 ].href;
	const isAvailable = !! href;
	const callback = useCallback( async ( lockoutId ) => {
		const url = href.replace( '{lockout_id}', lockoutId );
		const noticeId = `ban-lockout-${ url }`;

		setBanningIds( ( ids ) => [ ...ids, lockoutId ] );
		removeNotice( noticeId, 'ithemes-security' );

		try {
			await apiFetch( {
				url,
				method: 'POST',
			} );
			setTimeout( () => removeNotice( noticeId, 'ithemes-security' ), 5000 );
			createNotice(
				'success',
				__( 'Ban Created', 'better-wp-security' ),
				{ id: noticeId, context: 'ithemes-security' }
			);

			return true;
		} catch ( e ) {
			createNotice(
				'error',
				sprintf(
					/* translators: 1. Error message */
					__( 'Error when banning lockout: %s', 'better-wp-security' ),
					e.message || __( 'An unexpected error occurred.', 'better-wp-security' )
				),
				{ id: noticeId, context: 'ithemes-security' }
			);

			return false;
		} finally {
			setBanningIds( ( ids ) => ids.filter( ( id ) => id !== lockoutId ) );
		}
	}, [ href, createNotice, removeNotice ] );

	return [ banningIds, callback, isAvailable ];
}

const StyledSurface = styled( Surface )`
	display: flex;
	flex-direction: column;
	overflow: hidden;
	height: 100%;
	position: relative;
`;

const StyledSearchContainer = styled.div`
	padding: 1rem;
`;

function ActiveLockouts( {
	card,
	config,
} ) {
	const [ banningIds, banLockout, isBanAvailable ] = useBanLockout( card );
	const [ releasingIds, releaseLockout, isReleaseAvailable ] = useReleaseLockout( card );
	const [ selectedId, setSelectedId ] = useState( 0 );
	const [ searchTerm, setSearchTerm ] = useState( '' );

	const { isQuerying } = useSelect(
		( select ) => ( {
			isQuerying: select( 'ithemes-security/dashboard' ).isQueryingDashboardCard( card.id ),
		} ),
		[ card.id ]
	);
	const { queryDashboardCard: query, refreshDashboardCard } = useDispatch( 'ithemes-security/dashboard' );
	const select = ( id ) => {
		return setSelectedId( id );
	};

	const onRelease = async ( e ) => {
		e.preventDefault();
		const released = await releaseLockout( selectedId );
		await refreshDashboardCard( card.id );

		if ( released ) {
			setSelectedId( ( currentSelectedId ) => currentSelectedId === selectedId ? 0 : currentSelectedId );
		}
	};

	const onBan = async ( e ) => {
		e.preventDefault();
		const banned = await banLockout( selectedId );
		await refreshDashboardCard( card.id );

		if ( banned ) {
			setSelectedId( ( currentSelectedId ) => currentSelectedId === selectedId ? 0 : currentSelectedId );
		}
	};

	const selectedLockout = find( card.data.lockouts, [ 'id', selectedId ] );
	const isBannable = selectedLockout?.bannable && isBanAvailable;
	return (
		<StyledSurface className="itsec-card--type-active-lockouts">
			<Header align="left">
				<MasterDetailBackButton isSinglePane onSelect={ select } selectedId={ selectedLockout?.id || 0 } />
				<Title card={ card } config={ config } />
			</Header>
			{ ! selectedLockout?.id && (
				<StyledSearchContainer>
					<SearchControl
						placeholder={ __( 'Search Lockouts', 'better-wp-security' ) }
						value={ searchTerm }
						onChange={ ( next ) => {
							setSearchTerm( next );
							query( card.id, next ? { search: next } : {} );
						} }
						isSearching={ isQuerying }
						size="small"
					/>
				</StyledSearchContainer>
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
				<MasterDetail
					masters={ withLinks( card.data.lockouts, card._links ) }
					getId={ ( lockout ) => lockout.id }
					isBorderless
					isSinglePane
					mode="list"
					renderMaster={ ( lockout ) => (
						<ActiveLockout master={ lockout } />
					) }
					onSelect={ select }
					selectedId={ selectedLockout?.id || 0 }
					renderDetail={ ( lockout, isVisible ) => (
						<Detail master={ lockout } isVisible={ isVisible } />
					) }
				/>
			) }
			{ selectedLockout?.id > 0 && ( isReleaseAvailable || isBannable ) && (
				<Footer>
					<FlexSpacer />
					{ isReleaseAvailable &&
						<span>
							<Button
								variant="primary"
								aria-disabled={ releasingIds.includes(
									selectedId
								) }
								isBusy={ releasingIds.includes( selectedId ) }
								onClick={ onRelease }
							>
								{ __( 'Release Lockout', 'better-wp-security' ) }
							</Button>
						</span>
					}
					{ isBannable &&
						<span>
							<Button
								variant="primary"
								aria-disabled={ banningIds.includes(
									selectedId
								) }
								isBusy={ banningIds.includes( selectedId ) }
								onClick={ onBan }
							>
								{ __( 'Ban', 'better-wp-security' ) }
							</Button>
						</span>
					}
				</Footer>
			) }
		</StyledSurface>
	);
}

export const slug = 'active-lockouts';
export const settings = {
	render: compose( [
		withDebounceHandler( 'query', 500, { leading: true } ),
		pure,
	] )( ActiveLockouts ),
};
