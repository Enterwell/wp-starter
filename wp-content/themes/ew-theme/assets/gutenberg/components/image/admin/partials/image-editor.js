import clsx from 'clsx';
import styles from '../image.module.scss?module'
import {getAttr, getAttrKey} from "../../../../helpers/BlockAttributesHelper";
import {MediaPlaceholder} from '@wordpress/block-editor';

/**
 * Image component editor partial
 * @param prefix
 * @param attributes
 * @param setAttributes
 * @param componentClassName
 * @param imageClassName
 * @returns {*}
 * @constructor
 */
export const ImageEditor = ({prefix, attributes, setAttributes, componentClassName, imageClassName}) => {
	const imageUrl = getAttr(prefix, 'imageUrl', attributes);
	const imageAlt = getAttr(prefix, 'imageAlt', attributes);
	const isCover = getAttr(prefix, 'isCover', attributes);

	return (
		<div className={clsx(styles.imageComponent, componentClassName)}>
			{!imageUrl &&
			<MediaPlaceholder
				icon='format-image'
				onSelect={({url, alt}) => setAttributes({
					[getAttrKey(prefix, 'imageUrl')]: url
				})}
				accept='image/*'
				allowedTypes={['image']}
			/>
			}
			{imageUrl &&
			<img
				className={clsx(styles.image, imageClassName)}
				style={isCover ? {'object-fit': 'cover'} : {}}
				src={imageUrl}
				alt={imageAlt}
			/>
			}
		</div>
	);
};