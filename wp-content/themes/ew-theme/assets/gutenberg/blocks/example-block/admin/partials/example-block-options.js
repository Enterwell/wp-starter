import blockManifest from '../../manifest.json';
import {PanelBody, PanelRow} from '@wordpress/components';

/**
 * Example gutenberg block options partial
 * @returns {*}
 * @constructor
 * @param props
 */
export const ExampleBlockOptions = (props) => {
	return (
		<PanelBody title="Example block options" icon={blockManifest.icon || 'block-default'} initialOpen={ true }>
			<PanelRow>Example block options go here</PanelRow>
		</PanelBody>
	);
};
