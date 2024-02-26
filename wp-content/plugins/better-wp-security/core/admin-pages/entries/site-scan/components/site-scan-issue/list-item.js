/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { chevronDown, chevronUp } from '@wordpress/icons';

/**
 * iThemes dependencies
 */
import { Button, Text } from '@ithemes/ui';
import store from '../../store';

/**
 * Internal dependencies
 */
import {
	severityColor,
	severityText,
	StyledDetailsContainer,
	StyledListDetailsContainer,
	StyledListItem,
	StyledSeverity,
} from './styles';

export default function ListItem( { icon, issue, children } ) {
	const { component } = useSelect( ( select ) => ( {
		component: select( store ).getComponentBySlug( issue.component ),
	} ), [ issue.component ] );
	const [ isExpanded, setIsExpanded ] = useState( false );

	return (
		<>
			<StyledListItem>
				<div>
					<Text icon={ icon } text={ component.label } />
					<>
						<Text as="p" weight={ 600 } text={ issue.title } />
						{ issue.description &&
							<Text as="p" text={ issue.description } />
						}
					</>
				</div>

				<div>
					<StyledSeverity
						backgroundColor={ severityColor( issue.severity ) }
						weight={ 600 }
						text={ severityText( issue.severity ) }
					/>
				</div>
				<Button
					aria-controls={ `solid-scan-result-${ issue.component + '-' + issue.id }` }
					aria-expanded={ isExpanded }
					icon={ isExpanded ? chevronUp : chevronDown }
					iconPosition="right"
					onClick={ () => setIsExpanded( ! isExpanded ) }
					variant="tertiary"
				/>
			</StyledListItem>
			<StyledListDetailsContainer variant="tertiary" isExpanded={ isExpanded }>
				<StyledDetailsContainer id={ `solid-scan-result-${ issue.component + '-' + issue.id }` }>
					{ children }
				</StyledDetailsContainer>
			</StyledListDetailsContainer>
		</>
	);
}
