/**
 * External dependencies
 */
import { useParams } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { useViewportMatch } from '@wordpress/compose';
import { sprintf, __ } from '@wordpress/i18n';

/**
 * Solid dependencies
 */
import { StepIndicator, SurfaceVariant, Text, TextSize, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import {
	StyledOnboardProgress,
	StyledOnboardProgressItem,
	StyledOnboardProgressList,
} from './styles';
import { usePages } from '../../page-registration';

export default function OnboardProgress( { className } ) {
	const { page } = useParams();
	const isMedium = useViewportMatch( 'medium' );
	const pages = usePages( { location: 'primary' } )
		.filter( ( { hideFromNav } ) => ! hideFromNav );

	if ( ! pages.find( ( { id } ) => id === page ) ) {
		return null;
	}

	return (
		<StyledOnboardProgress className={ className }>
			<StyledOnboardProgressList>
				{ pages.map( ( item, index ) => (
					// Show all on large devices, or only show current + next
					( isMedium || ( item.id === page ) || pages[ index - 1 ]?.id === page ) && (
						<StyledOnboardProgressItem
							key={ item.id }
							as="li"
							decoration="none"
							weight={ page === item.id ? TextWeight.HEAVY : TextWeight.NORMAL }
							variant={ page === item.id ? TextVariant.DARK : TextVariant.MUTED }
						>
							<StepIndicator
								step={ index + 1 }
								textSize={ TextSize.NORMAL }
								surfaceVariant={ page === item.id ? SurfaceVariant.PRIMARY_ACCENT : SurfaceVariant.SECONDARY }
							/>
							{ item.title }
						</StyledOnboardProgressItem>
					)
				) ) }
			</StyledOnboardProgressList>
			{ ! isMedium && <Text
				variant={ TextVariant.MUTED }
				text={
					sprintf(
					/* translators: 1. Number of steps. */
						__( 'out of %d', 'better-wp-security' ),
						pages.length
					) }
			/> }
		</StyledOnboardProgress>
	);
}
