/**
 * External dependencies
 */
import { Link, useHistory } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { arrowLeft as backIcon } from '@wordpress/icons';
import { Flex, FlexBlock, FlexItem } from '@wordpress/components';
import { useDispatch } from '@wordpress/data';
import { gmdate } from '@wordpress/date';

/**
 * Solid dependencies
 */
import { PageHeader, Surface, Button, Notice } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { withNavigate } from '@ithemes/security-hocs';
import { firewallStore } from '@ithemes/security.packages.data';
import { Page, BeforeCreateFirewallRuleSlot } from '../../components';
import { StyledRuleForm } from './styles';

export default function CreateRule() {
	const { push } = useHistory();
	const { saveItem, refreshQuery } = useDispatch( firewallStore );
	const [ rule, setRule ] = useState( {} );
	const [ isSaving, setIsSaving ] = useState( '' );
	const [ error, setError ] = useState( null );
	const onDeploy = () => doSave( 'publish', rule );
	const onDraft = () => doSave( 'draft', {
		...rule,
		paused_at: gmdate( 'Y-m-d\\TH:i:s' ),
	} );

	const doSave = async ( type, data ) => {
		setIsSaving( type );
		try {
			setError( null );
			await saveItem( data );
			await refreshQuery( 'main' );
		} catch ( e ) {
			setError( e );

			return;
		} finally {
			setIsSaving( '' );
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
				<BeforeCreateFirewallRuleSlot />
				{ error && (
					<Notice
						type="danger"
						text={ error.message || __( 'Could not create rule.', 'better-wp-security' ) }
					/>
				) }
				<Surface as={ FlexBlock }>
					<PageHeader
						hasBorder
						title={ __( 'Create Firewall Rule', 'better-wp-security' ) }
						description={ __( 'Custom firewall rules let you block attackers or allow authorized traffic.', 'better-wp-security' ) }
					/>
					<StyledRuleForm value={ rule } onChange={ setRule } />
				</Surface>
				<Flex justify="end">
					<Button
						variant="secondary"
						text={ __( 'Save as Draft', 'better-wp-security' ) }
						onClick={ onDraft }
						disabled={ !! isSaving }
						isBusy={ isSaving === 'draft' }
					/>
					<Button
						variant="primary"
						text={ __( 'Deploy', 'better-wp-security' ) }
						onClick={ onDeploy }
						disabled={ !! isSaving }
						isBusy={ isSaving === 'publish' }
					/>
				</Flex>
			</Flex>
		</Page>
	);
}
