/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { useInstanceId } from '@wordpress/compose';
import { Children, isValidElement } from '@wordpress/element';

/**
 * Solid dependencies
 */
import { Heading, TextSize, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { ErrorList } from '@ithemes/security-ui';
import './style.scss';

export default function PrimaryForm( {
	onSubmit = () => {},
	id,
	hasPadding,
	children,
	errors,
	apiError,
	className,
} ) {
	const generatedId = useInstanceId( PrimaryForm, 'itsec-primary-form' );
	id = id || generatedId;

	return (
		<div className={ classnames(
			'itsec-primary-form',
			className,
			hasPadding && 'itsec-primary-form--has-padding'
		) }>
			<ErrorList errors={ errors } apiError={ apiError } />
			<form
				id={ id }
				onSubmit={ ( e ) => {
					e.preventDefault();
					onSubmit();
				} }
			>
				{ children }
			</form>
		</div>
	);
}

export function PrimaryFormSection( { heading, className, children } ) {
	if ( ! Children.toArray( children ).some( isValidElement ) ) {
		return null;
	}

	return (
		<div
			className={ classnames( 'itsec-primary-form__section', className ) }
		>
			{ heading && (
				<Heading
					level={ 3 }
					variant={ TextVariant.DARK }
					size={ TextSize.NORMAL }
					weight={ TextWeight.HEAVY }
					className="itsec-primary-form__section-title"
				>
					{ heading }
				</Heading>
			) }

			{ children }
		</div>
	);
}
