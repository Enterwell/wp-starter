/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';

/**
 * SolidWP dependencies
 */
import { TextSize, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import PageHeader from '../..//components/page-header';
import Tools from '../../components/tool-panel/tool-panel';
import { StyledPageContainer, StyledSectionHeading } from '../../components/styles';
import {
	BeforeImportExportToolsSlot,
	AfterImportExportToolsSlot,
} from '../../components/slot-fill';

export default function List() {
	const [ hasBeforeTools, setHasBeforeTools ] = useState( false );

	return (
		<StyledPageContainer>
			<PageHeader />
			<BeforeImportExportToolsSlot>
				{ ( fills ) => <ToolsSlot fills={ fills } setHasTools={ setHasBeforeTools } /> }
			</BeforeImportExportToolsSlot>
			{ hasBeforeTools && (
				<StyledSectionHeading
					level={ 2 }
					size={ TextSize.LARGE }
					variant={ TextVariant.DARK }
					weight={ 600 }
					text={ __( 'Additional Tools', 'better-wp-security' ) }
				/>
			) }
			<Tools />
			<AfterImportExportToolsSlot />
		</StyledPageContainer>
	);
}

function ToolsSlot( { fills, setHasTools } ) {
	useEffect( () => setHasTools( fills.length > 0 ), [ fills, setHasTools ] );

	return fills;
}
