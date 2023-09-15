/**
 * External Dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress Dependencies
 */
import { Card, CardBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * iThemes Dependencies
 */
import { Button, Heading, Surface, List, ListItem, TextSize, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal Dependencies
 */
import { MarkPro } from '@ithemes/security-style-guide';
import { useConfigContext } from '../../../utils';

const StyledBody = styled( CardBody )`
	display: flex;
	flex-direction: column;
	gap: 1rem;
`;

const StyledMarkPro = styled( MarkPro, { shouldForwardProp: ( prop ) => prop !== 'installType' } )`
	g {
		fill: ${ ( { installType } ) => installType === 'free' && '#F8D739' };
	}
`;

const ResourceButton = styled( Button )`
	width: 100%;
	background: #EDEDED !important;

	&:not(:hover):not(:focus) {
		box-shadow: none !important;
	}

	@media (min-width: ${ ( { theme } ) => theme.breaks.huge }px ) {
		justify-content: left;
	}
`;

const links = [
	{
		href: 'https://ithem.es/plugin-vuln-report',
		text: __( 'Weekly WordPress Vulnerability Report', 'better-wp-security' ),
	},
	{
		href: 'https://ithem.es/plugin-get-started',
		text: __( 'Tutorials Library', 'better-wp-security' ),
	},
	{
		href: 'https://ithem.es/plugin-security-blog',
		text: __( 'Security Blogs', 'better-wp-security' ),
	},
	{
		href: 'https://ithem.es/plugin-free-webinars',
		text: __( 'Upcoming Webinars', 'better-wp-security' ),
	},
	{
		href: 'https://ithem.es/plugin-ebooks',
		text: __( 'Security Ebooks', 'better-wp-security' ),
	},
];

export default function ResourcesCard() {
	const { installType } = useConfigContext();

	return (
		<Surface variant="primary" as={ Card }>
			<StyledBody>
				<Heading
					level={ 3 }
					text={ __( 'More Security Resources', 'better-wp-security' ) }
					size={ TextSize.NORMAL }
					variant={ TextVariant.DARK }
					weight={ TextWeight.HEAVY }
					align="center"
					icon={ <StyledMarkPro installType={ installType } /> }
					iconSize={ 24 }
				/>
				<List gap={ 3 }>
					{ links.map( ( { href, text } ) => (
						<ListItem key={ href }>
							<ResourceButton
								variant="secondary"
								href={ href }
								text={ text }
							/>
						</ListItem>
					) ) }
				</List>
			</StyledBody>
		</Surface>
	);
}
