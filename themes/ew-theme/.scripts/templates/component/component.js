import clsx from 'clsx';
import styles from './COMPONENTKEBAB.module.scss?module'
import {withAttr} from "../../../helpers/BlockAttributesHelper";

/**
 * COMPONENTPASCAL component editor partial
 * @param prefix
 * @param attributes
 * @param setAttributes
 * @param componentClassName
 * @returns {*}
 * @constructor
 */
const COMPONENTPASCAL = ({prefix, attributes, setAttributes, componentClassName}) => {
	return (
		<div className={clsx(styles.COMPONENTCAMELComponent, componentClassName)}>

		</div>
	);
};

export default withAttr(COMPONENTPASCAL);