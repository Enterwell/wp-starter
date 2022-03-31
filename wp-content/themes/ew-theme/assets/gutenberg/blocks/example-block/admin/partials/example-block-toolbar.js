import {ToolbarButton, ToolbarGroup} from '@wordpress/components';

/**
 * Example gutenberg block toolbar partial
 * @returns {*}
 * @constructor
 * @param props
 */
export const ExampleBlockToolbar = (props) => {
	return (
		<ToolbarGroup>
			<ToolbarButton
				icon='admin-appearance'
				label='Test button'
				onClick={() => alert('Edit block attributes here')}
			/>
		</ToolbarGroup>
	);
};