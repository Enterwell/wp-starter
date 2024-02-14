/**
 * WordPress dependencies
 */
import { useBlockProps, BlockIcon } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { getBlockType } from '@wordpress/blocks';

export default function Edit( { name } ) {
	const { icon, title } = getBlockType( name );

	return (
		<div { ...useBlockProps() }>
			<Placeholder
				icon={ <BlockIcon icon={ icon } showColors /> }
				label={ title }
				instructions={ __( 'Users can adjust their User Security profile settings. This block does not provide any controls.', 'better-wp-security' ) }
			/>
		</div>
	);
}
