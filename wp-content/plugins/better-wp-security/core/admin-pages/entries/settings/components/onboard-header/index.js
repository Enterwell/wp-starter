/**
 * External dependencies
 */
import { useParams } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Solid dependencies
 */
import { StepIndicator, SurfaceVariant, TextSize, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { usePages, useNavigation } from '../../page-registration';
import {
	StyledOnboardAction,
	StyledOnboardDescription,
	StyledOnboardHeader,
	StyledOnboardTitle,
} from './styles';

export default function OnboardHeader( { title, description, showIndicator, showNext } ) {
	const { page } = useParams();
	const { goNext } = useNavigation();
	const pages = usePages( { location: 'primary' } )
		.filter( ( { hideFromNav } ) => ! hideFromNav );

	const step = pages.findIndex( ( { id } ) => id === page ) + 1;

	return (
		<StyledOnboardHeader>
			<StyledOnboardTitle
				level={ 2 }
				size={ TextSize.EXTRA_LARGE }
				variant={ TextVariant.DARK }
				text={ title }
			>
				{ step && showIndicator && (
					<StepIndicator
						step={ step }
						surfaceVariant={ SurfaceVariant.PRIMARY_ACCENT }
						textSize={ TextSize.NORMAL }
					/>
				) }
			</StyledOnboardTitle>
			<StyledOnboardDescription
				as="p"
				variant={ TextVariant.MUTED }
				text={ description }
			/>
			{ showNext && (
				<StyledOnboardAction
					onClick={ goNext }
					variant="secondary"
					text={ __( 'Next', 'better-wp-security' ) }
				/>
			) }
		</StyledOnboardHeader>
	);
}
