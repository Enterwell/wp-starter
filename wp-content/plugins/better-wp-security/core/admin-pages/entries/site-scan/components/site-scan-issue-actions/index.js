/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * iThemes dependencies
 */
import { Button, useConfirmationDialog } from '@ithemes/ui';
import store from '../../store';

/**
 * Internal dependencies
 */
import { StyledActionButtons } from '../../styles';

function StandardButton( { action, isApplying, onApply } ) {
	return (
		<Button
			isBusy={ isApplying }
			onClick={ onApply }
			variant={
				action.rel === 'ithemes-security:mute-vulnerability' || action.rel === 'ithemes-security:mute-issue'
					? 'muted' : 'secondary'
			}
			text={ action.title }
		/>
	);
}

function DestructiveButton( { action, isApplying, onApply } ) {
	const confirmationArgs = {
		title: __( 'Confirm your action', 'better-wp-security' ),
		body: __( 'Are you sure you want to do this?', 'better-wp-security' ),
		onContinue: onApply,
		continueText: action.title,
	};
	const [ onClick, element ] = useConfirmationDialog( confirmationArgs );
	return (
		<>
			<Button
				isDestructive={ action.isDestructive }
				isBusy={ isApplying }
				onClick={ onClick }
				text={ action.title }
			/>
			{ element }
		</>
	);
}

function IssueAction( { action, issue } ) {
	const { applyIssueAction } = useDispatch( store );
	const { isApplying } = useSelect( ( select ) => ( {
		isApplying: select( store ).isApplyingAction( issue, action.rel ),
	} ), [ action.rel, issue ] );

	const onApply = () => {
		applyIssueAction( issue, action.rel );
	};

	return (
		action.isDestructive
			? ( <DestructiveButton action={ action } isApplying={ isApplying } onApply={ onApply } /> )
			: ( <StandardButton action={ action } isApplying={ isApplying } onApply={ onApply } /> )
	);
}

export default function SiteScanIssueActions( { issue, allowedActions } ) {
	const { actions } = useSelect( ( select ) => ( {
		actions: select( store ).getIssueActions( issue ),
	} ), [ issue ] );
	const availableActions = allowedActions ? actions.filter( ( action ) => allowedActions?.includes( action.rel ) ) : actions;

	if ( ! availableActions ) {
		return null;
	}
	return (
		<StyledActionButtons>
			{ availableActions.map( ( action ) => (
				<IssueAction key={ action.rel } action={ action } issue={ issue } />
			) ) }
		</StyledActionButtons>
	);
}
