/**
 * External dependencies
 */
import { Link, useLocation, useParams } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { arrowLeft as backIcon } from '@wordpress/icons';
import { createSlotFill } from '@wordpress/components';

/**
 * Solid dependencies
 */
import { Button } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { withNavigate } from '@ithemes/security-hocs';
import { NoticeList, OnboardProgress } from '../';
import { usePreviousPage } from '../../page-registration';
import { StyledGraphic, StyledLogo, StyledMain, StyledMainContainer } from './styles';

const {
	Fill: OnboardBackActionFill,
	Slot: OnboardBackActionSlot,
} = createSlotFill( 'OnboardBackAction' );

export { OnboardBackActionFill };

export default function OnboardMain( { url, render: Component } ) {
	const { page } = useParams();
	const previous = usePreviousPage( page );
	const { pathname } = useLocation();

	return (
		<StyledMainContainer>
			<StyledGraphic />
			{ pathname !== '/onboard/site-type' && pathname !== '/onboard/summary' && (
				<>
					<StyledLogo />
					<OnboardBackActionSlot>
						{ ( fills ) => fills.length > 0 ? fills : (
							<Link
								to={ previous ? `${ url }/${ previous }` : url }
								component={ withNavigate( Button ) }
								text={ __( 'Back', 'better-wp-security' ) }
								icon={ backIcon }
								iconPosition="left"
								variant="tertiary"
							/>
						) }
					</OnboardBackActionSlot>
				</>
			) }
			<StyledMain>
				<NoticeList />
				<Component />
			</StyledMain>
			<OnboardProgress />
		</StyledMainContainer>
	);
}

export function OnboardEmptyMain() {
	return (
		<StyledMainContainer>
			<StyledMain />
		</StyledMainContainer>
	);
}
