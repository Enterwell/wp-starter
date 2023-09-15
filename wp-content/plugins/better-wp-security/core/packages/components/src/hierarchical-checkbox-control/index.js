/**
 * External dependencies
 */
import { isArray } from 'lodash';
import memize from 'memize';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { CheckboxControl } from '../';
import './style.scss';

class Node {
	tree;
	name;
	data;
	parent;
	children;

	constructor( tree, name, data, parent = null, children = [] ) {
		this.tree = tree;
		this.name = name;
		this.data = data;
		this.parent = parent;
		this.children = children;
	}

	/**
	 * Get the parent node.
	 *
	 * @return {Node|null} The parent node.
	 */
	getParent() {
		if ( ! this.parent ) {
			return null;
		}

		return this.tree.nodes[ this.parent ];
	}

	/**
	 * Get all parents of an option.
	 *
	 * @return {Array<string>} Parent ids.
	 */
	getAllParents() {
		const all = [];
		let parent = this.getParent();

		while ( parent ) {
			all.push( parent.name );
			parent = parent.getParent();
		}

		return all;
	}

	hasChildren() {
		return this.children.length > 0;
	}

	/**
	 * Get all children.
	 *
	 * @return {Array<string>} The children ids.
	 */
	getAllChildren() {
		const all = [];

		if ( ! this.hasChildren() ) {
			return all;
		}

		for ( const child of this ) {
			all.push( child.name, ...child.getAllChildren() );
		}

		return all;
	}

	/**
	 * Iterator over all children of the node.
	 *
	 * @yield {Node}
	 */
	*[ Symbol.iterator ]() {
		for ( let i = 0; i < this.children.length; i++ ) {
			const name = this.children[ i ];
			yield this.tree.nodes[ name ];
		}
	}
}

class Tree {
	nodes = {};
	ordered = [];

	add( name, data, parent = null ) {
		this.ordered.push( name );

		if ( this.nodes[ name ] ) {
			this.nodes[ name ].data = data;
			this.nodes[ name ].parent = parent;
		} else {
			this.nodes[ name ] = new Node( this, name, data, parent );
		}

		if ( parent ) {
			if ( this.nodes[ parent ] ) {
				this.nodes[ parent ].children.push( name );
			} else {
				this.nodes[ parent ] = new Node( this, parent );
			}
		}
	}

	*[ Symbol.iterator ]() {
		for ( let i = 0; i < this.ordered.length; i++ ) {
			const name = this.ordered[ i ];

			if ( ! this.nodes[ name ].parent ) {
				yield this.nodes[ name ];
			}
		}
	}
}

const toTree = memize( ( options ) => {
	const tree = new Tree();

	for ( const option of options ) {
		tree.add( option.value, option, option.parent );
	}

	return tree;
} );

class HierarchicalCheckboxControl extends Component {
	props;

	constructor() {
		super( ...arguments );

		this.renderOption = this.renderOption.bind( this );
		this.isChecked = this.isChecked.bind( this );
		this.isIndeterminate = this.isIndeterminate.bind( this );
		this.onChange = this.onChange.bind( this );
	}

	indeterminate( ref ) {
		ref.indeterminate = true;
	}

	/**
	 * Is the given option checked.
	 *
	 * @param {Node|null} value
	 * @return {boolean} True if checked.
	 */
	isChecked( value ) {
		if ( ! value ) {
			return false;
		}

		if ( isArray( this.props.value ) ) {
			return (
				this.props.value.includes( value.name ) ||
				this.isChecked( value.getParent() )
			);
		}

		return (
			this.props.value[ value.name ] ||
			this.isChecked( value.getParent() )
		);
	}

	/**
	 * Does this option have an indeterminate value.
	 *
	 * @param {Node} option
	 * @return {boolean} True if indeterminate.
	 */
	isIndeterminate( option ) {
		if ( ! option.hasChildren() ) {
			return false;
		}

		for ( const child of option ) {
			if ( this.isChecked( child ) ) {
				return true;
			}

			if ( this.isIndeterminate( child ) ) {
				return true;
			}
		}

		return false;
	}

	onChange( option, checked ) {
		const values = [ option.name, ...option.getAllChildren() ];
		const parents = checked ? [] : option.getAllParents();

		if ( isArray( this.props.value ) ) {
			let changed;

			if ( checked ) {
				changed = [ ...this.props.value, ...values ];
			} else {
				changed = this.props.value.filter(
					( maybeValue ) =>
						! values.includes( maybeValue ) &&
						! parents.includes( maybeValue )
				);
			}

			this.props.onChange( changed );
		} else {
			this.props.onChange( {
				...this.props.value,
				...values.reduce(
					( acc, key ) => ( acc[ key ] = checked ),
					{}
				),
				...parents.reduce( ( acc, key ) => ( acc[ key ] = false ), {} ),
			} );
		}
	}

	render() {
		const { label, help, options } = this.props;
		const tree = toTree( options );

		return (
			<div className="components-base-control">
				<div className="components-base-control__field">
					<div className="components-base-control__label">
						{ label }
					</div>
					{ help && (
						<p className="components-base-control__help">
							{ help }
						</p>
					) }
				</div>
				<ul className="components-hierarchical-checkbox-control__group">
					{ Array.from( tree, this.renderOption ) }
				</ul>
			</div>
		);
	}

	/**
	 * Render a single option and its children.
	 *
	 * @param {Node} option
	 * @return {JSX} The option component.
	 */
	renderOption( option ) {
		const { value, selectable = true, ...rest } = option.data;
		const checked = this.isChecked( option );
		const indeterminate = ! checked && this.isIndeterminate( option );

		return (
			<li
				key={ value }
				className={ classnames(
					'components-hierarchical-checkbox-control__option',
					{
						'components-hierarchical-checkbox-control__option--has-children': option.hasChildren(),
					}
				) }
			>
				<CheckboxControl
					{ ...rest }
					checked={ selectable ? checked : false }
					disabled={ ! selectable || this.props.disabled }
					indeterminate={ indeterminate }
					onChange={ ( newChecked ) =>
						this.onChange( option, newChecked )
					}
				/>
				{ option.hasChildren() && (
					<ul className="components-hierarchical-checkbox-control__group">
						{ Array.from( option, this.renderOption ) }
					</ul>
				) }
			</li>
		);
	}
}

export default HierarchicalCheckboxControl;
