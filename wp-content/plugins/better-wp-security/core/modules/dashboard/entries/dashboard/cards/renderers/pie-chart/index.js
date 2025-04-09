/**
 * External dependencies
 */
import styled from '@emotion/styled';
import {
	PolarAngleAxis,
	RadialBar,
	RadialBarChart as Chart,
	ResponsiveContainer,
} from 'recharts';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * SolidWP dependencies
 */
import { Text, TextSize, TextVariant, TextWeight } from '@ithemes/ui';
import { DateRangeControl } from '@ithemes/security-ui';

/**
 * Internal dependencies
 */
import { shortenNumber } from '@ithemes/security-utils';
import { PRIMARYS } from '@ithemes/security-style-guide';
import Header, { Title } from '../../../components/card/header';
import './style.scss';

const StyledChart = styled.div`display: flex;
	align-items: center;
	justify-content: center;
	column-gap: 1.5rem;
`;

const StyledChartTotals = styled.div`
	display: flex;
	flex-direction: column;
	align-items: center;
`;

const StyledDataValues = styled( Text )`
	display: grid;
	grid-template-columns: 1rem 2.25rem;
	justify-content: end;
	align-items: center;
	grid-gap: 0.25rem;
	color: ${ ( { color } ) => color };
`;

export default function PieChart( { card, config } ) {
	const { period } = useSelect( ( select ) => ( {
		period: select( 'ithemes-security/dashboard' ).getDashboardCardQueryArgs( card.id )?.period ??
			config.query_args.period?.default,
	} ), [ card.id, config ] );
	const { queryDashboardCard } = useDispatch( 'ithemes-security/dashboard' );

	const data = [];
	let total = 0;

	const onPeriodChange = ( newPeriod ) => {
		return queryDashboardCard( card.id, { period: newPeriod } );
	};

	if ( card.data && card.data.data ) {
		let i = 0;
		for ( const key in card.data.data ) {
			if ( ! card.data.data.hasOwnProperty( key ) ) {
				continue;
			}

			total += card.data.data[ key ].sum;
			data.push( {
				name: card.data.data[ key ].label,
				value: card.data.data[ key ].sum,
				fill: PRIMARYS[ i % PRIMARYS.length ],
			} );
			i++;
		}
	}

	const hasData = total > 0;

	if ( ! hasData ) {
		data.forEach( ( datum ) => ( datum.value = 1 ) );
	}

	function TextTotal() {
		return (
			<StyledChartTotals>
				<Text
					as="p"
					variant={ TextVariant.DARK }
					size={ TextSize.GIGANTIC }
					text={ hasData ? shortenNumber( card.data.circle_sum ) : '—' }
				/>
				<Text
					as="p"
					variant={ TextVariant.MUTED }
					size={ TextSize.EXTRA_LARGE }
					text={ hasData
						? card.data.circle_label
						: __( 'No Data', 'better-wp-security' ) }
				/>
			</StyledChartTotals>
		);
	}

	return (
		<div
			className={ classnames( 'itsec-card--type-pie-chart', {
				'itsec-card--type-pie-chart--no-data': ! hasData,
			} ) }
		>
			<Header>
				<Title card={ card } config={ config } />
				{ config.query_args.period && (
					<DateRangeControl value={ period } onChange={ onPeriodChange } />
				) }
			</Header>
			<StyledChart>
				<TextTotal />
				<ResponsiveContainer width="50%" height={ 200 }>
					<Chart
						innerRadius={ 30 }
						outerRadius={ 70 }
						barSize={ 6 }
						data={ data }
						startAngle={ 90 }
						endAngle={ 450 }
						barGap={ 6 }
					>
						<PolarAngleAxis
							type="number"
							domain={ [ 0, total ] }
							dataKey="uv"
							angleAxisId={ 0 }
							tick={ false }
						/>
						<RadialBar
							background
							dataKey="value"
							angleAxisId={ 0 }
							data={ data }
							cornerRadius={ 8 }
						/>
					</Chart>
				</ResponsiveContainer>
			</StyledChart>
			<table className="itsec-card-pie-chart__values">
				<tbody>
					{ data.map( ( datum, i ) => (
						<tr key={ datum.name }>
							<th scope="row">{ datum.name }</th>
							<td>
								<StyledDataValues
									as="span"
									size={ TextSize.NORMAL }
									weight={ TextWeight.HEAVY }
									variant={ TextVariant.DARK }
									align="right"
									indicator={ PRIMARYS[ i % PRIMARYS.length ] }
								>
									{ hasData
										? ( ( datum.value / total ) * 100 ).toFixed(
											0
										) + '%'
										: '—' }
								</StyledDataValues>
							</td>
						</tr>
					) ) }
				</tbody>
			</table>
		</div>
	);
}
