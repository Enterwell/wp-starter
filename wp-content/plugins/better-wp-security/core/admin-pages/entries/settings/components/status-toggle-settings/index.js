/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { ToggleControl } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';

export default function StatusToggleSettings( { module, setSettingsOpen, persist } ) {
	const isActive = module.status.selected === 'active';
	const [ toggling, setIsToggling ] = useState( false );
	const { activateModule, deactivateModule, editModule } = useDispatch(
		MODULES_STORE_NAME
	);

	const onToggleStatus = async ( checked ) => {
		setIsToggling( true );
		if ( checked ) {
			await ( persist
				? activateModule( module.id )
				: editModule( module.id, { status: { selected: 'active' } } )
			);
			setSettingsOpen( true );
		} else {
			await ( persist
				? deactivateModule( module.id )
				: editModule( module.id, { status: { selected: 'inactive' } } )
			);
		}
		setIsToggling( false );
	};

	return (
		<ToggleControl
			label={ module.title }
			checked={ isActive }
			onChange={ onToggleStatus }
			disabled={ toggling }
			aria-label={ sprintf(
				/* translators: 1. The module name. */
				__( 'Enable the “%s” module.', 'better-wp-security' ),
				module.title
			) }
			aria-describedby={ `itsec-module-description--${ module.id }` }
			__nextHasNoMarginBottom
		/>
	);
}
