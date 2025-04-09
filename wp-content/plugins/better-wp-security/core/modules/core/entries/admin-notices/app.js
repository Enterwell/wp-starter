/**
 * WordPress dependencies
 */
import { createPortal } from '@wordpress/element';
import { Popover, SlotFillProvider } from '@wordpress/components';

/**
 * Internal dependencies
 */
import '@ithemes/security.core.admin-notices-api';
import Toolbar from './components/toolbar';

function App( { portalEl } ) {
	return (
		<SlotFillProvider>
			{ createPortal( <Popover.Slot />, portalEl ) }
			<Toolbar />
		</SlotFillProvider>
	);
}

export default App;
