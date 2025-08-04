import {BlockControls, InspectorControls} from '@wordpress/block-editor';
import {BLOCKPASCALEditor} from './partials/BLOCKKEBAB-editor';
import {BLOCKPASCALOptions} from './partials/BLOCKKEBAB-options';
import {BLOCKPASCALToolbar} from './partials/BLOCKKEBAB-toolbar';

/**
 * BLOCKPASCAL gutenberg block
 * @param props
 * @returns {*}
 * @constructor
 */
const BLOCKPASCAL = (props) => {
	return (
		<>
		<InspectorControls>
		  <BLOCKPASCALOptions {...props} />
    </InspectorControls>
    <BlockControls>
      <BLOCKPASCALToolbar {...props} />
    </BlockControls>
    	<BLOCKPASCALEditor {...props} />
    </>
  );
};

// Export block as default for registration purposes
export default BLOCKPASCAL;