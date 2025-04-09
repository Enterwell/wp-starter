/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { dateI18n } from '@wordpress/date';

/**
 * SolidWP dependencies
 */
import { Text, TextSize, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { getFlagEmoji, isApiError } from '@ithemes/security-utils';
import {
	StyledColumn,
	StyledColumnContainer,
	StyledModal,
	StyledModalBody,
	StyledRequestMethod,
	StyledRow,
	StyledRowContent,
} from './styles';

export default function LogsDetailModal( {
	actionText,
	rule,
	ip,
	geolocation,
	date,
	requestUrl,
	requestMethod,
	userAgent,
	onRequestClose,
} ) {
	return (
		<StyledModal
			title={ __( 'Log Details', 'better-wp-security' ) }
			onRequestClose={ onRequestClose }
		>
			<StyledModalBody>
				<StyledColumnContainer>
					<StyledColumn>
						<Text
							size={ TextSize.SMALL }
							variant={ TextVariant.MUTED }
							text={ __( 'Triggered Rule', 'better-wp-security' ) }
						/>
						<Text
							size={ TextSize.EXTRA_LARGE }
							variant={ TextVariant.DARK }
							text={ rule || __( 'Unknown rule', 'better-wp-security' ) }
						/>
					</StyledColumn>
					<StyledColumn>
						<Text
							size={ TextSize.SMALL }
							variant={ TextVariant.MUTED }
							text={ __( 'Action Taken', 'better-wp-security' ) }
						/>
						<Text
							size={ TextSize.EXTRA_LARGE }
							text={ actionText }
							variant={ TextVariant.DARK }
						/>
					</StyledColumn>
				</StyledColumnContainer>

				<StyledRow>
					<Text
						size={ TextSize.SMALL }
						variant={ TextVariant.MUTED }
						text={ __( 'Date & Time:', 'better-wp-security' ) }
					/>
					<Text
						variant={ TextVariant.DARK }
						text={
							dateI18n( 'M d, Y - g:i:s', date )
						}
					/>
				</StyledRow>

				{ requestUrl && (
					<StyledRow>
						<Text
							size={ TextSize.SMALL }
							variant={ TextVariant.MUTED }
							text={ __( 'Request:', 'better-wp-security' ) }
						/>
						<StyledRowContent>
							{ requestMethod && (
								<StyledRequestMethod text={ requestMethod } />
							) }
							<Text
								variant={ TextVariant.DARK }
								text={ requestUrl } />
						</StyledRowContent>
					</StyledRow>
				) }
				<StyledRow>
					<Text
						size={ TextSize.SMALL }
						variant={ TextVariant.MUTED }
						text={ __( 'Origin:', 'better-wp-security' ) }
					/>
					<StyledRowContent>
						{ geolocation && ! isApiError( geolocation ) && (
							<>
								<Text
									variant={ TextVariant.DARK }
									text={
										getFlagEmoji( geolocation.country_code ) +
										' ' + geolocation.label
									}
								/>
								<Text variant={ TextVariant.MUTED }>&#x2022;</Text>
							</>
						) }
						<Text text={ ip } />
					</StyledRowContent>
				</StyledRow>

				{ userAgent && (
					<StyledRow>
						<Text
							size={ TextSize.SMALL }
							variant={ TextVariant.MUTED }
							text={ __( 'User Agent:', 'better-wp-security' ) } />
						<Text
							variant={ TextVariant.DARK }
							text={ userAgent } />
					</StyledRow>
				) }
			</StyledModalBody>
		</StyledModal>
	);
}
