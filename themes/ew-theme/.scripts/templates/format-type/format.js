import {RichTextToolbarButton} from '@wordpress/block-editor';
import {registerFormatType, toggleFormat} from '@wordpress/rich-text';
import globalManifest from '../../manifest.json';
import './FORMATKEBAB.module.scss?module';

const FORMATCAMELFormatName = `${globalManifest.projectNamespace}/format-FORMATKEBAB`;

/**
 * FORMATPASCAL format button
 * @param isActive
 * @param onChange
 * @param value
 * @returns {*}
 * @constructor
 */
const FORMATPASCALFormatButton = ({isActive, onChange, value}) => {
	return (
		<RichTextToolbarButton
			icon='editor-textcolor'
			title=''
			isActive={isActive}
			onClick={() => {
				onChange(
					toggleFormat(value, {type: FORMATCAMELFormatName})
				)
			}}
		/>
	);
};

// Register FORMATCAMEL format
registerFormatType(FORMATCAMELFormatName, {
	title: '',
	tagName: 'span',
	className: 'gf-FORMATKEBAB',
	edit: FORMATPASCALFormatButton
});