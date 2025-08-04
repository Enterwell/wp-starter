import clsx from 'clsx';
import styles from './container.module.scss?module'
import {withAttr} from "../../../helpers/BlockAttributesHelper";

/**
 * Container component editor partial
 * Servers the same purpose as c-container
 * @returns {*}
 * @constructor
 * @param props
 */
const Container = (props) => {
	const {className, children} = props;

	return (
		<div className={clsx(styles.containerComponent, className)}>
			{children}
		</div>
	);
};

export default withAttr(Container);