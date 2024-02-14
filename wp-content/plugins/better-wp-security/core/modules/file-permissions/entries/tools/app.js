/**
 * External dependencies
 */

/**
 * WordPress dependencies
 */

/**
 * Internal dependencies
 */
import { ToolFill } from '@ithemes/security.pages.tools';
import FilePermissions from './components/check-file-permissions/index';

export default function App() {
	return (
		<>
			<ToolFill tool="check-file-permissions">
				<FilePermissions />
			</ToolFill>
		</>
	);
}
