/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Heading, Text, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { StyledPageHeader } from '../styles';

export default function UserSecurityHeader() {
	return (
		<StyledPageHeader>
			<Heading
				level={ 1 }
				weight={ TextWeight.NORMAL }
				text={ __( 'User Security', 'better-wp-security' ) }
			/>
			<Text
				text={ __( 'Select user groups to edit using the filter options, and take quick security actions on multiple users at once.', 'better-wp-security' ) }
			/>
		</StyledPageHeader>
	);
}
