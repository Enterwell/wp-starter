/**
 * External dependencies
 */
import styled from '@emotion/styled';
import { keyframes, css } from '@emotion/react';

/**
 * WordPress dependencies
 */
import { Icon, Spinner } from '@wordpress/components';
import { check, close, lock, warning } from '@wordpress/icons';

/**
 * SolidWP dependencies
 */
import { Button, Text } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { STATUS_BUSY, STATUS_DONE, STATUS_FAILED, STATUS_WAITING } from '../../store/constant';

const fadeIn = keyframes`
	from {
		opacity: 0;
	}

	to {
		opacity: 1;
	}
`;

export function StatusIndicator( { status, hasIssues, isStep } ) {
	switch ( status ) {
		case 'upgrade':
			return <StyledUpgradeIcon icon={ lock } size={ 16 } isStep={ isStep } />;
		case STATUS_WAITING:
			return <StyledWaitingStatus isStep={ isStep } />;
		case STATUS_BUSY:
			return <StyledProgressSpinner isStep={ isStep } />;
		case STATUS_DONE:
			if ( hasIssues ) {
				return <StyledWarningIcon icon={ warning } size={ 16 } isStep={ isStep } />;
			}

			return <StyledSuccessIcon icon={ check } size={ 16 } isStep={ isStep } />;
		case STATUS_FAILED:
		default:
			return <StyledErrorIcon icon={ close } size={ 16 } isStep={ isStep } />;
	}
}

export function progressBarColor( status, index, length, side, installType = 'pro' ) {
	if ( installType === 'free' && side === 'left' ) {
		if ( index === 0 ) {
			return '#333333';
		}
		if ( index === 20 ) {
			return '';
		}
	} else {
		if ( index === 0 && side === 'left' ) {
			return '';
		}
		if ( index === length - 1 && side === 'right' ) {
			return '';
		}
	}

	switch ( status ) {
		case STATUS_WAITING:
			return '#cecece';
		case 'upgrade':
			return '#333333';
		default:
			return '#6817C5';
	}
}

export const StyledProgressContainer = styled.div`
	position: relative;
	display: flex;
	flex-direction: column;
	align-items: center;
	padding: 0.5rem 0;
	overflow: auto;
`;

export const StyledProgressBar = styled.div`
	display: grid;
	grid-auto-columns: min-content;
	grid-auto-flow: column;
	margin: 0 auto;
	opacity: 1;
	animation: 0.5s forwards;
	animation-name: ${ ( { isComplete } ) => isComplete && css`${ fadeIn }` };
`;

export const StyledScanComponent = styled.div`
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 0.5rem;
	position: ${ ( { isStep } ) => isStep && 'absolute' };
	left: ${ ( { isStep } ) => isStep && '50%' };
	transform: ${ ( { isStep } ) => isStep && 'translate(-50%, 0)' };
	z-index: 2;
	transition: opacity 1.25s ease-in-out .5s;
	opacity: ${ ( { isStep } ) => ! isStep || isStep === 'current' ? 1 : 0 };
	animation: 1.75s 1.75s forwards;
	animation-name: ${ ( { isStep } ) => isStep === 'next' && css`${ fadeIn }` };

	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.small }px) {
		gap: 1rem;
	}
`;

export const StyledAnimatedComponentContainer = styled.div`
	position: relative;
	height: 120px;
`;

export const StyledIconRow = styled.div`
	display: flex;
	align-items: center;
	align-self: stretch;
	position: relative;
	height: 20px;

	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.small }px) {
		height: 40px;
	}
`;

export const StyledProgressTrack = styled.div`
	background: ${ ( { background } ) => background };
	border-top: ${ ( { background } ) => background && '2px solid #F6F7F7' };
	border-bottom: ${ ( { background } ) => background && '2px solid #F6F7F7' };
	min-width: 50%;
	width: 2rem;
	height: 0.3rem;
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.small }px) {
		height: 1rem;
		width: 2.75rem;
		border-width: 4px;
	}
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.xlarge }px) {
		width: 3.5rem;
	}
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.wide }px) {
		width: 4.0rem;
	}
`;

const StyledStatusIndicator = ( { theme, isStep } ) => css`
	position: ${ isStep ? 'static' : 'absolute' } !important;
	left: calc(50%);
	transform: ${ ! isStep && 'translate(-50%, 0)' };
	height: 20px !important;
	width: 20px !important;
	border: 2px solid ${ theme.colors.surface.tertiary };

	@media screen and (min-width: ${ theme.breaks.small }px) {
		left: calc(50%);
		height: 40px !important;
		width: 40px !important;
		border-width: 6px;
	}
`;

const StyledWaitingStatus = styled.div`
	${ StyledStatusIndicator };
	background: #cecece;
	border-radius: 50%;
	border-color: ${ ( { theme } ) => theme.colors.surface.primary };
`;

const StyledProgressSpinner = styled( Spinner )`
	${ StyledStatusIndicator };
	color: ${ ( { theme } ) => theme.colors.primary.darker20 } !important;
	background: white;
	border-radius: 50%;
	margin: 0 !important;
	animation: ease-in 250ms;
 	animation-name: ${ ( { isStep } ) => isStep && css`${ fadeIn }` };
`;

const StyledStatusIcon = styled( Icon, {
	shouldForwardProp: ( propName ) => propName !== 'isStep',
} )`
	${ StyledStatusIndicator };
	fill: white;
	border-radius: 50%;
	animation: ease-in 250ms;
	animation-name: ${ ( { isStep } ) => isStep && css`${ fadeIn }` };
`;

const StyledUpgradeIcon = styled( StyledStatusIcon ) `
	background: ${ ( { theme } ) => theme.colors.surface.dark };
`;

const StyledWarningIcon = styled( StyledStatusIcon ) `
	background: #FFC518;
`;

const StyledSuccessIcon = styled( StyledStatusIcon ) `
	background: ${ ( { theme } ) => theme.colors.surface.primaryAccent };
`;

const StyledErrorIcon = styled( StyledStatusIcon )`
	background: #D63638;
`;

export const StyledTextContainer = styled.div`
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	margin-top: -0.5rem;
`;

export const StyledCaret = styled.span`
	content: ' ';
	height: 0;
	border: 0.5rem solid transparent;
	border-bottom-color: ${ ( { status, theme } ) => status === 'upgrade' ? theme.colors.surface.dark : theme.colors.surface.primaryAccent };
`;

export const StyledComponentText = styled( Text )`
	display: flex;
	text-align: center;
	justify-content: center;
	align-items: center;
	background: ${ ( { status, theme } ) => status === 'upgrade' ? theme.colors.surface.dark : theme.colors.surface.primaryAccent };
	color: white;
	padding: 0.5rem 0.875rem;
	width: 56px;
	height: 56px;
	border-radius: 8px;
	font-size: 11px;
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.small }px) {
		font-size: 0.875rem;
		width: 80px;
	}
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.xlarge }px) {
		width: 104px;
	}
`;

export const StyledUpgradeButton = styled( Button )`
	border-radius: 2px;
	padding: 4px 2px;
	font-size: 0.6875rem;
	&:hover {
		background: ${ ( { theme } ) => theme.colors.surface.secondary } !important;
		color: ${ ( { theme } ) => theme.colors.text.accent }
	}
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.medium }px) {
		font-size: ${ ( { theme } ) => theme.sizes.text.normal }rem;
		padding: 0.5rem 0.875rem;
	}
`;
