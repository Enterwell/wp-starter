/**
 * External dependencies
 */
import styled from '@emotion/styled';
import { keyframes } from '@emotion/react';
import { motion } from 'motion/react';

const typing = keyframes`
	from, to { opacity: 0; }
	50% { opacity: 1; }
`;

export const StyledLoginContainer = motion.create( styled.div`
	position: relative;
	width: 248px;
	display: flex;
	flex-direction: column;
	justify-content: flex-start;
	gap: 16px;
	padding: 24px 16px;
	border-radius: 4px;
	border: solid 1px #8a9ea8;
	background-color: #fff;
` );

export const StyledLoginHeading = styled.span`
	font-size: 20px;
	line-height: 1.4;
	text-align: center;
	color: #6c6c6c;
`;

export const StyledLoginInputContainer = styled.div`
	display: flex;
	flex-direction: column;
`;

export const StyledLoginLabel = styled.span`
	flex-grow: 0;
	font-size: 13px;
	line-height: 1.23;
	text-align: left;
	color: #6c6c6c;
`;

export const StyledLoginInput = styled.div`
	margin: 4px 0;
	height: 40px;
	padding: 12px;
	border: solid 1px #e7e7e7;
	position: relative;
`;

export const StyledLoginInputText = motion.create( styled.span`
	display: inline-flex;
	align-items: center;
	gap: 1px;
	overflow: hidden;
	font-size: 13px;
	line-height: 1.23;
	letter-spacing: normal;
	text-align: left;
	color: #6c6c6c;

	&:after {
		content: '';
		height: 16px;
		background-color: #6c6c6c;
		display: inline-block;
		width: 1px;
		vertical-align: middle;
		animation: .75s ${ typing } infinite;
	}
` );

export const StyledLoginErrorText = motion.create( styled.span`
	display: inline-block;
	overflow: hidden;
	flex-grow: 0;
	font-size: 11px;
	line-height: 1.45;
	text-align: left;
	color: #b32d2e;
` );

export const StyledLoginAction = styled.div`
	align-self: stretch;
	flex-grow: 0;
	display: flex;
	flex-direction: row;
	justify-content: center;
	align-items: center;
	gap: 4px;
	padding: 12px 24px;
	border-radius: 4px;
	background-color: #232323;
	
	svg {
		fill: #fff;
	}
`;

export const StyledLoginActionText = styled.span`
	flex-grow: 0;
	font-size: 16px;
	font-weight: 500;
	line-height: 1.5;
	text-align: center;
	color: #fff;
`;

export const StyledProtectedToggle = styled.div`
	display: flex;
	flex-direction: column;
	gap: 8px;
	align-items: center;
	position: absolute;
	bottom: 30px;
`;

export const StyledProtectedToggleText = styled.span`
	font-size: 13px;
	line-height: 1.23;
	text-align: center;
	color: #b6b6b6;
`;

export const StyledLockOverlay = motion.create( styled.div`
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	z-index: 1;
	display: flex;
	align-items: center;
	justify-content: center;
	background-color: rgba(3, 3, 3, 0.3);
` );

export const StyledLockContainer = motion.create( styled.div`
	width: 120px;
	height: 120px;
	padding: 20px;
	background-color: #6817c5;
	display: flex;
	justify-content: center;
	align-items: center;
	border-radius: 50%;
	z-index: 2;
	
	svg {
		fill: #fbf9ff;
	}
` );

export const StyledPasswordStrength = motion.create( styled.div`
	display: flex;
	flex-direction: column;
	gap: 8px;
` );

export const StyledPasswordStrengthLabel = styled.span`
	flex-grow: 0;
	font-size: 11px;
	line-height: 1.45;
	text-align: left;
	color: #6c6c6c;
`;

export const StyledPasswordStrengthMeter = styled.div`
	display: grid;
	grid-template-columns: repeat(4, 1fr);
	gap: 4px;
`;

export const StyledPasswordStrengthBubble = styled.span`
	height: 4px;
	border-radius: 10px;
	background: #858585;
	
	&:nth-child(2) {
		background: #6c6c6c;
	}
	
	&:nth-child(3) {
		background: #545454;
	}
	
	&:nth-child(4) {
		background: #333333;
	}
`;

export const StyledPasswordRules = styled.div`
	position: absolute;
	top: -10px;
	left: 20px;
	right: -10px;
	transform: translateY(-100%);
	font-size: 11px;
	line-height: 14px;
	text-align: left;
	color: #6c6c6c;

	/* triangle dimension */
	--a: 90deg; /* angle */
	--h: 1em;   /* height */

	--p: 30%;  /* triangle position (0%:left 100%:right) */
	--r: 8px; /* the radius */
	--b: 1px; /* border width  */
	--c1: #232323;
	--c2: #fff;

	padding: 1em;
	border-radius: var(--r) var(--r) min(var(--r),100% - var(--p) - var(--h)*tan(var(--a)/2)) min(var(--r),var(--p) - var(--h)*tan(var(--a)/2))/var(--r);
	clip-path: polygon(0 100%,0 0,100% 0,100% 100%,
	min(100%,var(--p) + var(--h)*tan(var(--a)/2)) 100%,
	var(--p) calc(100% + var(--h)),
	max(0%  ,var(--p) - var(--h)*tan(var(--a)/2)) 100%);
	background: var(--c1);
	border-image: conic-gradient(var(--c1) 0 0) fill 0/
    var(--r) max(0%,100% - var(--p) - var(--h)*tan(var(--a)/2)) 0 max(0%,var(--p) - var(--h)*tan(var(--a)/2))/0 0 var(--h) 0;
	
	&:before {
		content: "";
		position: absolute;
		z-index: -1;
		inset: 0;
		padding: var(--b);
		border-radius: inherit;
		clip-path: polygon(0 100%,0 0,100% 0,100% 100%,
		min(100% - var(--b),var(--p) + var(--h)*tan(var(--a)/2) - var(--b)*tan(45deg - var(--a)/4)) calc(100% - var(--b)),
		var(--p) calc(100% + var(--h) - var(--b)/sin(var(--a)/2)),
		max(       var(--b),var(--p) - var(--h)*tan(var(--a)/2) + var(--b)*tan(45deg - var(--a)/4)) calc(100% - var(--b)));
		background: var(--c2) content-box;
		border-image: conic-gradient(var(--c2) 0 0) fill 0/var(--r) max(var(--b),100% - var(--p) - var(--h)*tan(var(--a)/2)) 0 max(var(--b),var(--p) - var(--h)*tan(var(--a)/2))/0 0 var(--h) 0;
	}
`;
