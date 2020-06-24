/**
 * External dependencies
 */
import { map } from 'lodash';

export default function SettingsForm( { schema, settingComponent: SettingComponent, ...props } ) {
	return (
		<ul className="itsec-user-groups-group-tab__modules-list">
			{ map( schema.properties, ( moduleSchema, module ) => (
				<li key={ module }>
					<fieldset>
						<legend>{ moduleSchema.title }</legend>
						<ul>
							{ map( moduleSchema.properties, ( settingSchema, setting ) => (
								<li key={ setting }>
									<SettingComponent schema={ settingSchema } module={ module } setting={ setting } { ...props } />
								</li>
							) ) }
						</ul>
					</fieldset>
				</li>
			) ) }
		</ul>
	);
}
