/**
 * External Dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useCallback } from '@wordpress/element';
import { dateI18n } from '@wordpress/date';
import apiFetch from '@wordpress/api-fetch';
import { Tooltip } from '@wordpress/components';

/**
 * iThemes dependencies
 */
import { Heading, Text, TextSize, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { useAsync } from '@ithemes/security-hocs';
import { ActiveLockout } from './index';

const StyledDetail = styled.div`
	padding: 0.5rem 1.25rem;
`;

function Detail( { master = {}, isVisible } ) {
	const fetchDetails = useCallback( () => {
		if ( ! master.links.item ) {
			return Promise.reject( new Error( 'No data available.' ) );
		}

		const url = master.links.item[ 0 ].href.replace(
			'{lockout_id}',
			master.id
		);

		return apiFetch( { url } ).then( ( response ) => {
			return response.detail;
		} );
	}, [ master.id, master.links.item ] );

	const { value: details } = useAsync( fetchDetails, isVisible );
	return (
		<StyledDetail>
			<ActiveLockout master={ master } />
			{ details && details.history.length > 0 && (
				<History history={ details.history } />
			) }
		</StyledDetail>
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

const StyledListItem = styled.li`
	display: flex;
	align-items: center;
	gap: 0.75rem;
`;

const StyledHistoryLabel = styled( Text )`
	background-color: ${ ( { theme } ) => theme.colors.surface.secondary };
	padding: 11px 6px;
	border-radius: 2px;
`;

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

export default Detail;
