/**
 * External dependencies
 */
import { groupBy, map } from 'lodash';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useCallback } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { useSelect } from '@wordpress/data';
import { addQueryArgs } from '@wordpress/url';

/**
 * Solid dependencies
 */
import { Notice } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { Markup } from '@ithemes/security-ui';
import { useGlobalNavigationUrl, getFlagEmoji } from '@ithemes/security-utils';
import { useAsync } from '@ithemes/security-hocs';
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import DataList, { DataListDescription, DataListEmptyState, DataListGroup, DataListItem } from '../data-list';

export default function TopBlockedIPs( { period } ) {
	const { hasGeolocation } = useSelect( ( select ) => ( {
		hasGeolocation: select( MODULES_STORE_NAME ).getModule( 'geolocation' )?.status.selected === 'active',
	} ), [] );
	const fetchBlockedIps = useCallback( () => apiFetch( {
		path: addQueryArgs( '/ithemes-security/rpc/lockout-stats/top-blocked-ips', { period } ),
	} ), [ period ] );

	const { status, value, error } = useAsync( fetchBlockedIps );
	const settingsUrl = useGlobalNavigationUrl( 'settings', '/settings/configure/lockout' );

	return (
		<DataList
			title={ __( 'Top Blocked IPs', 'better-wp-security' ) }
		>
			{ status === 'error' && (
				<Notice
					type="danger"
					text={ sprintf(
					/* translators: The error message */
						__( 'An error has occurred: %s', 'better-wp-security' ),
						error.message
					) }
				/>
			) }

			{ hasGeolocation && ( status === 'success' || status === 'pending' ) && (
				<GeolocatedIps data={ value } />
			) }
			{ ! hasGeolocation && ( status === 'success' || status === 'pending' ) && (
				<NoLocationIps data={ value } />
			) }
			{ value?.length === 0 && status === 'success' && (
				<DataListEmptyState
					title={ __( 'No IPs have been locked out recently', 'better-wp-security' ) }
					description={ __( 'Consider customizing your firewall settings.', 'better-wp-security' ) }
					actionText={ __( 'Firewall Settings', 'better-wp-security' ) }
					actionLink={ settingsUrl }
				/>
			) }
		</DataList>
	);
}

function NoLocationIps( { data } ) {
	return data?.map( ( item ) => (
		<DataListGroup key={ item.ip } >
			<DataListItem text={ item.ip } count={ item.count } />
		</DataListGroup>
	) );
}

function GeolocatedIps( { data } ) {
	if ( ! data?.length ) {
		return null;
	}

	const grouped = groupBy(
		data,
		( item ) => item.location
			? `${ item.location?.country }:${ item.location?.country_code }`
			: ''
	);
	const credits = data.reduce( ( acc, item ) => {
		const credit = item.location?.credit;
		if ( credit && ! acc.includes( credit ) ) {
			acc.push( credit );
		}

		return acc;
	}, [] );

	return (
		<>
			{ map( grouped, ( ips, location ) => {
				let heading = __( 'Unknown', 'better-wp-security' );

				if ( location ) {
					const [ name, code ] = location.split( ':' );
					heading = name;

					if ( code ) {
						heading = getFlagEmoji( code ) + ' ' + heading;
					}
				}

				return (
					<DataListGroup
						key={ location }
						heading={ heading }
					>
						{ ips.map( ( ip ) => (
							<DataListItem
								key={ ip.ip }
								text={ ip.ip }
								count={ ip.count }
								hasHeading
							/>
						) ) }
					</DataListGroup>
				);
			} ) }
			{ credits.length > 0 && (
				<DataListDescription>
					{ credits.map( ( credit, i ) =>
						<Markup key={ i } noWrap content={ credit + ( i < credits.length - 1 ? '. ' : '.' ) } />
					) }
				</DataListDescription>
			) }
		</>
	);
}
