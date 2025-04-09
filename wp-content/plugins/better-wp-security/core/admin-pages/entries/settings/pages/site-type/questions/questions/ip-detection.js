/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useMemo, useState } from '@wordpress/element';
import { createSlotFill, SelectControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { Button } from '@ithemes/ui';
import { IpDirect, IpProxy } from '@ithemes/security-style-guide';
import { SelectableCard } from '../../../../components';
import Question from '../question';
import { StyledSelectableCardContainer, alignSelfStart } from '../styles';

export const {
	Slot: OnboardSiteTypeIpDetectionSlot,
	Fill: OnboardSiteTypeIpDetectionFill,
} = createSlotFill( 'OnboardSiteTypeIpDetection' );

export default function IpDetection( { question, onAnswer, isAnswering } ) {
	const schema = question.answer_schema;
	const proxyHeaderOptions = useMemo( () =>
		schema.properties.proxy_header.enum.map( ( header, i ) => ( {
			value: header,
			label: schema.properties.proxy_header.enumNames[ i ],
		} ) )
	, [ schema.properties.proxy_header.enum, schema.properties.proxy_header.enumNames ] );

	const [ proxy, setProxy ] = useState( '' );
	const [ proxyHeader, setProxyHeader ] = useState( '' );

	const canSubmit = proxy === 'disabled' || ( proxy === 'manual' && proxyHeader !== '' );

	return (
		<Question
			prompt={ question.prompt }
			description={ question.description }
		>
			<OnboardSiteTypeIpDetectionSlot fillProps={ { proxy, proxyHeader } } />
			<StyledSelectableCardContainer>
				<SelectableCard
					onClick={ () => setProxy( 'disabled' ) }
					title={ __( 'Direct Connection', 'better-wp-security' ) }
					description={ __( 'If your web server is directly exposed to the internet.', 'better-wp-security' ) }
					icon={ IpDirect }
					direction="vertical"
					isSelected={ proxy === 'disabled' }
				/>
				<SelectableCard
					onClick={ () => setProxy( 'manual' ) }
					title={ __( 'Proxy Server', 'better-wp-security' ) }
					description={ __( 'If your web server is behind a proxy server like CloudFlare.', 'better-wp-security' ) }
					icon={ IpProxy }
					direction="vertical"
					isSelected={ proxy === 'manual' }
				/>
			</StyledSelectableCardContainer>
			{ proxy === 'manual' && (
				<SelectControl
					value={ proxyHeader }
					onChange={ setProxyHeader }
					options={ proxyHeaderOptions }
					label={ schema.properties.proxy_header.title }
					help={ schema.properties.proxy_header.description }
				/>
			) }
			<Button
				className={ alignSelfStart }
				variant="primary"
				text={ __( 'Next', 'better-wp-security' ) }
				disabled={ ! canSubmit || isAnswering }
				onClick={ () => onAnswer( {
					proxy,
					proxy_header: proxyHeader,
				} ) }
			/>
		</Question>
	);
}
