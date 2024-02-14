/**
 * WordPress dependencies
 */
import { __, _n, sprintf } from '@wordpress/i18n';
import { dateI18n } from '@wordpress/date';
import { useViewportMatch } from '@wordpress/compose';
import { useState, useEffect } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { Tooltip } from '@wordpress/components';

/**
 * SolidWP dependencies
 */
import {
	Button,
	Heading,
	Text,
	TextSize,
	TextVariant,
	TextWeight,
} from '@ithemes/ui';
import { Gear } from '@ithemes/security-style-guide';
import { HiResIcon } from '@ithemes/security-ui';
import { logsStore, modulesStore } from '@ithemes/security.packages.data';

/**
 * Internal dependencies
 */
import { getFlagEmoji, isApiError } from '@ithemes/security-utils';
import RuleProvider from '../rule-provider';
import LogsDetailModal from '../logs-detail-modal';
import {
	StyledLogsTable,
	StyledTableHeader,
	StyledSubheading,
	StyledSearchControl,
	StyledAction,
	StyledRule,
	StyledTableColumn,
	StyledCombinedColumn,
	StyledNoResultsContainer,
	StyledNotice,
	StyledEmptyState,
} from './styles';

const DEFAULT_QUERY = {
	module: [ 'firewall', 'lockout' ],
	code: [ 'BLOCK::%', 'host-lockout::%', 'host-triggered-blacklist' ],
	per_page: 20,
	_embed: 1,
};

export default function LogsTable() {
	const [ viewEntry, setViewEntry ] = useState( 0 );
	const { isQuerying, items, logStorageDuration } = useSelect(
		( select ) => ( {
			isQuerying: select( logsStore ).isQuerying( 'firewall' ),
			items: select( logsStore ).getQueryResults( 'firewall' ),
			logStorageDuration: select( modulesStore ).getSetting( 'global', 'log_rotation' ) ?? 60,
		} ),
		[]
	);
	const { query } = useDispatch( logsStore );
	useEffect( () => {
		query( 'firewall', DEFAULT_QUERY );
	}, [ query ] );

	const [ search, setSearch ] = useState( '' );
	const onSearch = () => query( 'firewall', { ...DEFAULT_QUERY, search } );
	const onSubmit = ( e ) => {
		e.preventDefault();
		onSearch();
	};

	const isSmall = useViewportMatch( 'medium', '<' );

	return (
		<StyledLogsTable>
			<StyledTableHeader>
				<Heading
					level={ 3 }
					size={ TextSize.LARGE }
					variant={ TextVariant.DARK }
					weight={ TextWeight.HEAVY }
					text={ __( 'Firewall Logs', 'better-wp-security' ) }
				/>
				<StyledSubheading
					variant={ TextVariant.MUTED }
					text={ sprintf(
						/* translators: Number of days. */
						_n(
							'Firewall logs are stored for up to %d day and then archived.',
							'Firewall logs are stored for up to %d days and then archived.',
							logStorageDuration,
							'better-wp-security'
						), logStorageDuration
					) }
				/>
				<form onSubmit={ onSubmit }>
					<StyledSearchControl
						label={ __( 'Search firewall logs', 'better-wp-security' ) }
						value={ search }
						onChange={ setSearch }
						isSearching={ isQuerying }
						size="medium"
						placeholder={ __( 'Search by IP address or URL', 'better-wp-security' ) }
						onSubmit={ onSearch }
					/>
				</form>
			</StyledTableHeader>
			<table className="itsec-firewall-logs-table">
				<thead>
					{ isSmall
						? (
							<tr>
								<th>{ __( 'Action & Origin', 'better-wp-security' ) }</th>
								<th>{ __( 'Protected By', 'better-wp-security' ) }</th>
							</tr>
						)
						: (
							<tr>
								<th>{ __( 'Action', 'better-wp-security' ) }</th>
								<th>{ __( 'Rule', 'better-wp-security' ) }</th>
								<th>{ __( 'Origin', 'better-wp-security' ) }</th>
								<th>{ __( 'Date & Time', 'better-wp-security' ) }</th>
								<th>{ __( 'Protected By', 'better-wp-security' ) }</th>
							</tr>
						)
					}
				</thead>
				<tbody>
					{ isQuerying && (
						<tr>
							<td colSpan={ isSmall ? 2 : 5 }>
								<StyledNoResultsContainer>
									<StyledNotice text={ __( 'Data Loading', 'better-wp-security' ) } />
								</StyledNoResultsContainer>
							</td>
						</tr>
					) }

					{ ( ! isQuerying && items.length > 0 ) &&
						( items.map( ( log ) => (
							<DynamicTableRow
								key={ log.id }
								log={ log }
								isSmall={ isSmall }
								viewEntry={ viewEntry }
								setViewEntry={ setViewEntry }
							/>
						) ) )
					}

					{ ! isQuerying && items.length === 0 && ( <EmptyState isSmall={ isSmall } /> ) }
				</tbody>
			</table>
		</StyledLogsTable>
	);
}

