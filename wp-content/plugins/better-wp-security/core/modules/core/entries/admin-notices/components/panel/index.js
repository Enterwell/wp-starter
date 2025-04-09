/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { cog as configureIcon } from '@wordpress/icons';

/**
 * iThemes dependencies
 */
import { Button, Heading, Text, Notice, TextWeight, TextSize, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import NoticeList from '../notice-list';
import Highlights from '../highlights';
import { StyledHeader, StyledHeaderText, StyledPanel } from './styles';

export default function Panel( { notices, loaded } ) {
	const [ isConfiguring, setIsConfiguring ] = useState( false );

	return (
		<StyledPanel>
			<StyledHeader>
				<StyledHeaderText>
					<Heading
						level={ 3 }
						size={ TextSize.NORMAL }
						variant={ TextVariant.ACCENT }
						weight={ TextWeight.HEAVY }
						text={ __( 'Security Admin Messages', 'better-wp-security' ) }
					/>
					<Text
						as="p"
						size={ TextSize.SMALL }
						variant={ TextVariant.MUTED }
						text={ __( 'Important notices from Solid Security', 'better-wp-security' ) }
					/>
				</StyledHeaderText>

				<Button
					icon={ configureIcon }
					label={ __( 'Configure', 'better-wp-security' ) }
					onClick={ () => setIsConfiguring( ! isConfiguring ) }
					variant="tertiary"
				/>
			</StyledHeader>
			<Highlights loaded={ loaded } isConfiguring={ isConfiguring } />
			{ notices.length > 0 && <NoticeList notices={ notices } /> }
			{ notices.length === 0 && loaded && (
				<Notice text={ __( 'Keep up the good work! There are no security admin messages at this time.', 'better-wp-security' ) } />
			) }
		</StyledPanel>
	);
}
