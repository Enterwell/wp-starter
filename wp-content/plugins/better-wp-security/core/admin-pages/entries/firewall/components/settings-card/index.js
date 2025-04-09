import { PageHeader } from '@ithemes/ui';
import { ModuleCard } from '@ithemes/security.pages.settings';
import { StyledModulePanelContainer } from './styles';

export default function SettingsCard( { module } ) {
	return (
		<>
			<PageHeader
				title={ module.title }
				description={ module.description }
				fullWidth
				hasBorder
			/>
			<StyledModulePanelContainer>
				<ModuleCard module={ module } persistStatus includeTitle />
			</StyledModulePanelContainer>
		</>
	);
}
