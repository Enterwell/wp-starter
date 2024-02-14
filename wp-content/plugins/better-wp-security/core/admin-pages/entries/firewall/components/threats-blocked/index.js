/**
 * External dependencies
 */
import { useTheme } from '@emotion/react';
import {
	AreaChart,
	Area,
	ResponsiveContainer,
	XAxis,
	YAxis,
	Tooltip,
} from 'recharts';
import { isEmpty } from 'lodash';

/**
 * WordPress dependencies
 */
import { dateI18n } from '@wordpress/date';
import { useCallback } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { useViewportMatch } from '@wordpress/compose';

/**
 * SolidWP dependencies
 */
import {
	Button,
	SurfaceVariant,
	Text,
	TextSize,
	TextVariant,
} from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { DateRangeControl, HiResIcon } from '@ithemes/security-ui';
import { AllClearCard } from '@ithemes/security-style-guide';
import { useAsync } from '@ithemes/security-hocs';
import { useGlobalNavigationUrl } from '@ithemes/security-utils';
import {
	StyledLineGraphCard,
	StyledCardHeader,
	StyledTooltip,
	NoResultsContainer,
	StyledNotice,
	StyledEmptyStateContainer,
} from './styles';

function TooltipContent( { active, payload } ) {
	if ( ! active || ! payload?.length ) {
		return null;
	}

	const label = sprintf(
		/* translators: 1. Number of attempts. */
		_n( '%d attempt', '%d attempts', payload[ 0 ].value, 'better-wp-security' ),
		payload[ 0 ].value
	);

	return (
		<StyledTooltip variant={ SurfaceVariant.DARK }>
			<Text as="p" text={ label } />
			<Text as="p" text={ payload[ 0 ].payload.name } />
		</StyledTooltip>
	);
}

export default function ThreatsBlocked( { period, setPeriod } ) {
	const theme = useTheme();
	let threats;
	const isSmall = useViewportMatch( 'medium', '<' );
	const fetchData = useCallback( () => {
		const queryArgs = {
			events: [ 'local-brute-force', 'network-brute-force', 'firewall-block' ],
			period,
		};

		return apiFetch( {
			path: addQueryArgs(
				'/ithemes-security/v1/dashboard/events',
				queryArgs
			) } );
	}, [ period ] );

	const { status, value, error } = useAsync( fetchData );
	const settingsUrl = useGlobalNavigationUrl( 'settings', '/settings/configure/lockout' );

	if ( 'success' === status ) {
		threats = {
			'firewall-graph':
				{
					data: Object.values( value.data ),
				},
		};
	}
	const data = [],
		lines = [];
	let sum = 0;

	if ( ! isEmpty( threats ) ) {
		for ( const key in threats ) {
			for ( let i = 0; i < threats[ key ].data.length; i++ ) {
				const datum = threats[ key ].data[ i ];
				sum += datum.y;
				if ( data[ i ] ) {
					data[ i ][ key ] = datum.y;
				} else {
					const format = period === '24-hours' ? 'g A' : 'M j';

					data.push( {
						name: datum.t ? dateI18n( format, datum.t ) : datum.x,
						[ key ]: datum.y,
					} );
				}
			}
			lines.push( {
				dataKey: key,
			} );
		}
	}

	const onPeriodChange = ( newPeriod ) => {
		return setPeriod( newPeriod );
	};
	return (
		<StyledLineGraphCard>
			<StyledCardHeader>
				<Text
					size={ TextSize.LARGE }
					variant={ TextVariant.DARK }
					weight={ 600 }
					text={
						status === 'success'
							? sprintf(
								/* translators: Number of results */
								_n( '%d Threat Blocked', '%d Threats Blocked', sum, 'better-wp-security' ),
								sum ) : __( 'Threats Blocked', 'better-wp-security' ) }
				/>
				<DateRangeControl value={ period } onChange={ onPeriodChange } />
			</StyledCardHeader>

			{ status === 'pending' &&
				<NoResultsContainer>
					<StyledNotice text={ __( 'Data Loading', 'better-wp-security' ) } />
				</NoResultsContainer>
			}

			{ status === 'error' &&
				<NoResultsContainer>
					<StyledNotice type="danger" text={ sprintf(
						/* translators: The error message */
						__( 'An error has occurred: %s', 'better-wp-security' ),
						error.message ) }
					/>
				</NoResultsContainer>
			}

			{ status === 'success' && (
				sum > 0 ? (
					<ResponsiveContainer width="100%" height={ 275 }>
						<AreaChart
							data={ data }
							margin={
								{ top: 40, left: -15, right: 50, bottom: 10 }
							}
						>
							{ isSmall ? (
								<XAxis
									ticks={ [
										data[ 0 ]?.name,
										data[ data.length / 2 ]?.name,
										data[ data.length - 1 ]?.name,
									] }
									dataKey="name"
									tickLine={ false }
									stroke={ theme.colors.text.muted }
								/>
							) : (
								<XAxis
									interval={ 1 }
									dataKey="name"
									tickLine={ false }
									stroke={ theme.colors.text.muted }
								/>
							) }
							<YAxis allowDecimals={ false } tickLine={ false } stroke={ theme.colors.text.muted } />
							<Tooltip content={ <TooltipContent /> } />
							{ lines.map( ( line ) => (
								<Area
									type="linear"
									key={ line.dataKey }
									dataKey={ line.dataKey }
									stroke={ theme.colors.primary.darker20 }
									fill={ theme.colors.tertiary.base }
									isAnimationActive={ false }
									dot
								/>
							) ) }
						</AreaChart>
					</ResponsiveContainer>
				) : (
					<StyledEmptyStateContainer>
						<HiResIcon icon={ <AllClearCard /> } />
						<Text
							align="center"
							variant={ TextVariant.DARK }
							text={ __( 'There are no recently blocked threats. This could mean there haven’t been any attacks recently.', 'better-wp-security' ) }
						/>
						<Text
							align="center"
							variant={ TextVariant.DARK }
							text={ __( 'Make sure to configure the firewall settings if you haven’t yet!', 'better-wp-security' ) }
						/>
						<Button
							href={ settingsUrl }
							text={ __( 'Configure Firewall', 'better-wp-security' ) }
						/>
					</StyledEmptyStateContainer>
				)
			) }
		</StyledLineGraphCard>
	);
}
