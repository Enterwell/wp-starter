/**
 * Internal dependencies
 */
import { CheckboxGroupControl, Markup } from '@ithemes/security-components';

export default function CheckboxesWidget( props ) {
	const {
		disabled,
		options,
		value,
		readonly,
		onChange,
		label,
		schema,
		uiSchema = {},
	} = props;
	const { enumOptions, enumDisabled = [], enumDescriptions = [] } = options;

	const description = uiSchema[ 'ui:description' ] || schema.description;
	const optionList = enumOptions.map( ( option, i ) => ( {
		...option,
		disabled: enumDisabled.includes( option.value ),
		help: enumDescriptions[ i ] && (
			<Markup noWrap content={ enumDescriptions[ i ] } />
		),
	} ) );

	return (
		<CheckboxGroupControl
			value={ value || [] }
			onChange={ onChange }
			options={ optionList }
			label={ label || uiSchema[ 'ui:title' ] || schema.title }
			help={ <Markup noWrap content={ description } /> }
			readOnly={ readonly }
			disabled={ disabled }
		/>
	);
}
