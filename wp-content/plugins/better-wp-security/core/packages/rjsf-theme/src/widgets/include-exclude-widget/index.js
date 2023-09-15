/**
 * External dependencies
 */
import { partition, find, findIndex } from 'lodash';

/**
 * WordPress dependencies
 */
import { BaseControl, Button } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { speak } from '@wordpress/a11y';
import { BACKSPACE, DELETE } from '@wordpress/keycodes';

/**
 * Internal dependencies
 */
import {
	ActiveDescendantContainer,
	Markup,
} from '@ithemes/security-components';
import './style.scss';

export default function IncludeExcludeWidget( {
	id,
	disabled,
	options,
	value,
	onChange,
	schema,
	autofocus,
	readonly,
} ) {
	const {
		enumOptions,
		enumDisabled = [],
		excludeList: {
			title: excludeTitle = __( 'Excluded', 'better-wp-security' ),
			description: excludeDescription = __(
				'The list of items to exclude from the selection.',
				'better-wp-security'
			),
			button: excludeButton = __( 'Include', 'better-wp-security' ),
		} = {},
		includeList: {
			title: includeTitle = __( 'Included', 'better-wp-security' ),
			description: includeDescription = __(
				'The list of items to include in the selection.',
				'better-wp-security'
			),
			button: includeButton = __( 'Exclude', 'better-wp-security' ),
		} = {},
		title = schema.title,
		description = schema.description,
	} = options;

	useEffect( () => {
		const valid = enumOptions.map( ( option ) => option.value );
		const filtered = value.filter( ( v ) => valid.includes( v ) );

		if ( filtered.length !== value.length ) {
			onChange( filtered );
		}
	}, [ value, enumOptions, onChange ] );

	const [ excludeOptions, includeOptions ] = partition(
		enumOptions,
		( option ) => ! value.includes( option.value )
	);

	return (
		<div className="itsec-rjsf-include-exclude-widget">
			{ title && (
				<BaseControl.VisualLabel>{ title }</BaseControl.VisualLabel>
			) }
			{ description && <p>{ description }</p> }
			<div className="itsec-rjsf-include-exclude-widget__sides" id={ id }>
				<Listbox
					id={ id + '__exclude' }
					options={ excludeOptions }
					label={ excludeTitle }
					description={ excludeDescription }
					button={ excludeButton }
					disabled={ disabled || readonly }
					disabledOptions={ enumDisabled }
					autofocus={ autofocus }
					onToggle={ ( include ) => {
						speak(
							sprintf(
								/* translators: 1. The first item name 2. The second item name. */
								__( 'Moved %1$s to %2$s.', 'better-wp-security' ),
								find( excludeOptions, { value: include } )
									.label,
								includeTitle
							)
						);
						onChange( [ ...value, include ] );
					} }
				/>

				<Listbox
					id={ id + '__include' }
					options={ includeOptions }
					label={ includeTitle }
					description={ includeDescription }
					button={ includeButton }
					disabled={ disabled || readonly }
					disabledOptions={ enumDisabled }
					onToggle={ ( exclude ) => {
						speak(
							sprintf(
								/* translators: 1. The first item name 2. The second item name. */
								__( 'Moved %1$s to %2$s.', 'better-wp-security' ),
								find( includeOptions, { value: exclude } )
									.label,
								excludeTitle
							)
						);
						onChange( value.filter( ( v ) => v !== exclude ) );
					} }
				/>
			</div>
		</div>
	);
}

function Listbox( {
	id,
	label,
	description,
	options,
	button,
	onToggle,
	disabled,
	disabledOptions = [],
	autofocus,
} ) {
	const idPrefix = id + '__option__';
	const [ selected, select ] = useState( '' );
	const onNavigate = ( optionId ) =>
		select( optionId.substr( idPrefix.length ) );
	const onKeyDown = ( { keyCode } ) => {
		if ( ! selected || ( keyCode !== DELETE && keyCode !== BACKSPACE ) ) {
			return;
		}

		const position = findIndex( options, { value: selected } );
		const next =
			position + 1 < options.length ? position + 1 : position - 1;

		onToggle( selected );
		select( options[ next ]?.value || '' );
	};

	if ( selected && ! find( options, { value: selected } ) ) {
		select( '' );
	}

	return (
		<div className="itsec-rjsf-include-exclude-widget__side">
			<BaseControl
				id={ id }
				help={ <Markup noWrap content={ description } /> }
				className="itsec-rjsf-include-exclude-widget__listbox"
			>
				<span
					className="components-base-control__label"
					id={ id + '__label' }
				>
					{ label }
				</span>
				<ActiveDescendantContainer
					role="listbox"
					id={ id }
					active={ selected && idPrefix + selected }
					aria-labelledby={ id + '__label' }
					aria-describedby={ description && id + '__help' }
					onNavigate={ onNavigate }
					onKeyDown={ onKeyDown }
					autoFocus={ autofocus } // eslint-disable-line jsx-a11y/no-autofocus
				>
					{ options.map( ( option ) => (
						// Disable reason: Elements are keyboard controlled by the NavigableContainer.
						// eslint-disable-next-line jsx-a11y/click-events-have-key-events
						<div
							id={ idPrefix + option.value }
							key={ option.value }
							role={
								disabled ||
								disabledOptions.includes( option.value )
									? 'presentation'
									: 'option'
							}
							aria-selected={
								option.value === selected ? true : undefined
							}
							onClick={
								disabled ||
								disabledOptions.includes( option.value )
									? undefined
									: () => select( option.value )
							}
						>
							{ option.label }
						</div>
					) ) }
				</ActiveDescendantContainer>
			</BaseControl>
			<Button
				variant="secondary"
				disabled={ ! selected }
				className="itsec-rjsf-include-exclude-widget__move"
				onClick={ () => onToggle( selected ) }
				aria-keyshortcuts="Delete Backspace"
			>
				{ button }
			</Button>
		</div>
	);
}
