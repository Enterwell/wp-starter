/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Modal } from '@wordpress/components';

/**
 * SolidWP dependencies
 */
import { Surface, Text } from '@ithemes/ui';

export const StyledWelcomeModal = styled( Modal )`
	max-width: 600px;
	min-width: 480px;

	.components-modal__header {
		padding: 0 1.25rem;
		& button:hover {
			color: ${ ( { theme } ) => theme.colors.secondary.darker20 };
		}
		& button:focus {
			box-shadow: 0 0 0 2px ${ ( { theme } ) => theme.colors.primary.base };
		}
	}
	
	.components-modal__content {
		padding: 0 1.25rem 2rem;
	}

	.components-modal__header-heading {
		font-size: ${ ( { theme } ) => theme.sizes.text.large };
	}
`;

export const StyledLogoBanner = styled( Surface )`
	display: flex;
	align-items: center;
	gap: 1.5rem;
	background: linear-gradient(94deg, #292929 66.78%, #8645D1 141.05%);
	margin: 1rem 0 0;
	padding: 1.75rem;
`;

export const StyledTextContainer = styled.div`
	display: flex;
	flex-direction: column;
	gap: 1rem;
`;

export const StyledCard = styled.div`
	display: flex;
	flex-direction: column;
	gap: 2.5rem;
`;

export const StyledHeader = styled( Surface )`
	display: flex;
	justify-content: center;
	padding: 1.25rem;
	background: linear-gradient(94deg, #292929 66.78%, #8645D1 141.05%);
`;

export const StyledFeature = styled.div`
	display: flex;
	gap: 1.25rem;
	align-items: center;
`;

export const StyledFeatureTextContainer = styled.div`
	display: flex;
	flex-direction: column;
	gap: 0.75rem;
`;

export const StyledFeaturesContainer = styled.div`
	display: flex;
	flex-direction: column;
	gap: 2.5rem;
`;

export const StyledThumbnailContainer = styled.div`
	width: 200px;
	height: 168px;
`;

export const StyledThumbnail = styled.img`
	width: 200px;
	height: 168px;
`;

export const StyledUpgradeText = styled( Text )`
	max-width: 400px;
	margin: 0 auto;
	& a {
		color: ${ ( { theme } ) => theme.colors.primary.base };
	}
`;

export const StyledVideoPlayer = styled.div`
	padding: 60% 0 0 0;
	position: relative;
	width: 552px;
`;

export const StyledIframe = styled.iframe`
	position: absolute;
	top: 0;
	left: 0;
	width: 552px;
	height: 100%;
`;
