/**
 * External dependencies
 */
import classnames from 'classnames';
import { curry, find, isString } from 'lodash';

/**
 * WordPress dependencies
 */
import { DOWN, UP, ENTER, SPACE } from '@wordpress/keycodes';
import { compose, withInstanceId, pure } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';

/**
 * Internal dependencies
 */
import Detail from './Detail';
import './style.scss';

function getSelected( masters, getId, selectedId ) {
	if ( ! masters.length ) {
		return undefined;
	}

	if ( selectedId === false ) {
		return undefined;
	}

	if ( ! selectedId ) {
		return masters[ 0 ];
	}

	const selected = find( masters, ( item ) => getId( item ) === selectedId );

	if ( selected ) {
		return selected;
	}

	return masters[ 0 ];
}

function MasterDetail( {
	masters,
	masterRender: MasterRender,
	detailRender: DetailRender,
	ListHeader,
	ListFooter,
	context,
	selectedId,
	select,
	instanceId,
	mode = 'table',
	idProp = 'id',
	isSmall = false,
	direction = 'horizontal',
	borderless = false,
	children,
	hasNext,
	loadNext,
	isQuerying,
} ) {
	const getId = isString( idProp ) ? ( item ) => item[ idProp ] : idProp;
	const selected = getSelected( masters, getId, selectedId );

	const masterRefs = {};
	let containerRef;

	const onKeyDown = curry( ( pos, e ) => {
		const { keyCode } = e;

		let newPos;

		switch ( keyCode ) {
			case UP:
				if ( pos === 0 ) {
					newPos = masters.length - 1;
				} else {
					newPos = pos - 1;
				}
				break;
			case DOWN:
				if ( pos === masters.length - 1 ) {
					newPos = 0;
				} else {
					newPos = pos + 1;
				}
				break;
			case ENTER:
			case SPACE:
				e.preventDefault();
				e.stopPropagation();
				select( getId( masters[ pos ] ) );
				return;
			default:
				return;
		}

		const ref = masterRefs[ getId( masters[ newPos ] ) ];

		if ( ref ) {
			e.stopPropagation();
			e.preventDefault();
			ref.focus();

			if ( newPos === 0 ) {
				e.nativeEvent.stopImmediatePropagation();
				containerRef.scrollTop = 0;
			}
		}
	} );

	let ListEl, MasterEl;

	switch ( mode ) {
		case 'list':
			ListEl = 'ul';
			MasterEl = 'li';
			break;
		case 'table':
		default:
			ListEl = 'table';
			MasterEl = 'tr';
			break;
	}

	const masterList = masters.map( ( master, i ) => {
		const isSelected = selectedId === getId( master );

		return (
			<MasterEl
				key={ getId( master ) }
				id={ `itsec-component-master-detail-${ instanceId }__master--${ getId(
					master
				) }` }
				tabIndex={ isSelected || ( ! selectedId && i === 0 ) ? 0 : -1 }
				role="tab"
				aria-selected={ isSelected }
				aria-controls={ `itsec-component-master-detail-${ instanceId }__detail--${ getId(
					master
				) }` }
				onFocus={ () => ! isSmall && select( getId( master ) ) }
				onClick={ () => select( getId( master ) ) }
				onKeyDown={ onKeyDown( i ) }
				ref={ ( ref ) => ( masterRefs[ getId( master ) ] = ref ) }
				className={ classnames(
					'itsec-component-master-detail__master',
					{
						'itsec-component-master-detail__master--selected': isSelected,
						'itsec-component-master-detail__master--selected-default':
							0 === selectedId && i === 0,
					}
				) }
			>
				<MasterRender master={ master } />
			</MasterEl>
		);
	} );

	let next = false;

	if ( hasNext ) {
		const nextButton = (
			<Button
				variant="link"
				onClick={ loadNext }
				disabled={ isQuerying }
				isBusy={ isQuerying }
			>
				{ __( 'Load More', 'better-wp-security' ) }
			</Button>
		);

		if ( mode === 'list' ) {
			next = <li>{ nextButton }</li>;
		} else {
			next = (
				<tfoot>
					<tr>
						<td colSpan={ 100 }>{ nextButton }</td>
					</tr>
				</tfoot>
			);
		}
	}

	return (
		<section
			className={ classnames(
				'itsec-component-master-detail',
				`itsec-component-master-detail--direction-${ direction }`,
				{
					'itsec-component-master-detail--is-small': isSmall,
					'itsec-component-master-detail--has-detail': selectedId,
					'itsec-component-master-detail--borderless': borderless,
				}
			) }
		>
			<section
				className="itsec-component-master-detail__master-list-container"
				ref={ ( ref ) => ( containerRef = ref ) }
			>
				{ ListHeader && <ListHeader context={ context } /> }
				{ /* eslint-disable-next-line jsx-a11y/no-noninteractive-element-to-interactive-role */ }
				<ListEl
					className="itsec-component-master-detail__master-list"
					role="tablist"
				>
					{ children }
					{ mode === 'table' ? (
						<tbody>{ masterList }</tbody>
					) : (
						masterList
					) }
					{ next }
				</ListEl>
				{ ListFooter && <ListFooter context={ context } /> }
			</section>
			{ masters.map( ( master ) => (
				<Detail
					key={ getId( master ) }
					master={ master }
					getId={ getId }
					parentInstanceId={ instanceId }
					isSelected={ master === selected }
					DetailRender={ DetailRender }
				/>
			) ) }
		</section>
	);
}

export default compose( [ withInstanceId, pure ] )( MasterDetail );
export { default as Back } from './Back';
