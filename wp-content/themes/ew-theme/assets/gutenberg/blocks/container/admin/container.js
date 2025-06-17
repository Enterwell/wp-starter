import {InspectorControls} from '@wordpress/block-editor';
import {ContainerEditor} from './partials/container-editor';
import {ContainerOptions} from './partials/container-options';

/**
 * Container block
 * @param props
 * @returns {*}
 * @constructor
 */
const Container = (props) => {
  return (
    <>
      <InspectorControls>
        <ContainerOptions {...props} />
      </InspectorControls>
      <ContainerEditor {...props} />
    </>
  );
};

// Export block as default for registration purposes
export default Container;
