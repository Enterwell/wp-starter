/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { coreStore } from '@ithemes/security.packages.data';

export default function useFeatureFlag( flag ) {
	return useSelect(
		( select ) => select( coreStore ).getFeatureFlags()?.includes( flag ),
		[ flag ]
	);
}
