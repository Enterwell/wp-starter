import clsx from 'clsx';
import styles from '../example-block.module.scss?module';
import {useInnerBlocksProps, useBlockProps} from '@wordpress/block-editor';

// Inner blocks template
const TEMPLATE = [
  ['core/paragraph', {placeholder: 'This ExampleBlock paragraph...'}]
];

/**
 * Example gutenberg block editor partial
 * @returns {*}
 * @constructor
 * @param props
 */
export const ExampleBlockEditor = (props) => {
  const {attributes} = props;
  const {className} = attributes;

  // Create inner block element with locked template
  const blockProps = useBlockProps();
  const innerBlockProps = useInnerBlocksProps(blockProps, {
    template: TEMPLATE,
    templateLock: 'all'
  });

  return (
    <div className={clsx(styles.exampleBlock, className)}>
      <code>This is new component ExampleBlock</code>
      <div {...innerBlockProps} />
    </div>
  );
};
