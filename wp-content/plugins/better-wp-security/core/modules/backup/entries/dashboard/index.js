/**
 * External dependencies.
 */
import styled from '@emotion/styled';
import { isEmpty, take } from 'lodash';

/**
 * WordPress dependencies.
 */
import { __, _n } from '@wordpress/i18n';
import { dateI18n } from '@wordpress/date';
import { withDispatch } from '@wordpress/data';

/**
 * iThemes dependencies
 */
import { Surface, Text, TextSize, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import {
	CardHeader,
	CardHeaderTitle,
	CardFooterSchemaActions,
} from '@ithemes/security.dashboard.dashboard';
import { shortenNumber } from '@ithemes/security-utils';
import './style.scss';

const StyledCardSurface = styled( Surface )`
	display: flex;
	flex-direction: column;
	justify-content: space-between;
	height: 100%;
	overflow: hidden;
`;

const StyledNoData = styled( Text )`
	padding: 1rem;
`;

const StyledTotalContainer = styled.div`
	flex-grow: 1;
	display: flex;
	align-items: center;
	justify-content: center;
	padding: ${ ( { theme: { getSize } } ) => getSize( 0.5 ) }
`;

const StyledTotalSurface = styled( Surface )`
	display: flex;
	flex-direction: column;
	flex-grow: 0;
	flex-shrink: 0;
	justify-content: center;
	align-items: center;
	border-radius: 50%;
	height: 120px;
	width: 120px;
`;

const StyledTotal = styled( Text )`
	& sup {
		position: absolute;
		font-size: .5em;
		line-height: 1;
	}
`;

const StyledBackups = styled.section`
	flex-shrink: 1;
	overflow-y: auto;
	position: relative;
`;

function DatabaseBackup( { card, config, addNotice } ) {
	const onComplete = ( href, response ) => {
		if ( href.endsWith( '/backup' ) ) {
			addNotice( response.message, 'backup-complete' );
		}
	};

	const label = _n( 'Backup', 'Backups', card.data.total, 'better-wp-security' );

	return (
		<StyledCardSurface>
			<CardHeader>
				<CardHeaderTitle card={ card } config={ config } />
			</CardHeader>

			{ isEmpty( card.data )
				? (
					<StyledNoData
						as="p"
						text={ __( 'Enable database logging or file backups to see a history of completed backups', 'better-wp-security' ) } />
				)
				: (
					<>
						<StyledTotalContainer>
							<StyledTotalSurface as="section" variant="secondary">
								<StyledTotal size={ TextSize.GIGANTIC } variant={ TextVariant.DARK }>
									{ shortenNumber( card.data.total ) }
									{ card.data.total > 99 && <sup>+</sup> }
								</StyledTotal>
								<Text
									size={ TextSize.LARGE }
									variant={ TextVariant.DARK }
									weight={ 600 }
									text={ label }
								/>
							</StyledTotalSurface>
						</StyledTotalContainer>
						{ card.data.backups.length > 0 && (
							<StyledBackups aria-label={ __( 'Recent Backups', 'better-wp-security' ) }>
								<table className="itsec-card-database-backup__recent-backups">
									<thead>
										<tr>
											<th scope="column">
												{ __( 'Date', 'better-wp-security' ) }
											</th>
											<th scope="column">
												{ __( 'Size', 'better-wp-security' ) }
											</th>
											{ card.data.source === 'files' && (
												<th scope="column">
													<span className="screen-reader-text">
														{ __( 'Download', 'better-wp-security' ) }
													</span>
												</th>
											) }
										</tr>
									</thead>
									<tbody>
										{ take( card.data.backups, 50 ).map( ( backup ) => (
											<tr key={ backup.url || backup.time }>
												<th scope="row">
													<Text weight={ 600 } text={ dateI18n(
														'M d, Y g:i A',
														backup.time
													) } />
												</th>
												<td>
													<Text weight={ 600 } text={ backup.size_format } />
												</td>
												{ card.data.source === 'files' && (
													<td>
														{ backup.url && (
															<a href={ backup.url } download>
																{ __( 'Download', 'better-wp-security' ) }
															</a>
														) }
													</td>
												) }
											</tr>
										) ) }
									</tbody>
								</table>
							</StyledBackups>
						) }
					</>
				)
			}

			<CardFooterSchemaActions card={ card } onComplete={ onComplete } />
		</StyledCardSurface>
	);
}

export const slug = 'database-backup';
export const settings = {
	render: withDispatch( ( dispatch ) => ( {
		addNotice( message, id ) {
			dispatch( 'core/notices' ).createSuccessNotice( message, {
				id,
				context: 'ithemes-security',
			} );
			setTimeout(
				() =>
					dispatch( 'core/notices' ).removeNotice(
						id,
						'ithemes-security'
					),
				10000
			);
		},
	} ) )( DatabaseBackup ),
};
