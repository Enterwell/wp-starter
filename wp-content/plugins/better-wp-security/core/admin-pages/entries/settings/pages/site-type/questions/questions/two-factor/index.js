/**
 * External dependencies
 */
import { Markup } from 'interweave';
import { EventType } from '@rive-app/react-canvas';
import { noop } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { createInterpolateElement, useEffect, useState } from '@wordpress/element';
import { login as icon } from '@wordpress/icons';
import { FormToggle } from '@wordpress/components';
import { useReducedMotion } from '@wordpress/compose';

/**
 * SolidWP dependencies
 */
import { Text, TextVariant } from '@ithemes/ui';
import { usePreloadedRiveGraphic } from '@ithemes/security-ui';

/**
 * Internal dependencies
 */
import { FeatureToggle, SimpleUserGroupControl } from '../../../../../components';
import { useConfigContext } from '../../../../../utils';
import LoginSecurity from '../../login-security';
import { StyledProtectedToggle, StyledProtectedToggleText } from '../../login-security/graphic';

export default function TwoFactor( { question, onAnswer, isAnswering } ) {
	const [ state, setState ] = useState( question.answer_schema.default );
	const { installType } = useConfigContext();

	return (
		<LoginSecurity
			headline={ __( '90% of targeted account takeover attacks can be prevented by using Two-Factor.', 'better-wp-security' ) }
			reason={ __( 'Two-Factor combines something you know (your password), with something you have. If an attacker is able to compromise your password, Two-Factor can stop them in their tracks because they won’t have access to your phone or email.', 'better-wp-security' ) }
			feature={ question.prompt }
			icon={ icon }
			upsell={ createInterpolateElement(
				__( '<b>Require Two-Factor</b> is a Pro feature.', 'better-wp-security' ),
				{
					b: <strong />,
				}
			) }
			isAnswering={ isAnswering }
			onContinue={ () => onAnswer( state ) }
			renderGraphic={ () => <Graphic /> }
		>
			<FeatureToggle
				label={ installType === 'pro' ? __( 'Require Two-Factor', 'better-wp-security' ) : __( 'Allow Two-Factor', 'better-wp-security' ) }
				checked={ state.enabled }
				onChange={ ( next ) => setState( { ...state, enabled: next } ) }
				recommended
			/>
			<SimpleUserGroupControl
				value={ state.users }
				onChange={ ( next ) => setState( { ...state, users: next } ) }
			/>
			<Text variant={ TextVariant.MUTED }>
				<Markup content={ question.description } noWrap />
			</Text>
		</LoginSecurity>
	);
}

function Graphic() {
	const reduced = useReducedMotion();
	const [ isProtected, setIsProtected ] = useState( reduced );

	const { RiveComponent, rive } = usePreloadedRiveGraphic( reduced ? 'onboard-two-factor-reduced' : 'onboard-two-factor', {
		autoplay: true,
		stateMachines: 'State Machine | Loop',
	} );
	useEffect( () => {
		rive?.on( EventType.RiveEvent, ( e ) => {
			if ( e.data.name === 'toggle_on' ) {
				setIsProtected( true );
			} else if ( e.data.name === 'toggle_off' ) {
				setIsProtected( false );
			}
		} );
	}, [ rive ] );

	return (
		<>
			<div style={ { width: 297, height: 372, marginTop: -40 } }>
				<RiveComponent />
			</div>
			<StyledProtectedToggle>
				<FormToggle
					checked={ isProtected }
					onChange={ noop }
				/>
				<StyledProtectedToggleText>
					{ isProtected ? __( 'Two-Factor Enabled', 'better-wp-security' ) : __( 'Standard Login', 'better-wp-security' ) }
				</StyledProtectedToggleText>
			</StyledProtectedToggle>
		</>
	);
}
