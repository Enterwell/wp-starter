/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Flex } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';

/**
 * SolidWP dependencies
 */
import { Button, Heading, MessageList, Text, TextSize, TextVariant, TextWeight } from '@ithemes/ui';
import { Patchstack } from '@ithemes/security-style-guide';
import { siteScannerStore, vulnerabilitiesStore } from '@ithemes/security.packages.data';

/**
 * Internal dependencies
 */
import { StyledCard, StyledCardGraphic, StyledPoweredBy } from './styles';
import ThumbPrint from './thumb-print';
import { useConfigContext } from '../../../../../utils';

export default function Intro( { question, onAnswer } ) {
	const { installType } = useConfigContext();
	const { isScanning, isQuerying } = useSelect( ( select ) => ( {
		isScanning: select( siteScannerStore ).isScanning(),
		isQuerying: select( vulnerabilitiesStore ).isQuerying( 'onboarding' ),
	} ), [] );
	const { runScan } = useDispatch( siteScannerStore );
	const { query } = useDispatch( vulnerabilitiesStore );
	const [ errors, setErrors ] = useState();

	const onScan = async () => {
		const result = await runScan();
		if ( result.status === 'error' ) {
			setErrors( result.errors );

			return;
		}
		await query( 'onboarding', {
			resolution: [ '', 'deactivated', 'patched' ],
			per_page: 10,
		} );
	};

	return (
		<Flex direction="column" gap={ 6 }>
			<StyledCard>
				<StyledCardGraphic>
					<ThumbPrint />
				</StyledCardGraphic>
				<Flex direction="column" gap={ 6 }>
					<Heading level={ 3 } text={ question.prompt } size={ TextSize.EXTRA_LARGE } weight={ TextWeight.NORMAL } />
					<Flex direction="column" gap={ 3 }>
						<Heading level={ 4 } text={ __( 'Why is this important?', 'better-wp-security' ) } size={ TextSize.SUBTITLE_SMALL } />
						<Text text={ question.description } variant={ TextVariant.MUTED } />
					</Flex>
				</Flex>
				{ errors && (
					<Flex direction="column" gap={ 4 }>
						<MessageList
							heading={ __( 'Oops! We hit a snag scanning your site.', 'better-wp-security' ) }
							messages={ errors.map( ( error ) => error.message ) }
							type="warning"
						/>
						<Button
							text={ __( 'Skip for Now', 'better-wp-security' ) }
							align="center"
							variant="primary"
							onClick={ () => onAnswer( false ) }
						/>
					</Flex>
				) }
				{ ! errors && (
					<Flex direction="column" gap={ 3 }>
						<Button
							text={ __( 'Scan Site', 'better-wp-security' ) }
							variant="primary"
							isBusy={ isScanning || isQuerying }
							onClick={ onScan }
						/>
						<Button
							text={ __( 'No, Skip Site Scan', 'better-wp-security' ) }
							variant="tertiary"
							onClick={ () => onAnswer( false ) }
						/>
					</Flex>
				) }
			</StyledCard>
			{ installType === 'free' && (
				<Button text={ __( 'Privacy Policy', 'better-wp-security' ) } href="https://go.solidwp.com/solid-privacy-policy" variant="tertiary" target="_blank" />
			) }
			<StyledPoweredBy>
				<Text text={ __( 'Powered by', 'better-wp-security' ) } variant={ TextVariant.MUTED } size={ TextSize.SMALL } />
				<Patchstack width={ 171 } alt={ __( 'Patchstack', 'better-wp-security' ) } />
			</StyledPoweredBy>
		</Flex>
	);
}
