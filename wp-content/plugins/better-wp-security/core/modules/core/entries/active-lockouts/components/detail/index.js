/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useCallback, useMemo } from '@wordpress/element';
import { dateI18n } from '@wordpress/date';
import { Tooltip } from '@wordpress/components';

/**
 * iThemes dependencies
 */
import {
	Heading,
	Text,
	TextSize,
	TextVariant,
	TextWeight,
} from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { useAsync } from '@ithemes/security-hocs';
import {
	StyledDetail,
	StyledListItem,
	StyledHistoryLabel,
} from './styles';

export default function Detail( { master = {}, isVisible, fetchLockoutDetails } ) {
	const lockout = useMemo( () => master, [ master ] );
	const doFetch = useCallback( () => {
		return fetchLockoutDetails( master );
	}, [ fetchLockoutDetails, master ] );
	const { value: details } = useAsync( doFetch, isVisible );
	return (
		<StyledDetail>
			<ActiveLockout master={ lockout } />
			{ details && details.history.length > 0 && (
				<History history={ details.history } />
			) }
		</StyledDetail>
	);
}

export function ActiveLockout( { master = {} } ) {
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

function History( { history } ) {
	return (
		<>
			<hr />

			<div>
				<Heading
					level={ 4 }
					size={ TextSize.NORMAL }
					variant={ TextVariant.DARK }
					weight={ TextWeight.HEAVY }
					text={ __( 'History', 'better-wp-security' ) }
				/>
				<ul>
					{ history.map( ( detail ) =>
						<HistoryItem
							key={ detail.id }
							history={ detail }
						/>
					) }
				</ul>
			</div>
		</>
	);
}

function HistoryItem( { history } ) {
	if ( ! history.label ) {
		return;
	}

	return (
		<StyledListItem key={ history.id }>
			<StyledHistoryLabel as="code">{ history.label }</StyledHistoryLabel>
			<Tooltip text={ dateI18n( 'M d, Y g:s A', history.time ) }>
				<span>
					{ ' ' }
					&#8226;
					{ ' ' }
					<Text
						as="time"
						variant={ TextVariant.DARK }
						text={ sprintf(
							/* translators: 1. Relative time from human_time_diff(). */
							__( '%s ago', 'better-wp-security' ),
							history.time_relative
						) }
					/>
				</span>
			</Tooltip>
		</StyledListItem>
	);
}
