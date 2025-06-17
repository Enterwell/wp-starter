import clsx from 'clsx';
import styles from '../container.module.scss?module';
import {useBlockProps, useInnerBlocksProps} from '@wordpress/block-editor';
import {Fragment} from '@wordpress/element';

/**
 * Container editor partial
 * @returns {*}
 * @constructor
 * @param props
 */
export const ContainerEditor = (props) => {
  const {attributes} = props;
  const {className} = attributes;

  const blockProps = useBlockProps({
    className: clsx(styles.container, className)
  });
  const innerBlockProps = useInnerBlocksProps();

  return (
    <div {...blockProps}>
      <Fragment {...innerBlockProps} />
    </div>
  );
};
