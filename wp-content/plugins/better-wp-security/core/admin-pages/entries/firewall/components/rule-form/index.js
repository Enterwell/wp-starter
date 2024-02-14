/**
 * External dependencies
 */
import { map } from 'lodash';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalInputControl as InputControl,
	BaseControl,
	Flex,
	FlexBlock,
	FlexItem,
	VisuallyHidden,
} from '@wordpress/components';
import { useInstanceId, useViewportMatch } from '@wordpress/compose';
import { closeSmall as removeIcon } from '@wordpress/icons';

/**
 * Solid dependencies
 */
import { Heading, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { Select, CreatableSelect } from '@ithemes/security-ui';
import { StyledRuleAction, halfFlexBasis } from './styles';

const DEFAULT = {
	inclusive: true,
};

export default function RuleForm( { value, onChange, className } ) {
	const id = useInstanceId( RuleForm, 'solid-rule-form' );
	const { config = { rules: [ DEFAULT ] } } = value;
	const onAndRule = ( after ) => () => {
		onChange( {
			...value,
			config: {
				...config,
				rules: config.rules.toSpliced( after + 1, 0, DEFAULT ),
			},
		} );
	};

	return (
		<Flex direction="column" gap={ 4 } align="stretch" expanded={ false } className={ className }>
			<InputControl
				value={ value.name ?? '' }
				onChange={ ( next ) => onChange( { ...value, name: next } ) }
				label={ __( 'Rule Name', 'better-wp-security' ) }
				required
				__next36pxDefaultSize
			/>
			<Heading level={ 3 } text={ __( 'If incoming requests match…', 'better-wp-security' ) } weight={ TextWeight.HEAVY } />
			<Flex direction="column" gap={ 3 } align="stretch" expanded={ false }>
				{ config.rules.map( ( rule, i ) => (
					<Rule
						key={ i }
						idx={ i }
						value={ rule }
						onAndRule={ onAndRule( i ) }
						onChange={ ( newRule ) => onChange( {
							...value,
							config: {
								...config,
								rules: config.rules.map( ( oldRule, j ) => j === i ? newRule : oldRule ),
							},
						} ) }
						onDelete={ config.rules.length === 1 ? null : () => onChange( {
							...value,
							config: {
								...config,
								rules: config.rules.toSpliced( i, 1 ),
							},
						} ) }
					/>
				) ) }
			</Flex>
			<Heading level={ 3 } text={ __( 'Then take action…', 'better-wp-security' ) } weight={ TextWeight.HEAVY } />
			<Flex direction="column" gap={ 3 } align="stretch" expanded={ false }>
				<BaseControl
					id={ id + '__action' }
					label={ __( 'Action', 'better-wp-security' ) }
					__nextHasNoMarginBottom
				>
					<Select
						inputId={ id + '__action' }
						options={ ACTIONS }
						value={ ACTIONS.find( ( action ) => action.value === config.type ) }
						onChange={ ( next ) => onChange( {
							...value,
							config: {
								...config,
								type: next.value,
								type_params: '',
							},
						} ) }
					/>
				</BaseControl>
				{ config.type === 'REDIRECT' && (
					<InputControl
						value={ config.type_params ?? '' }
						onChange={ ( next ) => onChange( {
							...value,
							config: {
								...config,
								type_params: next,
							},
						} ) }
						type="url"
						label={ __( 'Redirect Location', 'better-wp-security' ) }
						__next36pxDefaultSize
					/>
				) }
			</Flex>
		</Flex>
	);
}

function Rule( { idx, value, onChange, onDelete, onAndRule } ) {
	const isLarge = useViewportMatch( 'large' );
	const id = useInstanceId( Rule, 'solid-rule-form-rule' );
	const selectedField = value.parameter &&
		FIELDS.find( ( field ) => isField( value.parameter, field ) );
	const allowedOperators = OPERATORS
		.filter( ( operator ) => selectedField?.operators === true || selectedField?.operators.includes( operator.value ) );
	const selectedOperator = value.match?.type &&
		allowedOperators.find( ( operator ) => operator.value === value.match?.type );

	return (
		<fieldset>
			<VisuallyHidden as="legend">
				{ sprintf(
					/* translators: Which number rule is this in the list. */
					__( 'Rule %d', 'better-wp-security' ), idx + 1
				) }
			</VisuallyHidden>
			<Flex
				gap={ 1 }
				align={ isLarge ? 'start' : 'stretch' }
				direction={ isLarge ? 'row' : 'column' }
				expanded={ isLarge }
			>
				<FlexBlock>
					<Flex align="start" gap={ 1 }>
						<FlexBlock className={ halfFlexBasis }>
							<FieldControl id={ id } field={ selectedField } value={ value } onChange={ onChange } />
						</FlexBlock>
						{ selectedField?.allowSubFields && (
							<FlexItem className={ halfFlexBasis }>
								<SubFieldControl field={ selectedField } value={ value } onChange={ onChange } />
							</FlexItem>
						) }
					</Flex>
				</FlexBlock>
				<FlexBlock>
					<OperatorControl
						id={ id }
						operator={ selectedOperator }
						allowedOperators={ allowedOperators }
						value={ value }
						onChange={ onChange }
					/>
				</FlexBlock>
				<FlexBlock>
					<ValueControl
						id={ id }
						field={ selectedField }
						operator={ selectedOperator }
						value={ value }
						onChange={ onChange }
					/>
				</FlexBlock>
				<FlexItem>
					<Flex gap={ 1 } justify="start">
						<StyledRuleAction
							onClick={ onAndRule }
							variant="secondary"
							text={ __( 'And', 'better-wp-security' ) }
						/>
						{ onDelete && (
							<StyledRuleAction
								onClick={ onDelete }
								variant="tertiary"
								icon={ removeIcon }
								label={ __( 'Remove', 'better-wp-security' ) }
							/>
						) }
					</Flex>
				</FlexItem>
			</Flex>
		</fieldset>
	);
}

function FieldControl( { id, field, value, onChange } ) {
	return (
		<BaseControl
			id={ id + '__field' }
			label={ __( 'Field', 'better-wp-security' ) }
			help={ __( 'Select a field to inspect.', 'better-wp-security' ) }
			__nextHasNoMarginBottom
		>
			<Select
				inputId={ id + '__field' }
				key={ field?.value }
				options={ FIELDS }
				value={ field }
				onChange={ ( next ) => onChange( {
					...value,
					parameter: next.value,
					match: {
						type: 'equals',
					},
				} ) }
				isOptionSelected={ ( maybeOption, selected ) => selected.some( ( selectedOption ) => isField( maybeOption.value, selectedOption ) ) }
				required
			/>
		</BaseControl>
	);
}

function SubFieldControl( { field, value, onChange } ) {
	const { example, sanitize, display } = field.allowSubFields;

	return (
		<InputControl
			label={ __( 'Name', 'better-wp-security' ) }
			help={ sprintf(
				/* translators: 1. Example value. */
				__( 'e.g. %s', 'better-wp-security' ),
				example
			) }
			value={ display( value.parameter?.replace( field.value, '' ) ?? '' ) }
			onChange={ ( next ) => onChange( {
				...value,
				parameter: field.value + sanitize( next ),
			} ) }
			required
			__next36pxDefaultSize
		/>
	);
}

function OperatorControl( { id, operator, allowedOperators, value, onChange } ) {
	return (
		<BaseControl
			id={ id + '__operator' }
			label={ __( 'Operator', 'better-wp-security' ) }
			__nextHasNoMarginBottom
		>
			<Select
				inputId={ id + '__operator' }
				options={ allowedOperators }
				value={ operator }
				onChange={ ( next ) => onChange( {
					...value,
					match: {
						...( value.match || {} ),
						type: next.value,
						value: ( () => {
							const current = value.match?.value;

							if ( ! current ) {
								return next.isList ? [] : '';
							}

							if ( next.isList ) {
								return Array.isArray( current ) ? current : [ current ];
							}

							return Array.isArray( current ) ? current[ 0 ] : current;
						} )(),
					},
				} ) }
				isDisabled={ ! allowedOperators.length }
				required
			/>
		</BaseControl>
	);
}

function ValueControl( { id, field, operator, value, onChange } ) {
	if ( operator?.isList ) {
		return (
			<BaseControl
				id={ id + '__value' }
				label={ __( 'Value', 'better-wp-security' ) }
				__nextHasNoMarginBottom
			>
				<CreatableSelect
					inputId={ id + '__value' }
					key={ field?.value }
					options={ field?.listOptions?.map( ( option ) => ( {
						value: option,
						label: option,
					} ) ) }
					value={ value.match?.value?.map( ( option ) => ( {
						value: option,
						label: option,
					} ) ) }
					onChange={ ( next ) => onChange( {
						...value,
						match: {
							...( value.match || {} ),
							value: map( next, 'value' ),
						},
					} ) }
					isMulti
					isClearable
					required
				/>
			</BaseControl>
		);
	}

	if ( field?.listOptions ) {
		return (
			<BaseControl
				id={ id + '__value' }
				label={ __( 'Value', 'better-wp-security' ) }
				__nextHasNoMarginBottom
			>
				<CreatableSelect
					inputId={ id + '__value' }
					key={ field?.value }
					options={ field?.listOptions?.map( ( option ) => ( {
						value: option,
						label: option,
					} ) ) }
					value={ { value: value.match?.value ?? '', label: value.match?.value ?? '' } }
					onChange={ ( next ) => onChange( {
						...value,
						match: {
							...( value.match || {} ),
							value: next.value,
						},
					} ) }
					isClearable
					required
				/>
			</BaseControl>
		);
	}

	return (
		<InputControl
			label={ __( 'Value', 'better-wp-security' ) }
			help={ field?.example && sprintf(
				/* translators: 1. Example value. */
				__( 'e.g. %s', 'better-wp-security' ),
				field.example
			) }
			value={ value.match?.value }
			onChange={ ( next ) => onChange( {
				...value,
				match: {
					...( value.match || {} ),
					value: next,
				},
			} ) }
			disabled={ ! field }
			required
			__next36pxDefaultSize
		/>
	);
}

function isField( value, maybeField ) {
	if ( maybeField.value === value ) {
		return true;
	}

	if ( maybeField.allowSubFields && value.startsWith( maybeField.value ) ) {
		return true;
	}

	return false;
}

const FIELDS = [
	{
		value: 'server.REQUEST_URI',
		label: __( 'URI', 'better-wp-security' ),
		operators: [ 'equals', 'contains', 'not_contains' ],
		example: '/test?param=value',
	},
	{
		value: 'server.REQUEST_METHOD',
		label: __( 'Request Method', 'better-wp-security' ),
		operators: true,
		listOptions: [
			'GET',
			'HEAD',
			'POST',
			'PUT',
			'PATCH',
			'DELETE',
			'OPTIONS',
		],
	},
	{
		value: 'server.CONTENT_TYPE',
		label: __( 'Content Type', 'better-wp-security' ),
		operators: true,
	},
	{
		value: 'server.HTTP_',
		label: __( 'Header', 'better-wp-security' ),
		operators: true,
		allowSubFields: {
			example: 'user-agent',
			sanitize( value ) {
				return value.toUpperCase().replace( '-', '_' );
			},
			display( value ) {
				return value.toLowerCase().replace( '_', '-' );
			},
		},
	},
	{
		value: 'cookie.',
		label: __( 'Cookie', 'better-wp-security' ),
		operators: true,
		allowSubFields: {
			example: 'my-cookie',
			sanitize( value ) {
				return value.replace( '.', '_' );
			},
			display( value ) {
				return value;
			},
		},
	},
	{
		value: 'server.ip',
		label: __( 'IP Address' ),
		operators: [ 'equals', 'in_array', 'not_in_array' ],
		example: '127.0.0.1',
	},
];

const OPERATORS = [
	{
		value: 'equals',
		label: __( 'equals', 'better-wp-security' ),
	},
	{
		value: 'contains',
		label: __( 'contains', 'better-wp-security' ),
	},
	{
		value: 'not_contains',
		label: __( 'does not contain', 'better-wp-security' ),
	},
	{
		value: 'in_array',
		label: __( 'is in', 'better-wp-security' ),
		isList: true,
	},
	{
		value: 'not_in_array',
		label: __( 'is not in', 'better-wp-security' ),
		isList: true,
	},
];

const ACTIONS = [
	{
		value: 'BLOCK',
		label: __( 'Block', 'better-wp-security' ),
	},
	{
		value: 'REDIRECT',
		label: __( 'Redirect', 'better-wp-security' ),
	},
	{
		value: 'LOG',
		label: __( 'Log only', 'better-wp-security' ),
	},
	{
		value: 'WHITELIST',
		label: __( 'Allow', 'better-wp-security' ),
	},
];
