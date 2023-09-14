/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { Fragment, useRef } from '@wordpress/element';
import { Button, Dashicon } from '@wordpress/components';
import { useInstanceId } from '@wordpress/compose';
import { UP, DOWN } from '@wordpress/keycodes';

/**
 * Internal dependencies
 */
import { Spinner } from '@ithemes/security-components';
import './style.scss';

function cycleValue( value, total, offset ) {
	const nextValue = value + offset;
	if ( nextValue < 0 ) {
		return total + nextValue;
	} else if ( nextValue >= total ) {
		return nextValue - total;
	}

	return nextValue;
}

export default function Accordion( {
	panels,
	header = 'h3',
	expanded,
	setExpanded,
	isStyled,
	className,
	allowNone,
} ) {
	const id = useInstanceId( Accordion, 'itsec-accordion' );
	const refs = useRef( [] );
	const onTrigger = ( name ) => {
		if ( allowNone && name === expanded ) {
			setExpanded( '' );
		} else {
			setExpanded( name );
		}
	};

	const hasDescription = panels.some( ( panel ) => !! panel.description );

	return (
		<div
			id={ id }
			className={ classnames( 'itsec-accordion', className, {
				'itsec-accordion--styled': isStyled,
				'itsec-accordion--has-description': hasDescription,
			} ) }
		>
			{ panels.map(
				(
					{
						name,
						text,
						className: panelClassName,
						render: Component = TextPanel,
						...rest
					},
					i
				) => (
					<Fragment key={ name }>
						<Header
							id={ id + '__trigger__' + name }
							name={ name }
							{ ...rest }
							as={ header }
							controls={ id + '__panel__' + name }
							isExpanded={ expanded === name }
							onTrigger={ onTrigger }
							refs={ refs }
							index={ i }
							className={ panelClassName }
						/>
						<Component
							name={ name }
							text={ text }
							className={ classnames(
								'itsec-accordion__panel',
								panelClassName,
								{
									'itsec-accordion__panel--is-expanded':
										expanded === name,
								}
							) }
							role="region"
							id={ id + '__panel__' + name }
							aria-labelledby={ id + '__trigger__' + name }
						/>
					</Fragment>
				)
			) }
		</div>
	);
}

function Header( {
	id,
	name,
	title,
	description,
	showSpinner,
	icon,
	controls,
	isExpanded,
	onTrigger,
	className,
	as: Component,
	refs,
	index,
} ) {
	const onKeyDown = ( e ) => {
		if ( e.keyCode !== UP && e.keyCode !== DOWN ) {
			return;
		}

		e.preventDefault();
		const offset = e.keyCode === UP ? -1 : 1;
		const next = cycleValue( index, refs.current.length, offset );
		refs.current[ next ]?.focus();
	};

	const spinner =
		showSpinner === true ? <Spinner size={ 30 } /> : showSpinner;

	return (
		<Component
			className={ classnames( 'itsec-accordion__header', className, {
				'itsec-accordion__header--is-expanded': isExpanded,
				'itsec-accordion__header--has-graphic': spinner || icon,
			} ) }
		>
			<Button
				id={ id + name }
				icon={ isExpanded ? 'arrow-up-alt2' : 'arrow-down-alt2' }
				onClick={ () => onTrigger( name ) }
				aria-expanded={ isExpanded }
				aria-controls={ controls }
				ref={ ( ref ) => ( refs.current[ index ] = ref ) }
				onKeyDown={ onKeyDown }
			>
				<span className="itsec-accordion__header-title">{ title }</span>
				{ description && (
					<span className="itsec-accordion__header-description">
						{ description }
					</span>
				) }
				{ spinner
					? spinner
					: icon && (
						<Dashicon
							icon={ icon }
							className="itsec-accordion__header-icon"
						/>
					) }
			</Button>
		</Component>
	);
}

// eslint-disable-next-line no-unused-vars
function TextPanel( { text, name, ...rest } ) {
	return (
		<div { ...rest }>
			<p>{ text }</p>
		</div>
	);
}
