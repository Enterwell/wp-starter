/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
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
	StyledCombinedColumns,
	StyledDetailsContainer,
	StyledRowDetailsContainer,
	StyledScanInfo,
	StyledSeverity,
	StyledAction,
} from './styles';

export default function TableRow( { icon, issue, isLarge, children } ) {
	const { component } = useSelect( ( select ) => ( {
		component: select( store ).getComponentBySlug( issue.component ),
	} ), [ issue.component ] );
	const [ isExpanded, setIsExpanded ] = useState( false );
	return (
		<>
			<tr>
				{ isLarge && (
					<>
						<td>
							<Text icon={ icon } text={ component.label } />
						</td>
						<td>
							<StyledScanInfo>
								<>
									<Text weight={ 600 } text={ issue.title } />
									{ issue.description &&
										<Text text={ issue.description } />
									}
								</>
							</StyledScanInfo>
						</td>
						<td>
							<StyledSeverity
								weight={ 600 }
								backgroundColor={ severityColor( issue.severity ) }
								text={ severityText( issue.severity ) }
							/>
						</td>
					</>
				) }
				{ ! isLarge && (
					<>
						<td colSpan="2">
							<StyledCombinedColumns>
								<Text icon={ icon } text={ component.label } />
								<>
									<Text as="p" weight={ 600 } text={ issue.title } />
									{ issue.description &&
									<Text as="p" text={ issue.description } />
									}
								</>

							</StyledCombinedColumns>
						</td>
						<td>
							<StyledSeverity
								weight={ 600 }
								backgroundColor={ severityColor( issue.severity ) }
								text={ severityText( issue.severity ) }
							/>
						</td>
					</>
				) }
				<StyledAction>
					<Button
						aria-controls={ `solid-scan-result-${ issue.component + '-' + issue.id }` }
						aria-expanded={ isExpanded }
						icon={ isExpanded ? chevronUp : chevronDown }
						iconPosition="right"
						iconGap={ 0 }
						onClick={ () => setIsExpanded( ! isExpanded ) }
						variant="tertiary"
						label={ __( 'View Details', 'better-wp-security' ) }
						text={ isLarge && __( 'View Details', 'better-wp-security' ) }
					/>
				</StyledAction>
			</tr>
			<StyledRowDetailsContainer as="tr" id={ `solid-scan-result-${ issue.component + '-' + issue.id }` } isExpanded={ isExpanded } variant="tertiary">
				<td colSpan={ isLarge ? 5 : 4 }>
					<StyledDetailsContainer>
						{ children }
					</StyledDetailsContainer>
				</td>
			</StyledRowDetailsContainer>
		</>
	);
}
