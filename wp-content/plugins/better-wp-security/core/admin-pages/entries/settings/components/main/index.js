/**
 * External dependencies
 */
import { useLocation } from 'react-router-dom';
import { ErrorBoundary } from 'react-error-boundary';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { useEffect, useRef, useState } from '@wordpress/element';
import { createSlotFill } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { Toolbar, Help, ErrorRenderer } from '../';
import { useCurrentPage } from '../../page-registration';
import './style.scss';

export default function Main( { children } ) {
	const page = useCurrentPage();

	// Focus handling
	const ref = useRef();
	const location = useLocation();
	const { hash } = location;
	useEffect( () => {
		ref.current?.focus();
		ref.current?.ownerDocument.body.scrollTo( 0, 0 );
	}, [ location ] );

	// Aside handling
	const [ hasAside, setHasAside ] = useState( false );

	return (
		<div className="itsec-settings-main" ref={ ref } tabIndex={ -1 }>
			<Toolbar />
			<div
				className={ classnames( 'itsec-settings-main__wrapper', {
					'itsec-settings-main__wrapper--has-aside': hasAside,
				} ) }
			>
				<main
					aria-labelledby="itsec-page-header"
					className={ classnames( 'itsec-settings-main__content', {
						[ `itsec-page--${ page?.id }` ]: !! page,
					} ) }
				>
					<ErrorBoundary FallbackComponent={ ErrorRenderer }>
						<Help />
						{ hash === '#help' ? (
							<div hidden>{ children }</div>
						) : (
							children
						) }
					</ErrorBoundary>
				</main>
				<AsideSlot>
					{ ( fills ) => (
						<AsideContent
							fills={ fills }
							setHasAside={ setHasAside }
						/>
					) }
				</AsideSlot>
			</div>
		</div>
	);
}

function AsideContent( { fills, setHasAside } ) {
	useEffect( () => setHasAside( fills.length > 0 ), [ fills ] );

	if ( fills.length ) {
		return <aside className="itsec-aside">{ fills }</aside>;
	}

	return null;
}

const { Slot: AsideSlot, Fill: AsideFill } = createSlotFill( 'Aside' );

export { AsideFill };
