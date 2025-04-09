/**
 * External dependencies
 */
import { utils } from '@rjsf/core';

/**
 * WordPress dependencies
 */
import { Fragment } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Markup } from '@ithemes/security-components';

const { canExpand } = utils;

export default function ObjectFieldTemplate( props ) {
	const {
		TitleField,
		DescriptionField,
		schema,
		uiSchema,
		properties,
	} = props;

	if ( ! properties.length ) {
		return null;
	}

	const sectionMap = ( uiSchema[ 'ui:sections' ] || [] ).reduce(
		( acc, val ) => ( {
			...acc,
			[ val.fields.find(
				( field ) => !! schema.properties[ field ]
			) ]: val,
		} ),
		{}
	);

	return (
		<div className="itsec-rjsf-object-fieldset" id={ props.idSchema.$id }>
			{ ( uiSchema[ 'ui:title' ] || props.title ) && (
				<TitleField
					id={ `${ props.idSchema.$id }__title` }
					title={ props.title || uiSchema[ 'ui:title' ] }
					required={ props.required }
					formContext={ props.formContext }
				/>
			) }
			{ props.description && (
				<DescriptionField
					id={ `${ props.idSchema.$id }__description` }
					description={
						<Markup noWrap content={ props.description } />
					}
					formContext={ props.formContext }
				/>
			) }
			{ properties.map( ( { name, content } ) => {
				if ( sectionMap[ name ] ) {
					return (
						<Fragment key={ name }>
							<h3 className="itsec-rjsf-section-title">
								{ sectionMap[ name ].title }
							</h3>
							{ sectionMap[ name ].description && (
								<p className="itsec-rjsf-section-description">
									<Markup
										noWrap
										content={
											sectionMap[ name ].description
										}
									/>
								</p>
							) }
							{ content }
						</Fragment>
					);
				}

				return content;
			} ) }
			{ canExpand( schema, uiSchema, props.formData ) && (
				<AddButton
					className="object-property-expand"
					onClick={ props.onAddClick( schema ) }
					disabled={ props.disabled || props.readonly }
				/>
			) }
		</div>
	);
}

function AddButton( { className, onClick, disabled } ) {
	return (
		<div className="row">
			<p
				className={ `col-xs-3 col-xs-offset-9 text-right ${ className }` }
			>
				<Button
					icon="plus-alt2"
					className="btn-add col-xs-12"
					aria-label={ __( 'Add', 'better-wp-security' ) }
					tabIndex="0"
					onClick={ onClick }
					disabled={ disabled }
				/>
			</p>
		</div>
	);
}
