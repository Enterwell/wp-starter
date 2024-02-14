/**
 * WordPress dependenices
 */
import { useInstanceId } from '@wordpress/compose';
import { FlexBlock } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { PageHeader, Surface } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { AddNew, BanHostsActions, List, useBanHosts } from '@ithemes/security.core.ban-hosts';

export default function BannedIPs() {
	const {
		isCreating,
		setCreating,
		isSaving,
		setSaving,
		isQuerying,
		createBan,
		afterSave,
		query,
		selected,
		onSelect,
	} = useBanHosts( 'firewall' );
	const formId = useInstanceId( BannedIPs, 'itsec-banned-ips-form-' );

	return (
		<FlexBlock>
			<Surface>
				<PageHeader
					title={ __( 'Banned IPs', 'better-wp-security' ) }
					description={ __( 'Add, remove and edit banned IPs.', 'better-wp-security' ) }
					fullWidth
					hasBorder
				/>
				{ ! isCreating && (
					<>
						<List
							selected={ isCreating ? false : selected }
							onSelect={ onSelect }
							querying={ isQuerying }
							query={ query }
							queryId={ 'firewall' }
							className={ 'itsec-banned-ips-data' }
						/>
					</>
				) }
				{ isCreating && (
					<AddNew
						id={ formId }
						createForm={ isCreating }
						save={ createBan }
						setSaving={ setSaving }
						afterSave={ afterSave }
					/>
				) }
				<BanHostsActions
					isCreating={ isCreating }
					isSaving={ isSaving }
					setCreating={ setCreating }
					formId={ formId }
				/>
			</Surface>
		</FlexBlock>
	);
}
