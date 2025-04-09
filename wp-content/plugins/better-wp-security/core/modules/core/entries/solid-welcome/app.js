/**
 * External dependencies
 */
import { ThemeProvider } from '@emotion/react';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { useState } from '@wordpress/element';

/**
 * Solid dependencies
 */
import { solidTheme } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { coreStore } from '@ithemes/security.packages.data';
import { store as noticesStore } from '@ithemes/security.core.admin-notices-api';
import { CardOne, CardTwo, CardThree, CardFour } from './components';
import { PageControl } from '@ithemes/security-ui';
import { StyledWelcomeModal } from './styles';

export default function App( { onClose } ) {
	const [ currentPage, setCurrentPage ] = useState( 0 );
	const [ isClosed, setIsClosed ] = useState( false );

	const { installType, isDismissed } = useSelect( ( select ) => ( {
		installType: select( coreStore ).getInstallType(),
		isDismissed: select( noticesStore ).isNoticeDismissed( 'welcome-solidwp' ),
	} ), [] );

	const { doNoticeAction } = useDispatch( noticesStore );

	if ( isClosed || isDismissed ) {
		return null;
	}

	const closeModal = () => {
		onClose?.();
		doNoticeAction( 'welcome-solidwp', 'dismiss' );
		setIsClosed( true );
	};

	const modalTitle = installType === 'free'
		? __( 'Welcome to Solid Security Basic', 'better-wp-security' )
		: __( 'Welcome to Solid Security Pro', 'better-wp-security' );

	return (
		<ThemeProvider theme={ solidTheme }>
			<StyledWelcomeModal
				title={ modalTitle }
				onRequestClose={ closeModal }
			>
				<div>
					{ currentPage === 0 && (
						<CardOne installType={ installType } />
					) }
					{ currentPage === 1 && (
						<CardTwo installType={ installType } />
					) }
					{ currentPage === 2 && (
						<CardThree installType={ installType } />
					) }
					{ currentPage === 3 && (
						<CardFour installType={ installType } />
					) }
				</div>
				<PageControl
					currentPage={ currentPage }
					numberOfPages={ 4 }
					setCurrentPage={ setCurrentPage }
					onClose={ closeModal }
				/>
			</StyledWelcomeModal>
		</ThemeProvider>
	);
}
