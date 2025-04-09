/**
 * Internal dependencies
 */
import { CardHeader, CardHeaderTitle } from '@ithemes/security.dashboard.dashboard';
import { List, AddNew, BanHostsActions, useBanHosts } from '@ithemes/security.core.ban-hosts';
import { StyledSurface } from './styles';

export default function BannedUsers( { card, config } ) {
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
	} = useBanHosts( 'dashboard' );

	const formId = `itsec-ban-card-create-form__${ card.id }`;
	return (
		<StyledSurface>
			<CardHeader>
				<CardHeaderTitle
					card={ card }
					config={ config }
				/>
			</CardHeader>
			{ ! isCreating && (
				<>
					<List
						selected={ isCreating ? false : selected }
						onSelect={ onSelect }
						querying={ isQuerying }
						query={ query }
						queryId={ 'dashboard' }
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
		</StyledSurface>
	);
}

export const slug = 'banned-users-list';
export const settings = {
	render: BannedUsers,
};
