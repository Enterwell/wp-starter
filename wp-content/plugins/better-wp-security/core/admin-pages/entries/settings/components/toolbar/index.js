/**
 * External dependencies
 */
import { useParams } from 'react-router-dom';

/**
 * WordPress components.
 */
import {
	createSlotFill,
	Popover,
	Toolbar,
	ToolbarButton,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useMediaQuery, useFocusOnMount } from '@wordpress/compose';
import { useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Search } from '@ithemes/security-search';
import { useGlobalNavigationUrl } from '@ithemes/security-utils';
import './style.scss';

const { Slot: ToolbarSlot, Fill: ToolbarFill } = createSlotFill( 'Toolbar' );

export { ToolbarFill };

export default function() {
	const { root } = useParams();
	const dashboardUrl = useGlobalNavigationUrl( 'dashboard' );
	const isSmall = useMediaQuery( '(max-width: 600px)' );
	const [ isSearchOpen, setIsSearchOpen ] = useState( false );
	const focusSearchRef = useFocusOnMount();

	return (
		<div
			role="region"
			aria-label={ __( 'Toolbar', 'better-wp-security' ) }
			className="itsec-settings-toolbar"
		>
			{ root !== 'onboard' && ! isSmall && <Search /> }

			<Toolbar label={ __( 'Toolbar Actions', 'better-wp-security' ) }>
				{ root !== 'onboard' && (
					<>
						{ isSmall && (
							<>
								<ToolbarButton
									icon="search"
									text={ __( 'Search', 'better-wp-security' ) }
									aria-expanded={ isSearchOpen }
									onClick={ () =>
										setIsSearchOpen( ! isSearchOpen )
									}
								/>
								{ isSearchOpen && (
									<Popover
										className="itsec-settings-search__popover"
										expandOnMobile
										headerTitle={ __( 'Search', 'better-wp-security' ) }
										focusOnMount="container"
										onClose={ () =>
											setIsSearchOpen( false )
										}
										onFocusOutside={ () => {} }
									>
										<Search
											showResults
											ref={ focusSearchRef }
											onPick={ () =>
												setIsSearchOpen( false )
											}
										/>
									</Popover>
								) }
							</>
						) }
						<ToolbarButton
							icon="shield-alt"
							href={ dashboardUrl }
							text={ __( 'Dashboard', 'better-wp-security' ) }
						/>
					</>
				) }
				<ToolbarSlot />
			</Toolbar>
		</div>
	);
}
