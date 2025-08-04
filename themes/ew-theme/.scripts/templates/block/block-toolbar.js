import {ToolbarButton, ToolbarGroup} from '@wordpress/components';

/**
 * BLOCKPASCAL gutenberg block toolbar partial
 * @returns {*}
 * @constructor
 * @param props
 */
export const BLOCKPASCALToolbar = (props) => {
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