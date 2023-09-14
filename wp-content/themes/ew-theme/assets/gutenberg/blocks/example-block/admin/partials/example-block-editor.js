import clsx from 'clsx';
import styles from '../example-block.module.scss?module';

/**
 * Example gutenberg block editor partial
 * @returns {*}
 * @constructor
 * @param props
 */
export const ExampleBlockEditor = (props) => {
	const {attributes} = props;
	const {className} = attributes;

	return (
		<div className={clsx(styles.exampleBlock, className)}>
			<code>This is new component ExampleBlock</code>
		</div>
	);
};
