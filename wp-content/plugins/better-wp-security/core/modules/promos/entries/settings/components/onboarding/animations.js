/**
 * External Dependencies
 */
import { keyframes } from '@emotion/css';
import { times } from 'lodash';
import styled from '@emotion/styled';

/**
 * Internal Dependencies
 */
import {
	MarkPro,
	RedBar,
	YellowBar,
	LightGreenBar,
	GreenBar,
	LogoProColorAccent,
} from '@ithemes/security-style-guide';

/**
 * Animations
 */
const StyledLogoProColorAccentReveal = keyframes`
	0% {
		transform: scale(0);	
	}
	
	100% {
		transform: scale(1);
	}
`;

const Reveal = keyframes`
	from, 0%, 50%, to {
		width: 0%;
	}
	
	100% {
		width: 100%;
	}
`;

const LogoSlide = keyframes`
	0% {
		left: 0%;
	}
	10% {
		left: 21%;
	}
	30% {
		left: 21%;
	}
	40% {
		left: 45%;
	}
	60% {
		left: 45%;
	}
	70% {
		left: 70%;
	}
	100% {
		left: 70%;
	}
`;

const ExplosionSlide = keyframes`
	0% {
		left: 0%;
	}
	10% {
		left: 22.5%;
	}
	30% {
		left: 22.5%;
	}
	40% {
		left: 46.5%;
	}
	60% {
		left: 46.5%;
	}
	70% {
		left: 71.5%;
	}
	100% {
		left: 71.5%;
	}
`;

const Explosion = keyframes`
	0% {
		top: 100%;
	}
	33%, 100% {
		top: -50%;
	}
`;

/**
 * Components
 */
const StyledBarsContainer = styled.div`
	display: grid;
	grid-template-columns: repeat(4, 1fr);
	grid-template-rows: 1fr;
	position: relative;
`;

const StyledRedBar = styled( RedBar )`
	width: 0%;
	animation: ${ Reveal } 2s ease;
	animation-fill-mode: forwards;
`;

const StyledYellowBar = styled( YellowBar )`
	width: 0%;
	animation: ${ Reveal } 2s ease;
	animation-delay: 2s;
	animation-fill-mode: forwards;
`;

const StyledLightGreenBar = styled( LightGreenBar )`
	width: 0%;
	animation: ${ Reveal } 2s ease;
	animation-delay: 4s;
	animation-fill-mode: forwards;
`;

const StyledGreenBar = styled( GreenBar )`
	width: 0%;
	animation: ${ Reveal } 2s ease;
	animation-delay: 6s;
	animation-fill-mode: forwards;
`;

const FireworkDiv = styled.div`
	position: absolute;
	top: -25px;
	left: 0px;
	transform: scale( .25 );
	animation: ${ ExplosionSlide } 7s ease 1s forwards;
`;

const ExplosionDiv = styled.div`
	position: absolute;
	left: -2px;
	bottom: 0;
	width: 4px;
	height: 80px;
	transform-origin: 50% 100%;
	overflow: hidden;
	display: none;
	
	@media ( min-width: ${ ( { theme } ) => theme.breaks.medium }px ) {
		display: block;
	}
	
	&:nth-of-type(1) {
		transform: rotate( 0deg ) translateY( -15px );
	}
	
	&:nth-of-type(2) {
		transform: rotate(30deg) translateY(-15px);
	}
	
	&:nth-of-type(3) {
		transform: rotate(60deg) translateY(-15px);
	}
	
	&:nth-of-type(4) {
		transform: rotate(90deg) translateY(-15px);
	}
	
	&:nth-of-type(5) {
		transform: rotate(120deg) translateY(-15px);
	}
	
	&:nth-of-type(6) {
		transform: rotate(150deg) translateY(-15px);
	}

	&:nth-of-type(7) {
		transform: rotate(180deg) translateY(-15px);
	}
	
	&:nth-of-type(8) {
		transform: rotate(210deg) translateY(-15px);
	}
	
	&:nth-of-type(9) {
		transform: rotate(240deg) translateY(-15px);
	}
	
	&:nth-of-type(10) {
		transform: rotate(270deg) translateY(-15px);
	}
	
	&:nth-of-type(11) {
		transform: rotate(300deg) translateY(-15px);
	}
	
	&:nth-of-type(12) {
		transform: rotate(330deg) translateY(-15px);
	}
	
	&:before {
		content: '';
		position: absolute;
		left: 0;
		right: 0;
		top: 100%;
		height: 40px;
		background-color: #fff;
		animation: ${ Explosion } 2s ease-in-out 2.1s;
		animation-iteration-count: 3;
	}
`;

const StyledMarkPro = styled( MarkPro )`
	float: left;
	width: .9rem;
	position: absolute;
	top: -1.25rem;
	left: 0rem;
	
	g {
		fill: #FFF;
	}
	animation: ${ LogoSlide } 7s ease 1s forwards;
	
	@media (min-width: ${ ( { theme } ) => theme.breaks.medium }px ) {
		width: 1.5rem;
		top: -2.25rem;
	}
`;

const StyledLogoProColorAccent = styled( LogoProColorAccent )` 
	width: 3rem !important;
	display: block;
	margin-left: auto;
	margin-right: 6%;
	transform: scale(0);
	
	@media (min-width: ${ ( { theme } ) => theme.breaks.medium }px ) {
		width: 5rem !important;
	}
	
	animation: ${ StyledLogoProColorAccentReveal } 1s ease forwards;
	animation-delay: 7s;
`;

export default function ProPromotionProgressBar() {
	return (
		<div>
			<StyledLogoProColorAccent />
			<StyledBarsContainer>
				<StyledMarkPro />
				<FireworkDiv>
					{ times( 12, ( i ) => <ExplosionDiv key={ i } /> ) }
				</FireworkDiv>
				<StyledRedBar width={ '100%' } />
				<StyledYellowBar width={ '100%' } />
				<StyledLightGreenBar width={ '100%' } />
				<StyledGreenBar width={ '100%' } />
			</StyledBarsContainer>
		</div>
	);
}
