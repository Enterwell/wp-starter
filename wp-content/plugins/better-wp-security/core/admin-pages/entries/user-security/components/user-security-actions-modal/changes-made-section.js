/**
 * External dependencies
 */
import { omit } from 'lodash';

/**
 * WordPress dependencies
 */
import {
	closeSmall as closeIcon,
} from '@wordpress/icons';

/**
 * SolidWP dependencies
 */
import { Button } from '@ithemes/ui';

export function UserSecurityActionsModalChangesMade( { setActiveActions, actions, confirmationMessages, setConfirmationMessages } ) {
	return (
		<>
			{
				Object.entries( confirmationMessages ).map( ( [ key, value ] ) => (
					<Button
						text={ value }
						variant="link"
						onClick={ () => {
							setActiveActions(
								omit( actions, key )
							);
							setConfirmationMessages(
								omit( confirmationMessages, key )
							);
						} }
						icon={ closeIcon }
						iconGap={ 0 }
						key={ key }
					/>
				) )
			}
		</>
	);
}
