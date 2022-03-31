import {MediaPlaceholder} from '@wordpress/block-editor';
import {TextControl, BaseControl, Button, ToggleControl} from '@wordpress/components';
import styles from "../image.module.scss?module";
import {getAttr, getAttrKey} from "../../../../helpers/BlockAttributesHelper";

/**
 * Image component options partial
 * @param prefix
 * @param attributes
 * @param setAttributes
 * @returns {*}
 * @constructor
 */
export const ImageOptions = ({prefix, attributes, setAttributes}) => {
	const imageUrl = getAttr(prefix, 'imageUrl', attributes);
	const imageAlt = getAttr(prefix, 'imageAlt', attributes);
	const isCover = getAttr(prefix, 'isCover', attributes);

	/**
	 * Remove image button local component
	 */
	const removeImageButton = (
		<>
			{imageUrl &&
			<Button
				isSecondary
				isSmall
				isDestructive
				className={styles.optionsRemoveImageButton}
				onClick={() => setAttributes({
					[getAttrKey(prefix, 'imageUrl')]: undefined
				})}
				icon='trash'
			/>
			}
		</>
	);

	return (
		<>
			<BaseControl label='Image'>
				{!imageUrl &&
				<MediaPlaceholder
					className={styles.optionsImagePlaceholder}
					icon='format-image'
					onSelect={({url, alt}) => setAttributes({
						[getAttrKey(prefix, 'imageUrl')]: url
					})}
					accept='image/*'
					allowedTypes={['image']}
				/>
				}
				{imageUrl && (
					<div className={styles.optionsImageContainer}>
						<img
							className={styles.optionsImage}
							src={imageUrl}
							alt={imageAlt}
						/>
						{removeImageButton}
					</div>
				)}
			</BaseControl>

			<TextControl
				label='Image alt text'
				value={imageAlt}
				onChange={(value) => setAttributes({[getAttrKey(prefix, 'imageAlt')]: value})}
			/>

			<ToggleControl
				label='Cover image?'
				help={'Has image as cover in its container.'}
				checked={isCover}
				onChange={(value) => setAttributes({[getAttrKey(prefix, 'isCover')]: value})}
			/>
		</>
	);
};