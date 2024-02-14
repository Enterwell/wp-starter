/**
 * SolidWP dependencies
 */
import { MasterDetail } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import Detail, { ActiveLockout } from '../detail';

export default function List( { lockouts, select, selectedLockout, fetchLockoutDetails } ) {
	return (
		<MasterDetail
			masters={ lockouts }
			getId={ ( lockout ) => lockout.id }
			isBorderless
			isSinglePane
			mode="list"
			renderMaster={ ( lockout ) => (
				<ActiveLockout master={ lockout } />
			) }
			onSelect={ select }
			selectedId={ selectedLockout?.id || 0 }
			renderDetail={ ( lockout, isVisible ) => (
				<Detail
					master={ lockout }
					isVisible={ isVisible }
					fetchLockoutDetails={ fetchLockoutDetails }
				/>
			) }
		/>
	);
}
