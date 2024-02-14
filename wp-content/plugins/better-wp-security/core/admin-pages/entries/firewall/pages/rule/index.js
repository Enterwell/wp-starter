/**
 * External dependencies
 */
import { Link, useHistory, useParams } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { arrowLeft as backIcon } from '@wordpress/icons';
import { Flex, FlexBlock, FlexItem } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { gmdate } from '@wordpress/date';

/**
 * Solid dependencies
 */
import { PageHeader, Surface, Button, Notice } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { withNavigate } from '@ithemes/security-hocs';
import { getSelf } from '@ithemes/security-utils';
import { firewallStore } from '@ithemes/security.packages.data';
import { Page } from '../../components';
import { StyledRuleForm } from '../create-rule/styles';

export default function Rule() {
	const { push } = useHistory();
	const { id } = useParams();
	const { item, self, isSaving, notFound, error } = useSelect( ( select ) => {
		const _self = getSelf( select( firewallStore ).getItemById( id ) );

		return {
			item: _self && select( firewallStore ).getEditedItem( _self ),
			self: _self,
			isSaving: _self && select( firewallStore ).isSaving( _self ),
			notFound: select( firewallStore ).hasResolutionFailed( 'getItemById', [ id ] ),
			error: _self && select( firewallStore ).getLastSaveError( _self ),
		};
	}, [ id ] );
	const { editItem, saveEditedItem } = useDispatch( firewallStore );
	const onDeploy = async () => {
		if ( item.paused_at ) {
			await editItem( self, {
				paused_at: null,
			} );
		}
		return doSave();
	};
	const onDraft = async () => {
		if ( ! item.paused_at ) {
			await editItem( self, {
				paused_at: gmdate( 'Y-m-d\\TH:i:s' ),
			} );
		}
		await doSave();
	};

	const doSave = async () => {
		try {
			await saveEditedItem( self );
		} catch ( e ) {
			return;
		}

		push( '/rules' );
	};

	return (
		<Page>
			<Flex
				gap={ 5 }
				direction="column"
				align="stretch"
				justify="start"
				expanded={ false }
				as="form"
			>
				<FlexItem>
					<Link
						to="/rules"
						component={ withNavigate( Button ) }
						variant="tertiary"
						icon={ backIcon }
						text={ __( 'Back to Rules overview', 'better-wp-security' ) }
					/>
				</FlexItem>
				{ error && (
					<Notice
						type="danger"
						text={ error.message || __( 'Could not save rule.', 'better-wp-security' ) }
					/>
				) }
				{ notFound && (
					<Notice
						type="warning"
						text={ __( 'Firewall rule not found.', 'better-wp-security' ) }
					/>
				) }
				{ item && (
					<>
						<Surface as={ FlexBlock }>
							<PageHeader
								hasBorder
								title={ __( 'Edit Firewall Rule', 'better-wp-security' ) }
								description={ __( 'Custom firewall rules let you block attackers or allow authorized traffic.', 'better-wp-security' ) }
							/>
							<StyledRuleForm value={ item } onChange={ ( next ) => editItem( self, next ) } />
						</Surface>
						<Flex justify="end">
							<Button
								variant="secondary"
								text={ __( 'Save as Draft', 'better-wp-security' ) }
								onClick={ onDraft }
								disabled={ isSaving }
								isBusy={ isSaving && item.paused_at }
							/>
							<Button
								variant="primary"
								text={ __( 'Deploy', 'better-wp-security' ) }
								onClick={ onDeploy }
								disabled={ isSaving }
								isBusy={ isSaving && item.paused_at === null }
							/>
						</Flex>
					</>
				) }
			</Flex>
		</Page>
	);
}
