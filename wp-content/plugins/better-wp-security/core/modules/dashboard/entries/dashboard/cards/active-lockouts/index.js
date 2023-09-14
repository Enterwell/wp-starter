/**
 * External dependencies
 */
import { isEmpty, find } from 'lodash';
import memize from 'memize';
/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { compose, pure } from '@wordpress/compose';
import { Fragment, useState, useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { dateI18n } from '@wordpress/date';
import { Button } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

/**
 * iThemes dependencies
 */
import { SearchControl } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { withDebounceHandler } from '@ithemes/security-hocs';
import Header, { Title } from '../../components/card/header';
import Footer from '../../components/card/footer';
import MasterDetail, { Back } from '../../components/master-detail';
import { CardHappy } from '../../components/empty-states';
import Detail from './Detail';
import './style.scss';

function MasterRender( { master } ) {
	return (
		<Fragment>
			<time
				className="itsec-card-active-lockouts__start-time"
				dateTime={ master.start_gmt }
				title={ dateI18n( 'M d, Y g:s A', master.start_gmt ) }
			>
				{ sprintf(
					/* translators: 1. Relative time from human_time_diff(). */
					__( '%s ago', 'better-wp-security' ),
					master.start_gmt_relative
				) }
			</time>
			<h3 className="itsec-card-active-lockouts__label">
				{ master.label }
			</h3>
			<p className="itsec-card-active-lockouts__description">
				{ master.description }
			</p>
		</Fragment>
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

	const isSmall = true;

	const selectedLockout = find( card.data.lockouts, [ 'id', selectedId ] );
	const isBannable = selectedLockout?.bannable && isBanAvailable;

	return (
		<div className="itsec-card--type-active-lockouts">
			<Header>
				<Back
					isSmall={ isSmall }
					select={ select }
					selectedId={ selectedLockout?.id || 0 }
				/>
				<Title card={ card } config={ config } />
			</Header>
			{ ! selectedLockout?.id && (
				<div className="itsec-card-active-lockouts__search-container">
					<SearchControl
						value={ searchTerm }
						onChange={ ( next ) => {
							setSearchTerm( next );
							query( card.id, next ? { search: next } : {} );
						} }
						placeholder={ __( 'Search Lockouts', 'better-wp-security' ) }
						isSearching={ isQuerying }
						surfaceVariant="secondary"
					/>
				</div>
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
					detailRender={ Detail }
					masterRender={ MasterRender }
					mode="list"
					selectedId={ selectedLockout?.id || 0 }
					select={ select }
					isSmall={ isSmall }
				/>
			) }
			{ selectedLockout?.id > 0 && ( isReleaseAvailable || isBannable ) && (
				<Footer>
					{ isReleaseAvailable &&
						<span className="itsec-card-footer__action">
							<Button
								variant="primary"
								isSmall
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
						<span className="itsec-card-footer__action">
							<Button
								variant="primary"
								isSmall
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
		</div>
	);
}

export const slug = 'active-lockouts';
export const settings = {
	render: compose( [
		withDebounceHandler( 'query', 500, { leading: true } ),
		pure,
	] )( ActiveLockouts ),
	elementQueries: [
		{
			type: 'width',
			dir: 'max',
			px: 500,
		},
	],
};
