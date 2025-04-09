/**
 * External dependencies
 */
import classnames from 'classnames';
import { mapValues } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useInstanceId } from '@wordpress/compose';
import { useDispatch, useSelect } from '@wordpress/data';
import {
	Children,
	Fragment,
	useEffect,
	useMemo,
	useState,
} from '@wordpress/element';
import {
	CardHeader,
	Fill,
	Flex,
	FlexItem,
	Slot,
} from '@wordpress/components';

/**
 * SolidWP dependencies
 */
import { MODULES_STORE_NAME, toolsStore } from '@ithemes/security.packages.data';
import {
	Button,
	MessageList,
	Text,
	TextVariant,
} from '@ithemes/ui';
import { ErrorList } from '@ithemes/security-ui';
import { PrimarySchemaFormInputs } from '@ithemes/security-schema-form';

/**
 * Internal dependencies
 */
import {
	StyledCardContainer,
	StyledToolContainer,
	StyledToolHeading,
	StyledCardBody,
	StyledResult,
	StyledTextContainer,
	StyledHelpText,
	StyledInputContainer,
	StyledToolActionContainer,
	StyledToolActionMessage,
} from './styles';
import { getAjv } from '../../utils';

export default function Tools() {
	const { tools, activeModules, isLoaded } = useSelect( ( select ) => ( {
		tools: select( toolsStore ).getResolvedTools(),
		activeModules: select( MODULES_STORE_NAME ).getActiveModules(),
		isLoaded: select( toolsStore ).hasFinishedResolution( 'getTools' ),
	} ), [] );

	const activeTools = useMemo(
		() =>
			tools
				.filter(
					( tool ) =>
						activeModules.includes(
							tool.module ) &&
						tool.available !== false
				), [ tools, activeModules ]
	);

	return (
		<StyledCardContainer>
			{ activeTools.length > 0 && isLoaded && (
				activeTools.map( ( tool ) => (
					<ToolPanel key={ tool.slug } tool={ tool } />
				) )
			) }
		</StyledCardContainer>
	);
}

function ToolPanel( { tool, className, ...rest } ) {
	const { result, isRunning } = useSelect( ( select ) => ( {
		result: select( toolsStore ).getLastResult( tool.slug ),
		isRunning: select( toolsStore ).isRunning( tool.slug ),
	} ), [ tool.slug ] );

	const [ schemaError, setSchemaError ] = useState( [] );
	useEffect( () => setSchemaError( [] ), [ tool ] );
	return (
		<StyledToolContainer
			className={ classnames( className, 'itsec-tool' ) }
			isRounded
			size="small"
			{ ...rest }
		>
			<CardHeader size="small">
				<StyledToolHeading
					level={ 3 }
					variant={ TextVariant.DARK }
					weight={ 600 }
					text={ tool.title }
				/>
			</CardHeader>

			<StyledCardBody>
				<ResultSummary result={ result } schemaError={ schemaError } />
				<StyledTextContainer>

					<Text variant={ TextVariant.DARK } text={ tool.description } />

					{ tool.help && <StyledHelpText content={ tool.help } tagName="p" /> }

				</StyledTextContainer>
				<ToolSlot tool={ tool.slug } />

				<RunTool
					tool={ tool }
					setSchemaError={ setSchemaError }
					isRunning={ isRunning }
				/>
			</StyledCardBody>
		</StyledToolContainer>
	);
}

function ResultSummary( { result, schemaError } ) {
	return (
		<StyledResult>
			<ErrorList schemaError={ schemaError } apiError={ result?.error } />

			<MessageList messages={ result?.success ?? [] } type="success" />

			<MessageList messages={ result?.warning ?? [] } type="warning" />

			<MessageList messages={ result?.info ?? [] } type="info" />

		</StyledResult>
	);
}

function RunTool( { tool, setSchemaError, isRunning } ) {
	const isActive = useIsToolConditionActive( tool );
	const id = useInstanceId( RunTool, 'itsec-tool-form' );
	const formContext = useMemo( () => ( {
		disableInlineErrors: true,
		tool: tool.slug,
	} ), [ tool.slug ] );
	const { runTool } = useDispatch( toolsStore );
	const onSubmit = ( { formData } ) => {
		setSchemaError( [] );
		runTool( tool.slug, formData );
	};

	const hasMessage = ! isActive && tool.condition?.description;

	return (
		<StyledToolActionContainer hasMessage={ hasMessage }>
			{ tool.form && isActive && (
				<StyledInputContainer>
					<PrimarySchemaFormInputs
						id={ id }
						idPrefix={ `itsec_tool_${ tool.slug }` }
						schema={ tool.form }
						uiSchema={ tool.form.uiSchema }
						formContext={ formContext }
						showErrorList={ false }
						onError={ setSchemaError }
						onSubmit={ onSubmit }
					/>
				</StyledInputContainer>
			) }

			{ ! isActive && tool.condition?.description && (
				<StyledToolActionMessage
					type="warning"
					messages={ [ tool.condition.description ] }
				/>
			) }

			<Flex className="itsec-tool__actions" justify="flex-end" align="flex-end">
				<FlexItem>
					<Button
						variant="primary"
						className="itsec-tool__trigger"
						type={ tool.form ? 'submit' : 'button' }
						form={ tool.form ? id : undefined }
						onClick={
							tool.form ? undefined : () => runTool( tool.slug )
						}
						isBusy={ isRunning }
						disabled={ ! isActive }
					>
						{ __( 'Run', 'better-wp-security' ) }
					</Button>
				</FlexItem>
				<ToolSlot
					tool={ tool.slug }
					fillProps={ { isActive } }
					area="actions"
				>
					{ ( fills ) =>
						Children.map( fills, ( child, i ) => (
							<Fragment key={ i }>
								{ child && <FlexItem>{ child }</FlexItem> }
							</Fragment>
						) )
					}
				</ToolSlot>
			</Flex>
		</StyledToolActionContainer>
	);
}

export function ToolFill( { tool, area = 'main', ...props } ) {
	return <Fill name={ `Tool${ area }${ tool }` } { ...props } />;
}

function ToolSlot( { tool, area = 'main', ...props } ) {
	return <Slot name={ `Tool${ area }${ tool }` } { ...props } />;
}

function useIsToolConditionActive( tool ) {
	const { activeModules, settings } = useSelect(
		( select ) => ( {
			settings: mapValues(
				tool.condition?.settings || {},
				( value, key ) =>
					select( MODULES_STORE_NAME ).getSettings( key )
			),
			activeModules: select( MODULES_STORE_NAME ).getActiveModules(),
		} ),
		[ tool ]
	);

	if ( ! tool.condition ) {
		return true;
	}

	if ( tool.condition[ 'active-modules' ] ) {
		for ( const activeModule of tool.condition[ 'active-modules' ] ) {
			if ( ! activeModules?.includes( activeModule ) ) {
				return false;
			}
		}
	}

	if ( tool.condition.settings ) {
		const ajv = getAjv();

		for ( const [ module, schema ] of Object.entries(
			tool.condition.settings
		) ) {
			const validate = ajv.compile( schema );

			if ( ! validate( settings[ module ] ) ) {
				return false;
			}
		}
	}

	return true;
}

