/**
 * External dependencies
 */
import { map, isEmpty } from 'lodash';

/**
 * Solid dependencies
 */
import { Heading, TextSize, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import './style.scss';

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
		<ul className="itsec-user-groups-setting-form">
			{ map(
				definitions,
				( module ) =>
					! isEmpty( module.settings ) && (
						<li
							key={ module.id }
							className="itsec-user-groups-setting-form__module"
						>
							<Heading
								level={ 3 }
								size={ TextSize.NORMAL }
								variant={ TextVariant.DARK }
								weight={ TextWeight.HEAVY }
								text={ module.title }
							/>
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
	);
}
