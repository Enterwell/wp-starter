/**
 * External dependencies
 */
import { AnimatePresence, useTime, useMotionValueEvent } from 'motion/react';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { shield as icon, info as tooltipIcon, lock as lockIcon } from '@wordpress/icons';
import { Flex, Icon, Tooltip } from '@wordpress/components';
import { useReducedMotion } from '@wordpress/compose';

/**
 * SolidWP dependencies
 */
import { Text, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { FeatureToggle } from '../../../../../components';
import LoginSecurity from '../../login-security';
import {
	StyledLockContainer,
	StyledLockOverlay,
	StyledLoginContainer,
	StyledLoginErrorText,
	StyledLoginInput,
	StyledLoginInputContainer,
	StyledLoginInputText,
	StyledLoginLabel,
} from '../../login-security/graphic';

export default function Firewall( { question, onAnswer, isAnswering } ) {
	const [ state, setState ] = useState( question.answer_schema.default );

	return (
		<LoginSecurity
			headline={ __( 'Attackers can make thousands of login attempts before you even notice.', 'better-wp-security' ) }
			reason={ __( 'Brute Force protection slows down attackers who try to guess login credentials for your site.', 'better-wp-security' ) }
			feature={ question.prompt }
			icon={ icon }
			isAnswering={ isAnswering }
			onContinue={ () => onAnswer( state ) }
			renderGraphic={ () => <Graphic /> }
		>
			<Flex direction="column" gap={ 3 } expanded={ false }>
				<FeatureToggle
					label={ __( 'Local Brute Force', 'better-wp-security' ) }
					checked={ state[ 'brute-force' ] }
					onChange={ ( next ) => setState( { ...state, 'brute-force': next } ) }
					recommended
				/>
				<FeatureToggle
					label={ __( 'Network Brute Force', 'better-wp-security' ) }
					checked={ state[ 'network-brute-force' ] }
					onChange={ ( next ) => setState( { ...state, 'network-brute-force': next } ) }
					recommended
				>
					<Tooltip text={ __( 'Enabling this will send data about login attempts to SolidWP servers.', 'better-wp-security' ) }>
						<span style={ { lineHeight: 0 } }><Icon icon={ tooltipIcon } /></span>
					</Tooltip>
				</FeatureToggle>

				<Text text={ question.description } variant={ TextVariant.MUTED } />
			</Flex>
		</LoginSecurity>
	);
}

/**
 * @typedef {'empty'|'filled'|'invalid'|'overlay'|'locked'} State
 * @typedef {{state: State, duration: number}[]} StateSequence
 */

/** @member {StateSequence} */
const sequence = [
	...[].concat( ...Array( 2 ).fill( [
		{ state: 'empty', duration: 1000 },
		{ state: 'filled', duration: 1000 },
		{ state: 'invalid', duration: 2000 },
	] ) ),
	{ state: 'empty', duration: 1000 },
	{ state: 'filled', duration: 1000 },
	{ state: 'invalid', duration: 1000 },
	{ state: 'overlay', duration: 1000 },
	{ state: 'locked', duration: 6000 },
];
const sequenceLength = sequence.reduce( ( sum, { duration } ) => sum + duration, 0 );

/**
 * Gets the current sequence state that should be shown.
 *
 * @param {number} time The length of time the component has been visible.
 * @return {State} The current state.
 */
function getSequenceState( time ) {
	const loopTime = time % sequenceLength;
	let accTime = 0;

	for ( const state of sequence ) {
		accTime += state.duration;

		if ( loopTime < accTime ) {
			return state.state;
		}
	}

	return sequence[ sequence.length - 1 ].state;
}

function Graphic() {
	const reduced = useReducedMotion();
	const time = useTime();
	const [ state, setState ] = useState(
		/** @type {State} */
		reduced ? 'locked' : 'empty'
	);
	useMotionValueEvent( time, 'change', ( value ) => ! reduced && setState( getSequenceState( value ) ) );

	return (
		<StyledLoginContainer layout>
			<AnimatePresence>
				{ ( state === 'locked' || state === 'overlay' ) && (
					<Lock state={ state } />
				) }
			</AnimatePresence>
			<StyledLoginInputContainer>
				<StyledLoginLabel>{ __( 'Username or Email Address', 'better-wp-security' ) }</StyledLoginLabel>
				<StyledLoginInput>
					<StyledLoginInputText initial={ { width: 0 } } animate={ { width: state === 'empty' ? 1 : 'auto' } } transition={ { duration: 0.5 } }>
						{ state !== 'empty' && __( 'example@gmail.com', 'better-wp-security' ) }
					</StyledLoginInputText>
				</StyledLoginInput>
				<AnimatePresence>
					{ ( state === 'invalid' || state === 'overlay' || state === 'locked' ) && (
						<StyledLoginErrorText initial={ { opacity: 0, height: 0 } } animate={ { opacity: 1, height: 'auto' } } exit={ { opacity: 0, height: 0 } }>
							{ __( 'We do not recognize your email', 'better-wp-security' ) }
						</StyledLoginErrorText>
					) }
				</AnimatePresence>
			</StyledLoginInputContainer>
			<StyledLoginInputContainer>
				<StyledLoginLabel>{ __( 'Password', 'better-wp-security' ) }</StyledLoginLabel>
				<StyledLoginInput>
					<StyledLoginInputText initial={ { width: 0 } } animate={ { width: state === 'empty' ? 1 : 'auto' } } transition={ { duration: 0.5 } }>
						{ state !== 'empty' && '●●●●●●●●' }
					</StyledLoginInputText>
				</StyledLoginInput>
				<AnimatePresence>
					{ ( state === 'invalid' || state === 'overlay' || state === 'locked' ) && (
						<StyledLoginErrorText initial={ { opacity: 0, height: 0 } } animate={ { opacity: 1, height: 'auto' } } exit={ { opacity: 0, height: 0 } }>
							{ __( 'We do not recognize your password', 'better-wp-security' ) }
						</StyledLoginErrorText>
					) }
				</AnimatePresence>
			</StyledLoginInputContainer>
		</StyledLoginContainer>
	);
}

/**
 * @param {'overlay'|'locked'} state
 */
function Lock( { state } ) {
	return (
		<StyledLockOverlay initial={ { opacity: 0 } } animate={ { opacity: 1 } } exit={ { opacity: 0 } }>
			<AnimatePresence>
				{ state === 'locked' && (
					<StyledLockContainer
						initial={ { width: 0, height: 0, padding: 0 } }
						animate={ { width: 120, height: 120, padding: 20 } }
						exit={ { width: 0, height: 0, padding: 0 } }
					>
						<Icon icon={ lockIcon } size={ 80 } />
					</StyledLockContainer>
				) }
			</AnimatePresence>
		</StyledLockOverlay>
	);
}
