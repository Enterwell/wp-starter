import clsx from 'clsx';
import styles from '../BLOCKKEBAB.module.scss?module';

/**
 * BLOCKPASCAL gutenberg block editor partial
 * @returns {*}
 * @constructor
 * @param props
 */
export const BLOCKPASCALEditor = (props) => {
	const {attributes} = props;
	const {className} = attributes;

	return (
		<div className={clsx(styles.BLOCKCAMEL, className)}>
			<code>This is new component BLOCKPASCAL</code>
		</div>
	);
};