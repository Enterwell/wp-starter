import clsx from 'clsx';
import styles from './image.module.scss?module'
import {MediaPlaceholder} from '@wordpress/block-editor';
import {withAttr} from "../../../helpers/BlockAttributesHelper";

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
const Image = ({prefix, attributes, setAttributes, componentClassName, imageClassName}) => {
	const {imageUrl, imageAlt, isCover} = attributes;

	return (
		<div className={clsx(styles.imageComponent, componentClassName)}>
			{!imageUrl &&
			<MediaPlaceholder
				icon='format-image'
				onSelect={({url}) => setAttributes({imageUrl: url})}
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

export default withAttr(Image);