/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Flex } from '@wordpress/components';

/**
 * Solid dependencies
 */
import { Heading, Text, TextSize, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { SelectableCard, useNavigateTo } from '@ithemes/security.pages.settings';
import { StyledWrapper, defaultUserGroup, customUserGroup } from './styles';
import { useApplyDefaultGroupSettings, useCreateDefaultGroups } from '../../utils';

export default function OnboardChooser() {
	const navigateTo = useNavigateTo();
	const createDefault = useCreateDefaultGroups();
	const applyDefaultSettings = useApplyDefaultGroupSettings();

	const onDefault = async () => {
		const navIds = await createDefault();
		await applyDefaultSettings();
		navigateTo( `/onboard/user-groups/${ navIds[ 0 ] }` );
	};
	const onCustom = () => {
		navigateTo( '/onboard/user-groups/everybody-else' );
	};

	return (
		<StyledWrapper>
			<Flex as="header" gap={ 2 } direction="column" expanded={ false }>
				<Heading
					level={ 2 }
					size={ TextSize.EXTRA_LARGE }
					variant={ TextVariant.DARK }
					text={ __( 'User Groups', 'better-wp-security' ) }
				/>
				<Text
					size={ TextSize.SUBTITLE_SMALL }
					variant={ TextVariant.DARK }
					weight={ TextWeight.HEAVY }
					text={ __( 'Enable or disable security features for specific groups of users.', 'better-wp-security' ) }
				/>
				<Text
					variant={ TextVariant.MUTED }
					text={ __( 'Default user groups are the roles that already exist in WordPress, like Authors, Editors, and Administrators. Custom user groups can include any existing user or role. User groups allow you to define unique security options and requirements for each group. Create and enforce group security policies with default and custom user groups.', 'better-wp-security' ) }
				/>
			</Flex>
			<Flex gap={ 12 }>
				<SelectableCard
					title={ __( 'Default User Groups', 'better-wp-security' ) }
					icon={ defaultUserGroup }
					direction="vertical"
					onClick={ onDefault }
				/>
				<SelectableCard
					title={ __( 'Custom User Groups', 'better-wp-security' ) }
					icon={ customUserGroup }
					direction="vertical"
					onClick={ onCustom }
				/>
			</Flex>
		</StyledWrapper>
	);
}
