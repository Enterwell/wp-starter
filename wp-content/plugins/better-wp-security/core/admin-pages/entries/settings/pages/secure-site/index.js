/**
 * External dependencies
 */
import { useParams } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Flex } from '@wordpress/components';
import { useDispatch } from '@wordpress/data';

/**
 * iThemes dependencies
 */
import { Text, TextSize, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { useSingletonEffect } from '@ithemes/security-hocs';
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import { ONBOARD_STORE_NAME } from '../../stores';
import { useNavigation } from '../../page-registration';
import { StyledSpinner } from './styles';

export default function SecureSite() {
	const { root } = useParams();
	const { goNext } = useNavigation();
	const { completeOnboarding } = useDispatch( ONBOARD_STORE_NAME );

	useCompletionSteps();
	useSingletonEffect( SecureSite, () => {
		completeOnboarding( { root } ).then( goNext );
	} );

	return (
		<Flex expanded={ false } direction="column" gap={ 4 } align="center">
			<StyledSpinner />
			<Text
				size={ TextSize.LARGE }
				variant={ TextVariant.DARK }
				weight={ TextWeight.HEAVY }
				align="center"
				text={ __( 'Hang on while your website is secured!', 'better-wp-security' ) }
			/>
		</Flex>
	);
}

function useCompletionSteps() {
	const { registerCompletionStep } = useDispatch( ONBOARD_STORE_NAME );
	const { saveModules, saveSettings } = useDispatch( MODULES_STORE_NAME );

	useSingletonEffect( useCompletionSteps, () => {
		registerCompletionStep( {
			id: 'savingModules',
			label: __( 'Enable Features', 'better-wp-security' ),
			priority: 5,
			callback() {
				return saveModules();
			},
		} );

		registerCompletionStep( {
			id: 'savingSettings',
			label: __( 'Configure Settings', 'better-wp-security' ),
			priority: 10,
			callback() {
				return saveSettings();
			},
		} );
	} );
}
