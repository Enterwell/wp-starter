/**
 * WordPress dependencies
 */
import { useViewportMatch } from '@wordpress/compose';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { coreStore } from '@ithemes/security.packages.data';
import Header from './header';
import Features from './features';
import { StyledSummary } from './styles';

export default function Summary() {
	const { installType } = useSelect(
		( select ) => ( {
			installType: select( coreStore ).getInstallType(),
		} ),
		[]
	);
	const isSmall = useViewportMatch( 'small' );

	return (
		<StyledSummary isSmall={ isSmall }>
			<Header installType={ installType } />
			<Features installType={ installType } />
		</StyledSummary>
	);
}
