/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';

export default function TabButton( {
	tabId,
	onClick,
	children,
	selected,
	...rest
} ) {
	return (
		<Button
			role="tab"
			tabIndex={ selected ? null : -1 }
			aria-selected={ selected }
			id={ tabId }
			onClick={ onClick }
			{ ...rest }
		>
			{ children }
		</Button>
	);
}
