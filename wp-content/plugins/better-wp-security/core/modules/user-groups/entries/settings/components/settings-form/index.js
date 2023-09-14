/**
 * External dependencies
 */
import { map, isEmpty } from 'lodash';

export default function SettingsForm( {
	highlight,
	definitions,
	settingComponent: SettingComponent,
	...props
} ) {
	const [ highlightModule, highlightSetting ] = ( highlight || '' ).split(
		'/'
	);

	return (
		<form>
			<ul className="itsec-user-groups-group-tab__modules-list">
				{ map(
					definitions,
					( module ) =>
						! isEmpty( module.settings ) && (
							<li
								key={ module.id }
								className="itsec-user-groups-group-tab_settings-list"
							>
								<h3>{ module.title }</h3>
								{ map(
									module.settings,
									( definition, setting ) => (
										<SettingComponent
											key={ setting }
											definition={ definition }
											module={ module.id }
											setting={ setting }
											isHighlighted={
												module.id === highlightModule &&
												setting === highlightSetting
											}
											{ ...props }
										/>
									)
								) }
							</li>
						)
				) }
			</ul>
		</form>
	);
}
