/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { createInterpolateElement, useState } from '@wordpress/element';

/**
 * SolidWP dependencies
 */
import { TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { WelcomeFlowBanner } from './index';
import VideoThumbnail from './images/video-thumbnail.png';
import {
	StyledCard,
	StyledUpgradeText,
	StyledVideoPlayer,
	StyledIframe,
} from '../styles';

export function CardFour( { installType } ) {
	const [ showVideo, setShowVideo ] = useState( false );
	const onClick = () => {
		setShowVideo( true );
	};
	return (
		<StyledCard>
			<WelcomeFlowBanner text={ __( 'Learn more about SolidWP!', 'better-wp-security' ) } />

			{ ! showVideo && (
				<input
					type="image"
					src={ VideoThumbnail }
					alt={ __( 'Click here to watch a video introducing SolidWP', 'better-wp-security' ) }
					onClick={ onClick }
				/>
			) }

			{ showVideo && (
				<StyledVideoPlayer>
					<div>
						<StyledIframe
							src="https://player.vimeo.com/video/863249227?h=deb6ff1117&autoplay=1&title=0&byline=0&portrait=0"
							allow="autoplay; fullscreen; picture-in-picture"
							allowFullScreen
							title="Welcome to SolidWP"
						/>
					</div>
				</StyledVideoPlayer>
			) }

			{ installType === 'free' ? (
				<StyledUpgradeText
					align="center"
					variant={ TextVariant.MUTED }
					text={ createInterpolateElement(
						__( 'Learn more about the rebrand <a>here</a>.', 'better-wp-security' ),
						{
							// eslint-disable-next-line jsx-a11y/anchor-has-content
							a: <a href="https://go.solidwp.com/security-free-learn-more-solidwp" />,
						}
					) }
				/>
			) : (
				<StyledUpgradeText
					align="center"
					variant={ TextVariant.MUTED }
					text={ createInterpolateElement(
						__( 'Learn more about the rebrand <a>here</a>.', 'better-wp-security' ),
						{
							// eslint-disable-next-line jsx-a11y/anchor-has-content
							a: <a href="https://go.solidwp.com/security-pro-learn-more-solidwp" />,
						}
					) }
				/>
			) }
		</StyledCard>
	);
}
