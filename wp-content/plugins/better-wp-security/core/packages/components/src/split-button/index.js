/**
 * External dependencies
 */
import classnames from 'classnames';
import { identity } from 'lodash';

/**
 * WordPress dependencies
 */
import { Button, DropdownMenu } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.scss';

export default function SplitButton( {
	className,
	variant,
	isSmall,
	controls,
	...rest
} ) {
	const button = (
		<Button
			className="itsec-split-button__main"
			variant={ variant }
			isSmall={ isSmall }
			{ ...rest }
		/>
	);

	controls = controls.filter( identity );

	if ( ! controls.length ) {
		return button;
	}

	return (
		<div
			className={ classnames( 'itsec-split-button', className, {
				'is-small': isSmall,
			} ) }
		>
			{ ' ' }
			{ button }
			<DropdownMenu
				className="itsec-split-button__dropdown"
				label={ __( 'More Actions', 'better-wp-security' ) }
				icon="arrow-down"
				popoverProps={ {
					position: 'bottom center',
					focusOnMount: 'container',
				} }
				toggleProps={ {
					isSmall,
					variant,
				} }
				controls={ controls }
			/>
		</div>
	);
}
