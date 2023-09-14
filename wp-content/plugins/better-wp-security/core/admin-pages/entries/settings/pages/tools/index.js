/**
 * External dependencies
 */
import classnames from 'classnames';
import { mapValues } from 'lodash';
import { useLocation } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useMemo, useState, Children } from '@wordpress/element';
import {
	Card,
	Button,
	Slot,
	Fill,
	Flex,
	FlexItem,
} from '@wordpress/components';
import { useInstanceId } from '@wordpress/compose';
import { useSelect, useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import {
	Accordion,
	MessageList,
	ErrorList,
	HelpList,
	Markup,
	Spinner,
} from '@ithemes/security-components';
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import {
	HelpFill,
	PageHeader,
	PrimarySchemaFormInputs,
} from '../../components';
import { TOOLS_STORE_NAME } from '../../stores';
import { getAjv } from '../../utils';
import './style.scss';

export default function Tools() {
	const { hash } = useLocation();
	const { tools, running, activeModules, isLoaded } = useSelect(
		( select ) => ( {
			tools: select( TOOLS_STORE_NAME ).getResolvedTools(),
			running: select( TOOLS_STORE_NAME ).getRunning(),
			activeModules: select( MODULES_STORE_NAME ).getActiveModules(),
			isLoaded: select( TOOLS_STORE_NAME ).hasFinishedResolution(
				'getTools'
			),
		} )
	);
	const panels = useMemo(
		() =>
			tools
				.filter(
					( tool ) =>
						activeModules.includes( tool.module ) &&
						tool.available !== false
				)
				.map( ( tool ) => ( {
					name: tool.slug,
					title: tool.title,
					description: tool.description,
					showSpinner: (
						<Spinner
							size={ 30 }
							paused={ ! running.includes( tool.slug ) }
						/>
					),
					render: ToolPanel,
				} ) ),
		[ tools, running, activeModules ]
	);
	const [ expanded, setExpanded ] = useState( '' );

	useEffect( () => {
		if ( hash ) {
			setExpanded( hash.slice( 1 ) );
		}
	}, [ hash ] );

	return (
		<>
			<HelpFill>
				<PageHeader title={ __( 'Tools', 'better-wp-security' ) } />
				<HelpList topic="tools" />
			</HelpFill>
			<PageHeader title={ __( 'Tools', 'better-wp-security' ) } />
			{ tools.length > 0 && isLoaded && (
				<Card>
					<Accordion
						className="itsec-tools-list"
						isStyled
						panels={ panels }
						allowNone
						expanded={ expanded }
						setExpanded={ setExpanded }
					/>
				</Card>
			) }
		</>
	);
}

function ToolPanel( { name, className, ...rest } ) {
	const { tool, result, isRunning } = useSelect( ( select ) => ( {
		tool: select( TOOLS_STORE_NAME ).getTool( name ),
		result: select( TOOLS_STORE_NAME ).getLastResult( name ),
		isRunning: select( TOOLS_STORE_NAME ).isRunning( name ),
	} ) );

	const [ schemaError, setSchemaError ] = useState( [] );
	useEffect( () => setSchemaError( [] ), [ name ] );

	return (
		<div className={ classnames( className, 'itsec-tool' ) } { ...rest }>
			<ResultSummary result={ result } schemaError={ schemaError } />
			{ tool.help && <Markup content={ tool.help } tagName="p" /> }

			<ToolSlot tool={ tool.slug } />

			{ tool.toggleable ? (
				<></>
			) : (
				<RunTool
					tool={ tool }
					setSchemaError={ setSchemaError }
					isRunning={ isRunning }
				/>
			) }
		</div>
	);
}

function ResultSummary( { result, schemaError } ) {
	return (
		<>
			<ErrorList schemaError={ schemaError } apiError={ result?.error } />
			<MessageList messages={ result?.success } type="success" />
			<MessageList messages={ result?.warning } type="warning" />
			<MessageList messages={ result?.info } type="info" />
		</>
	);
}

function RunTool( { tool, setSchemaError, isRunning } ) {
	const isActive = useIsToolConditionActive( tool );
	const id = useInstanceId( RunTool, 'itsec-tool-form' );
	const formContext = useMemo( () => ( {
		disableInlineErrors: true,
		tool: tool.slug,
	} ) );
	const { runTool } = useDispatch( TOOLS_STORE_NAME );
	const onSubmit = ( { formData } ) => {
		setSchemaError( [] );
		runTool( tool.slug, formData );
	};

	return (
		<>
			{ tool.form && isActive && (
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
			) }
			<Flex className="itsec-tool__actions" justify="flex-start">
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
							<FlexItem key={ i }>{ child }</FlexItem>
						) )
					}
				</ToolSlot>
			</Flex>

			{ ! isActive && tool.condition?.description && (
				<MessageList
					type="warning"
					messages={ [ tool.condition.description ] }
				/>
			) }
		</>
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
			if ( ! activeModules.includes( activeModule ) ) {
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
