/**
 * External dependencies
 */
import { useLocation } from 'react-router-dom';
import { ErrorBoundary } from 'react-error-boundary';

/**
 * WordPress dependencies
 */
import { useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Flex, FlexBlock } from '@wordpress/components';
import { useViewportMatch } from '@wordpress/compose';

/**
 * Solid dependencies
 */
import { Heading, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { TopToolbar } from '@ithemes/security-ui';
import { ErrorRenderer, Navigation } from '../';
import { StyledMainContainer, StyledNavigationContainer, StyledMain } from './styles';

export default function Main( { children } ) {
	const isMedium = useViewportMatch( 'medium' );
	// Focus handling
	const ref = useRef();
	const location = useLocation();
	useEffect( () => {
		ref.current?.focus();
		ref.current?.ownerDocument.body.scrollTo( 0, 0 );
	}, [ location ] );

	return (
		<>
			<TopToolbar />
			<StyledMainContainer>
				<Heading level={ 1 } text={ __( 'Settings', 'better-wp-security' ) } weight={ TextWeight.NORMAL } />
				<Flex gap={ 5 } align="start" direction={ isMedium ? 'row' : 'column' }>
					<StyledNavigationContainer isMedium={ isMedium }>
						<Navigation orientation={ isMedium ? 'vertical' : 'horizontal' } />
					</StyledNavigationContainer>
					<FlexBlock>
						<StyledMain
							ref={ ref }
							aria-labelledby="itsec-page-header"
						>
							<ErrorBoundary FallbackComponent={ ErrorRenderer }>
								{ children }
							</ErrorBoundary>
						</StyledMain>
					</FlexBlock>
				</Flex>
			</StyledMainContainer>
		</>
	);
}
