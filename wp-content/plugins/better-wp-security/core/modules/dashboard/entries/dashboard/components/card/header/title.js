/**
 * iThemes dependencies
 */
import { Heading, TextSize, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { getCardTitle } from '../../../utils';

export default function Title( { card, config } ) {
	return (
		<Heading
			level={ 2 }
			className="itsec-card-header-title"
			variant={ TextVariant.DARK }
			size={ TextSize.LARGE }
			weight={ TextWeight.HEAVY }
			text={ getCardTitle( card, config ) }
		/>
	);
}
