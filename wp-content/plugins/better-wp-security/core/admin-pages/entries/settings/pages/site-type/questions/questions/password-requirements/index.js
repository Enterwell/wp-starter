/**
 * External dependencies
 */
import { AnimatePresence } from 'motion/react';
import { noop } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { createInterpolateElement, useEffect, useState } from '@wordpress/element';
import { lock as icon, chevronRight as registerIcon } from '@wordpress/icons';
import { Flex, Icon, FormToggle } from '@wordpress/components';
import { useReducedMotion } from '@wordpress/compose';

/**
 * SolidWP dependencies
 */
import { Text, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { FeatureToggle, SimpleUserGroupControl } from '../../../../../components';
import LoginSecurity from '../../login-security';
import {
	StyledLoginAction,
	StyledLoginActionText,
	StyledLoginContainer,
	StyledLoginHeading,
	StyledLoginInput,
	StyledLoginInputContainer,
	StyledLoginInputText,
	StyledLoginLabel,
	StyledPasswordRules,
	StyledPasswordStrength,
	StyledPasswordStrengthBubble,
	StyledPasswordStrengthLabel,
	StyledPasswordStrengthMeter,
	StyledProtectedToggle,
	StyledProtectedToggleText,
} from '../../login-security/graphic';

export default function PasswordRequirements( { question, onAnswer, isAnswering } ) {
	const [ state, setState ] = useState( question.answer_schema.default );

	return (
		<LoginSecurity
			headline={ __( '80% of hacks can be attributed to password compromises.', 'better-wp-security' ) }
			reason={ __( 'A strong password improves security by making it significantly harder for hackers to guess their way into your website. Refusing compromised passwords prevents bad actors from using passwords found in breaches, an attack known as “Credential Stuffing.”', 'better-wp-security' ) }
			feature={ question.prompt }
			icon={ icon }
			isAnswering={ isAnswering }
			onContinue={ () => onAnswer( state ) }
			renderGraphic={ () => <Graphic /> }
		>
			<Flex direction="column" gap={ 3 } expanded={ false }>
				{ [ 'strength', 'hibp' ].map( ( requirement ) => (
					<FeatureToggle
						key={ requirement }
						label={ question.answer_schema.properties[ requirement ].label }
						checked={ state[ requirement ] }
						onChange={ ( next ) => setState( { ...state, [ requirement ]: next } ) }
						recommended
					/>
				) ) }
			</Flex>
			<SimpleUserGroupControl
				value={ state.users }
				onChange={ ( next ) => setState( { ...state, users: next } ) }
			/>
			<Text text={ question.description } variant={ TextVariant.MUTED } />

		</LoginSecurity>
	);
}

function Graphic() {
	const reduced = useReducedMotion();
	const [ state, setState ] = useState( reduced ? 'protected' : 'unprotected' );

	useEffect( () => {
		if ( reduced ) {
			return;
		}
		const id = setInterval( () => {
			setState( ( current ) => current === 'unprotected' ? 'protected' : 'unprotected' );
		}, 3000 );

		return () => clearTimeout( id );
	}, [ reduced ] );

	return (
		<>
			<StyledLoginContainer layout>
				<StyledLoginHeading>{ __( 'Register', 'better-wp-security' ) }</StyledLoginHeading>
				<StyledLoginInputContainer>
					<StyledLoginLabel>{ __( 'Email Address', 'better-wp-security' ) }</StyledLoginLabel>
					<StyledLoginInput>
						<StyledLoginInputText>
							{ __( 'example@gmail.com', 'better-wp-security' ) }
						</StyledLoginInputText>
					</StyledLoginInput>
				</StyledLoginInputContainer>
				<StyledLoginInputContainer>
					<StyledLoginLabel>{ __( 'Password', 'better-wp-security' ) }</StyledLoginLabel>
					<StyledLoginInput>
						<AnimatePresence
							initial={ { opacity: 0, width: 0, height: 0 } }
							animate={ { opacity: 1, width: 'auto', height: 'auto' } }
							exit={ { opacity: 0, width: 0, height: 0 } }
						>
							{ state === 'protected' && (
								<StyledPasswordRules>
									{ createInterpolateElement(
										__( '<b>Hint:</b> The password should be at least twelve characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ & ).', 'better-wp-security' ),
										{
											b: <strong />,
										}
									) }
								</StyledPasswordRules>
							) }
						</AnimatePresence>
						<StyledLoginInputText>
							●●●●●●●●
						</StyledLoginInputText>
					</StyledLoginInput>
				</StyledLoginInputContainer>
				<StyledPasswordStrength
					initial={ { opacity: 0, height: 0 } }
					animate={ state === 'protected' ? { opacity: 1, height: 'auto' } : { opacity: 0, height: 0 } }
				>
					<StyledPasswordStrengthLabel>
						{ __( 'Password Strength:', 'better-wp-security' ) }
					</StyledPasswordStrengthLabel>
					<StyledPasswordStrengthMeter>
						<StyledPasswordStrengthBubble />
						<StyledPasswordStrengthBubble />
						<StyledPasswordStrengthBubble />
						<StyledPasswordStrengthBubble />
					</StyledPasswordStrengthMeter>
				</StyledPasswordStrength>
				<StyledLoginAction>
					<StyledLoginActionText>
						{ __( 'Register', 'better-wp-security' ) }
					</StyledLoginActionText>
					<Icon icon={ registerIcon } />
				</StyledLoginAction>
			</StyledLoginContainer>
			<StyledProtectedToggle>
				<FormToggle
					checked={ state === 'protected' }
					onChange={ noop }
				/>
				<StyledProtectedToggleText>
					{ state === 'unprotected' ? __( 'No Password Requirements', 'better-wp-security' ) : __( 'Password Requirements Enabled', 'better-wp-security' ) }
				</StyledProtectedToggleText>
			</StyledProtectedToggle>
		</>
	);
}
