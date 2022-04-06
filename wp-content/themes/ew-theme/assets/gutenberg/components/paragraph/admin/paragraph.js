import clsx from 'clsx';
import styles from './paragraph.module.scss?module'
import {withAttr} from "../../../helpers/BlockAttributesHelper";
import {RichText} from '@wordpress/block-editor';
import globalManifest from '../../../manifest.json';

/**
 * Paragraph component editor partial
 * @param prefix
 * @param attributes
 * @param setAttributes
 * @param className
 * @returns {*}
 * @constructor
 */
const Paragraph = ({prefix, attributes, setAttributes, className}) => {
	const {paragraphText} = attributes;

	return (
		<RichText
			tagName='p'
			value={paragraphText}
			onChange={text => setAttributes({paragraphText: text})}
			className={clsx(styles.paragraphComponent, className)}
			placeholder='Write text here...'
			multiline={true}
			allowedFormats={
				[
					'core/bold',
					'core/italic',
					'core/link',
					'core/strikethrough',
					'core/underline',
					'core/text-color',
					'core/subscript',
					'core/superscript',
					`${globalManifest.projectNamespace}/format-uppercase`
				]
			}
		/>
	);
};

export default withAttr(Paragraph);