/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { external as linkIcon } from '@wordpress/icons';
import { useSelect } from '@wordpress/data';

/**
 * SolidWP dependencies
 */
import { Button, Notice } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { BeforeHeaderFill } from '@ithemes/security.pages.vulnerabilities';
import { coreStore } from '@ithemes/security.packages.data';
import { StyledTextContainer } from './styles';

export default function App() {
	const { installType } = useSelect(
		( select ) => ( {
			installType: select( coreStore ).getInstallType(),
		} ),
		[]
	);

	if ( installType !== 'free' ) {
		return null;
	}
	return (
		<BeforeHeaderFill>
			<Notice
				text={
					<StyledTextContainer
						text={ __( 'Pro users receive early protection and alerts for vulnerabilities.', 'better-wp-security' ) }
					>
						<Button
							icon={ linkIcon }
							iconSize={ 15 }
							iconPosition="right"
							text={ __( 'Get early protection', 'better-wp-security' ) }
							variant="link"
							target="_blank"
							href="https://go.solidwp.com/basic-to-pro"
						/>
					</StyledTextContainer>
				}
				badge={ __( 'Why go Pro?', 'better-wp-security' ) }
			/>
		</BeforeHeaderFill>
	);
}
