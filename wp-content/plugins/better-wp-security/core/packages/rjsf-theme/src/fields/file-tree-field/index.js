/**
 * External dependencies
 */
import { cloneDeep } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { useState, useEffect } from '@wordpress/element';
import { Button } from '@wordpress/components';

/**
 * Internal dependencies
 */
import {
	Tree,
	walkTree,
	TextareaListControl,
	Markup,
} from '@ithemes/security-components';
import './style.scss';

// const { getUiOptions } = utils;

export default function FileTreeField( {
	idSchema,
	formData,
	onChange,
	uiSchema,
	schema,
	name,
	disabled,
	readonly,
	autofocus,
} ) {
	const id = idSchema.$id;
	const label = uiSchema[ 'ui:title' ] || schema.title || name;
	const description = uiSchema[ 'ui:description' ] || schema.description;
	const [ tree, setTree ] = useState( [] );
	const [ active, setActive ] = useState( '' );

	useEffect( () => {
		apiFetch( {
			path: addQueryArgs( '/ithemes-security/rpc/file-change/file-tree', {
				directory: '/',
			} ),
		} ).then( setTree );
	}, [] );

	const onLoad = async ( directory ) => {
		const items = await apiFetch( {
			path: addQueryArgs( '/ithemes-security/rpc/file-change/file-tree', {
				directory,
			} ),
		} );
		const clone = cloneDeep( tree );
		walkTree( clone, ( item ) => {
			if ( item.id === directory ) {
				item.children = items;

				return walkTree.halt;
			}
		} );
		setTree( clone );
	};

	const onActivate = ( item ) => {
		onChange(
			formData.includes( item )
				? formData.filter( ( maybe ) => maybe !== item )
				: [ ...formData, item ]
		);
	};

	return (
		<div className="itsec-rjsf-file-tree">
			{ description && <Markup content={ description } tagName="p" /> }
			<div className="itsec-rjsf-file-tree__controls">
				<div>
					<Tree
						tree={ tree }
						id={ id }
						label={ __( 'File Selector', 'better-wp-security' ) }
						active={ active }
						setActive={ setActive }
						onActivate={ onActivate }
						onLoad={ onLoad }
					/>
					<Button
						variant="secondary"
						disabled={ ! active }
						className="itsec-rjsf-file-tree__select"
						onClick={ () => onActivate( active ) }
						aria-keyshortcuts="Enter Space"
					>
						{ __( 'Select', 'better-wp-security' ) }
					</Button>
				</div>
				<TextareaListControl
					value={ formData }
					onChange={ onChange }
					className="itsec-rjsf-file-tree__list"
					label={ label }
					disabled={ disabled }
					readonly={ readonly }
					autoFocus={ autofocus } // eslint-disable-line jsx-a11y/no-autofocus
				/>
			</div>
		</div>
	);
}
