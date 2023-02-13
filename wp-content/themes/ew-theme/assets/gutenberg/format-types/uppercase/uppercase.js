import {RichTextToolbarButton} from '@wordpress/block-editor';
import {registerFormatType, toggleFormat} from '@wordpress/rich-text';
import globalManifest from '../../manifest.json';
import './uppercase.module.scss?module';

const uppercaseFormatName = `${globalManifest.projectNamespace}/format-uppercase`;

/**
 * Uppercase format button
 * Changes text to uppercase (through CSS)
 * @param isActive
 * @param onChange
 * @param value
 * @returns {*}
 * @constructor
 */
const UppercaseFormatButton = ({isActive, onChange, value}) => {
	return (
		<RichTextToolbarButton
			icon='editor-expand'
			title='Uppercase'
			isActive={isActive}
			onClick={() => {
				onChange(
					toggleFormat(value, {type: uppercaseFormatName})
				)
			}}
		/>
	);
};

// Register uppercase format
registerFormatType(uppercaseFormatName, {
	title: 'Uppercase',
	tagName: 'span',
	className: 'gf-uppercase',
	edit: UppercaseFormatButton
});