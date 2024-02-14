/**
 * External dependencies
 */
import { Link, useLocation } from 'react-router-dom';
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { arrowLeft } from '@wordpress/icons';

/**
 * SolidWP dependencies
 */
import { Button, Heading, Text, TextSize, TextVariant, TextWeight } from '@ithemes/ui';
import { withNavigate } from '@ithemes/security-hocs';

const StyledLink = styled( Link )`
	margin-bottom: 1rem;
`;

export default function PageHeader() {
	const { pathname } = useLocation();

	return (
		<header>
			{ pathname !== '/tools' && (
				<StyledLink
					to="/tools"
					component={ withNavigate( Button ) }
					icon={ arrowLeft }
					variant="tertiary"
					text={ __( 'Back to list', 'better-wp-security' ) }
				/>
			) }

			<Heading level={ 1 } text={ __( 'Tools', 'better-wp-security' ) } weight={ TextWeight.NORMAL } />
			<Text
				size={ TextSize.SMALL }
				variant={ TextVariant.MUTED }
				text={ __( 'Advanced tools to help you manage your site’s security.', 'better-wp-security' ) }
			/>
		</header>
	);
}
