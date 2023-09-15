/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button, Card, Flex, FlexItem } from '@wordpress/components';
import { useMemo } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { ErrorList, FlexSpacer, TabPanel } from '@ithemes/security-components';
import { MultiGroupHeader, TabSettingsBulk } from '../';

export default function ManageMultipleGroups( { groupIds, showSave = true } ) {
	const { isDirty, isSaving, errors } = useSelect( ( select ) => ( {
		isDirty: select(
			'ithemes-security/user-groups-editor'
		).hasBulkSettingEdits(),
		isSaving: select(
			'ithemes-security/user-groups-editor'
		).isSavingBulkEdits( groupIds ),
		errors: select(
			'ithemes-security/user-groups-editor'
		).getBulkErrorsList(),
	} ) );
	const { saveBulkEdits, resetBulkGroupSettingEdits } = useDispatch(
		'ithemes-security/user-groups-editor'
	);
	const tabs = useMemo( () => [
		{
			name: 'settings',
			title: __( 'Features', 'better-wp-security' ),
			className: 'itsec-manage-user-group-tabs__tab',
			Component: TabSettingsBulk,
		},
	] );

	const onSave = () => saveBulkEdits( groupIds );
	const onReset = () => resetBulkGroupSettingEdits( groupIds );

	return (
		<>
			<MultiGroupHeader groupIds={ groupIds } />
			<Card>
				<TabPanel tabs={ tabs } isStyled>
					{ ( { Component } ) => (
						<Component groupIds={ groupIds }>
							<ErrorList errors={ errors } />
						</Component>
					) }
				</TabPanel>
			</Card>

			{ showSave && (
				<Flex>
					<FlexSpacer />
					<FlexItem>
						<Button
							variant="secondary"
							onClick={ onReset }
							disabled={ ! isDirty }
						>
							{ __( 'Undo Changes', 'better-wp-security' ) }
						</Button>
					</FlexItem>
					<FlexItem>
						<Button
							variant="primary"
							onClick={ onSave }
							isBusy={ isSaving }
							disabled={ isSaving || ! isDirty }
						>
							{ __( 'Save', 'better-wp-security' ) }
						</Button>
					</FlexItem>
				</Flex>
			) }
		</>
	);
}
