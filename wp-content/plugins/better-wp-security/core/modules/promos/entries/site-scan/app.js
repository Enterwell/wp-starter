/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import {
	ProgressBarBeforeFill,
	ScanComponentPromo,
} from '@ithemes/security.pages.site-scan';
import { coreStore } from '@ithemes/security.packages.data';

const proModules = [
	{
		slug: 'inactive-users',
		priority: 1,
		label: __( 'Inactive Users', 'better-wp-security' ),
		description: __( 'Scan for inactive users registered on your site', 'better-wp-security' ),
		// high index to differentiate it from free modules for progress bar track
		index: 20,
	},
	{
		slug: 'old-site-scan',
		priority: 2,
		label: __( 'Rogue Installs', 'better-wp-security' ),
		description: __( 'Check for sites that are no longer in use.', 'better-wp-security' ),
	},
];

export default function App() {
	const { installType } = useSelect(
		( select ) => ( {
			installType: select( coreStore ).getInstallType(),
		} ),
		[]
	);
	return (
		<ProgressBarBeforeFill>
			{ installType === 'free' && (
				proModules.map( ( module ) => (
					<ScanComponentPromo
						key={ module.slug }
						index={ module.index }
						label={ module.label }
						description={ module.description }
					/>
				) )
			) }
		</ProgressBarBeforeFill>
	);
}
