import {BlockControls, InspectorControls} from '@wordpress/block-editor';
import {ExampleBlockEditor} from './partials/example-block-editor';
import {ExampleBlockOptions} from './partials/example-block-options';
import {ExampleBlockToolbar} from './partials/example-block-toolbar';

/**
 * Example gutenberg block
 * @param props
 * @returns {*}
 * @constructor
 */
const ExampleBlock = (props) => {
	return (
		<>
			<InspectorControls>
				<ExampleBlockOptions {...props} />
			</InspectorControls>
			<BlockControls>
				<ExampleBlockToolbar {...props} />
			</BlockControls>
			<ExampleBlockEditor {...props} />
		</>
	);
};

// Export block as default for registration purposes
export default ExampleBlock;