function DynamicTableRow( props ) {
	if ( props.log.module.raw === 'firewall' ) {
		return <FirewallTableRow { ...props } />;
	}

	if ( props.log.module.raw === 'lockout' ) {
		return <LockoutTableRow { ...props } />;
	}

	return null;
}

function FirewallTableRow( { log, isSmall, viewEntry, setViewEntry } ) {
	return (
		<LogsTableRow
			id={ log.id }
			action={ 'BLOCK' }
			actionText={ __( 'Block', 'better-wp-security' ) }
			rule={ log._embedded?.[ 'ithemes-security:firewall-rule' ]?.[ 0 ].name }
			ip={ log.ip.raw }
			geolocation={ log._embedded?.[ 'ithemes-security:geolocate' ]?.[ 0 ] }
			date={ log.created_at }
			protectedBy={ log._embedded?.[ 'ithemes-security:firewall-rule' ]?.[ 0 ].provider }
			requestUrl={ log.url }
			requestMethod={ log.data.method }
			userAgent={ log.data.user_agent }
			isSmall={ isSmall }
			viewEntry={ viewEntry }
			setViewEntry={ setViewEntry }
		/>
	);
}

function LockoutTableRow( { log, isSmall, viewEntry, setViewEntry } ) {
	return (
		<LogsTableRow
			id={ log.id }
			action={ 'BLOCK' }
			actionText={ log.code.raw.code === 'host-triggered-blacklist'
				? __( 'Ban', 'better-wp-security' )
				: __( 'Lockout', 'better-wp-security' )
			}
			rule={ log.code.raw.code === 'host-triggered-blacklist'
				? __( 'Locked out too many times', 'better-wp-security' )
				: ( log.data.module_details?.reason ?? log.data.module )
			}
			ip={ log.ip.raw }
			geolocation={ log._embedded?.[ 'ithemes-security:geolocate' ]?.[ 0 ] }
			date={ log.created_at }
			protectedBy={ 'solid' }
			requestUrl={ log.url }
			isSmall={ isSmall }
			viewEntry={ viewEntry }
			setViewEntry={ setViewEntry }
		/>
	);
}

function LogsTableRow( {
	id,
	actionText,
	action,
	rule,
	ip,
	geolocation,
	date,
	protectedBy,
	requestUrl,
	requestMethod,
	userAgent,
	isSmall,
	viewEntry,
	setViewEntry,
} ) {
	const flag = geolocation && ! isApiError( geolocation ) && (
		<Tooltip text={ geolocation.label }><span>{ getFlagEmoji( geolocation.country_code ) }{ ' ' }</span></Tooltip>
	);

	return (
		<tr>
			{ isSmall ? (
				<>
					<td>
						<StyledCombinedColumn>
							<StyledAction
								weight={ TextWeight.HEAVY }
								action={ action }
								text={ actionText }
								textTransform="uppercase"
							/>
							{ flag }
							{ ip }
						</StyledCombinedColumn>
					</td>
					<td>
						<StyledTableColumn>
							<RuleProvider provider={ protectedBy } />
							<Button text={ __( 'Details', 'better-wp-security' ) } />
						</StyledTableColumn>
					</td>
				</>
			) : (
				<>
					<StyledAction
						as="td"
						action={ action }
						weight={ TextWeight.HEAVY }
						text={ actionText }
						textTransform="uppercase"
					/>
					<StyledRule as="td">
						{ rule || __( 'Unknown rule', 'better-wp-security' ) }
					</StyledRule>
					<td>
						{ flag }
						{ ip }
					</td>

					<td>{ dateI18n( 'M d, Y - g:i:s', date ) }</td>
					<td>
						<StyledTableColumn>
							<RuleProvider provider={ protectedBy } />
							<Button
								aria-pressed={ viewEntry === id }
								onClick={ () => setViewEntry( id ) }
								text={ __( 'Details', 'better-wp-security' ) }
							/>
						</StyledTableColumn>
					</td>
				</>
			) }
			{ viewEntry === id && (
				<LogsDetailModal
					actionText={ actionText }
					rule={ rule }
					ip={ ip }
					geolocation={ geolocation }
					date={ date }
					requestUrl={ requestUrl }
					requestMethod={ requestMethod }
					userAgent={ userAgent }
					onRequestClose={ () => setViewEntry( 0 ) }
				/>
			) }
		</tr>
	);
}

function EmptyState( { isSmall } ) {
	const { logTypeFile } = useSelect( ( select ) => ( {
		logTypeFile: select( modulesStore ).getSetting( 'global', 'log_type' ) === 'file',
	} ), [] );
	return (
		<tr>
			<td colSpan={ isSmall ? 2 : 5 }>
				{ logTypeFile ? (
					<StyledNoResultsContainer>
						<StyledNotice text={ __( 'To view logs inside Solid Security, you must enable database logging in Global Settings.', 'better-wp-security' ) } />
					</StyledNoResultsContainer>
				) : (
					<StyledEmptyState>
						<HiResIcon icon={ <Gear /> } />
						<Text text={ __( 'We haven’t logged any activity yet.', 'better-wp-security' ) } />
					</StyledEmptyState>
				) }
			</td>
		</tr>
	);
}
