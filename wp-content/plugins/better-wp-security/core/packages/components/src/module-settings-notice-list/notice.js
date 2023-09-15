/**
 * External dependencies
 */
import { noop } from 'lodash';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';

function Notice( {
	className,
	status,
	children,
	onRemove = noop,
	isDismissible = true,
	actions = [],
} ) {
	const classes = classnames(
		className,
		'notice',
		'notice-alt',
		'notice-' + status,
		{
			'is-dismissible': isDismissible,
		}
	);

	return (
		<div className={ classes }>
			<p>
				{ children }
				{ actions.map(
					(
						{
							className: buttonCustomClasses,
							label,
							onClick,
							url,
							isLink = false,
						},
						index
					) => (
						<Button
							key={ index }
							href={ url }
							isSmall={ ! isLink && ! url }
							variant={ ( url || isLink ) && 'link' }
							onClick={
								url
									? undefined
									: () => {
										onRemove();
										onClick();
									}
							}
							className={ classnames(
								'notice__action',
								buttonCustomClasses
							) }
						>
							{ label }
						</Button>
					)
				) }
			</p>
			{ isDismissible && (
				<button
					type="button"
					className="notice-dismiss"
					onClick={ onRemove }
				>
					<span className="screen-reader-text">
						{ __( 'Dismiss this notice', 'better-wp-security' ) }
					</span>
				</button>
			) }
		</div>
	);
}

export default Notice;
