import blockManifest from '../../manifest.json';
import {PanelBody, PanelRow} from '@wordpress/components';

/**
 * BLOCKPASCAL gutenberg block options partial
 * @returns {*}
 * @constructor
 * @param props
 */
export const BLOCKPASCALOptions = (props) => {
	return (
		<PanelBody title="BLOCKPASCAL options" icon={blockManifest.icon || 'block-default'} initialOpen={ true }>
			<PanelRow>BLOCKPASCAL options go here</PanelRow>
		</PanelBody>
	);
};