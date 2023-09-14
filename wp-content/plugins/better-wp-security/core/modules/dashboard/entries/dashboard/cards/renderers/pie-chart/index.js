/**
 * External dependencies
 */
import {
	ResponsiveContainer,
	PieChart as Chart,
	Pie,
	Cell,
	Sector,
} from 'recharts';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { shortenNumber } from '@ithemes/security-utils';
import { PRIMARYS } from '@ithemes/security-style-guide';
import Header, { Title, Date } from '../../../components/card/header';
import './style.scss';

export default function PieChart( { card, config } ) {
	const data = [];
	let total = 0;

	if ( card.data && card.data.data ) {
		for ( const key in card.data.data ) {
			if ( ! card.data.data.hasOwnProperty( key ) ) {
				continue;
			}

			total += card.data.data[ key ].sum;
			data.push( {
				name: card.data.data[ key ].label,
				value: card.data.data[ key ].sum,
			} );
		}
	}

	const hasData = total > 0;
	const fillOpacity = hasData ? 1 : 0.4;

	if ( ! hasData ) {
		data.forEach( ( datum ) => ( datum.value = 1 ) );
	}

	const renderActiveShape = ( props ) => {
		const {
			cx,
			cy,
			innerRadius,
			outerRadius,
			startAngle,
			endAngle,
			fill,
		} = props;

		return (
			<g>
				<text
					x={ cx }
					y={ cy + 10 }
					dy={ 8 }
					textAnchor="middle"
					fill={ fill }
					fillOpacity={ fillOpacity }
					className="itsec-card-pie-chart__circle-sum"
				>
					{ hasData ? shortenNumber( card.data.circle_sum ) : '—' }
				</text>
				<text
					x={ cx }
					y={ cy + 30 }
					dy={ 8 }
					textAnchor="middle"
					fill={ fill }
					fillOpacity={ fillOpacity }
					className="itsec-card-pie-chart__circle-label"
				>
					{ hasData
						? card.data.circle_label
						: __( 'No Data', 'better-wp-security' ) }
				</text>
				<Sector
					cx={ cx }
					cy={ cy }
					innerRadius={ innerRadius }
					outerRadius={ outerRadius }
					startAngle={ startAngle }
					endAngle={ endAngle }
					fill={ fill }
					fillOpacity={ fillOpacity }
				/>
			</g>
		);
	};

	return (
		<div
			className={ classnames( 'itsec-card--type-pie-chart', {
				'itsec-card--type-pie-chart--no-data': ! hasData,
			} ) }
		>
			<Header>
				<Title card={ card } config={ config } />
				{ config.query_args.period && (
					<Date card={ card } config={ config } />
				) }
			</Header>
			<ResponsiveContainer width="100%" height={ 200 }>
				<Chart>
					<Pie
						data={ data }
						dataKey="value"
						innerRadius={ 60 }
						outerRadius={ 80 }
						fill="#8884d8"
						fillOpacity={ fillOpacity }
						paddingAngle={ 5 }
						activeShape={ renderActiveShape }
						activeIndex={ 0 }
						isAnimationActive={ false }
					>
						{ data.map( ( entry, index ) => (
							<Cell
								key={ index }
								fill={ PRIMARYS[ index % PRIMARYS.length ] }
							/>
						) ) }
					</Pie>
				</Chart>
			</ResponsiveContainer>
			<table className="itsec-card-pie-chart__values">
				<tbody>
					{ data.map( ( datum, i ) => (
						<tr key={ datum.name }>
							<th scope="row">{ datum.name }</th>
							<td style={ { color: PRIMARYS[ i ] } }>
								{ hasData
									? ( ( datum.value / total ) * 100 ).toFixed(
										0
									) + '%'
									: '—' }
							</td>
						</tr>
					) ) }
				</tbody>
			</table>
		</div>
	);
}
