/**
 * WordPress dependencies
 */
import { Flex } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Button, Heading, Text, TextSize, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { useGlobalNavigationUrl } from '@ithemes/security-utils';
import { Logo } from '@ithemes/security-ui';
import Improvements from './improvements';

export default function Header( { installType } ) {
	const dashboardLink = useGlobalNavigationUrl( 'dashboard' ),
		settingsLink = useGlobalNavigationUrl( 'settings' );

	return (
		<Flex direction="column" gap={ 8 } expanded={ false } align="start">
			<Logo size={ 44 } />
			<Flex as="header" direction="column" gap={ 2 } expanded={ false }>
				<Heading
					level={ 1 }
					text={ installType === 'free'
						? __( 'Great Work! Thanks to Solid Security Basic, your site is secure and ready for your users.', 'better-wp-security' )
						: __( 'Great Work! Your site is ready and is more secure than ever!', 'better-wp-security' ) }
					size={ TextSize.GIGANTIC }
					weight={ TextWeight.NORMAL }
				/>
				<Text
					text={ installType === 'free'
						? __( 'Use your security dashboard for insights into your users’ activity and potential threats to your site. From there you’ll be guided to actions you can take.', 'better-wp-security' )
						: __( 'If you want to dig into your site’s security, check out your security dashboard, and make changes via settings.', 'better-wp-security' ) }
					size={ TextSize.EXTRA_LARGE }
					variant={ TextVariant.DARK }
				/>
			</Flex>
			<Flex gap={ 4 } justify="start">
				<Button
					variant={ installType === 'free' ? 'secondary' : 'primary' }
					href={ dashboardLink }
					text={ __( 'Dashboard', 'better-wp-security' ) }
				/>
				<Button
					variant={ installType === 'free' ? 'secondary' : 'primary' }
					href={ settingsLink }
					text={ __( 'Settings', 'better-wp-security' ) }
				/>
			</Flex>
			<Improvements installType={ installType } />
		</Flex>
	);
}
