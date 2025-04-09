/**
 * External dependencies
 */
import { groupBy } from 'lodash';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { Card, Dashicon, Modal, Button } from '@wordpress/components';
import { useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { HELP_STORE_NAME } from '@ithemes/security.packages.data';
import { Markup } from '@ithemes/security-components';
import './style.scss';

export default function HelpList( { topic, fallback } ) {
	const isEnabled = useSelect( ( select ) =>
		select( HELP_STORE_NAME ).isEnabled()
	);

	if ( isEnabled === undefined ) {
		return null;
	}

	return isEnabled ? (
		<RemoteHelp topic={ topic } fallback={ fallback } />
	) : (
		<EnableModal />
	);
}

function EnableModal() {
	const [ isOpen, setOpen ] = useState( true );
	const [ isBusy, setBusy ] = useState( false );
	const { enableHelp } = useDispatch( HELP_STORE_NAME );
	const onContinue = async () => {
		setBusy( true );
		await enableHelp();
		setBusy( false );
	};

	return (
		isOpen && (
			<Modal
				title={ __( 'Privacy Notice', 'better-wp-security' ) }
				onRequestClose={ () => setOpen( false ) }
				className="itsec-help-list__enable-modal"
			>
				<p>
					{ __(
						'Loading help remotely requires making an API request to iThemes.com. Only the requested help topic is transmitted.',
						'better-wp-security'
					) }
				</p>
				<footer>
					<a href="https://go.solidwp.com/privacy-policy">
						{ __( 'Privacy Policy', 'better-wp-security' ) }
					</a>
					<Button isBusy={ isBusy } onClick={ onContinue } variant="primary">
						{ __( 'Continue', 'better-wp-security' ) }
					</Button>
				</footer>
			</Modal>
		)
	);
}

function RemoteHelp( { topic, fallback } ) {
	const { help, isLoaded, fallbackHelp, fallbackLoaded } = useSelect(
		( select ) => ( {
			help: select( HELP_STORE_NAME ).getHelp( topic ),
			isLoaded: select(
				HELP_STORE_NAME
			).hasFinishedResolution( 'getHelp', [ topic ] ),
			fallbackHelp: fallback
				? select( HELP_STORE_NAME ).getHelp( fallback )
				: [],
			fallbackLoaded: fallback
				? select( HELP_STORE_NAME ).hasFinishedResolution( 'getHelp', [
					fallback,
				] )
				: true,
		} )
	);

	if ( ! isLoaded || ! fallbackLoaded ) {
		return null;
	}

	const byType = groupBy( [ ...help, ...fallbackHelp ], 'type' );

	return (
		<>
			<Section
				title={ __( 'Help Center', 'better-wp-security' ) }
				icon="sos"
				link="https://help.ithemes.com/hc/en-us/categories/200147050/"
				items={ byType.hc }
			/>

			<Section
				title={ __( 'Blog', 'better-wp-security' ) }
				icon="book-alt"
				link="https://ithemes.com/blog/"
				items={ byType.post }
			/>

			<Section
				title={ __( 'Video', 'better-wp-security' ) }
				icon="youtube"
				link="https://www.youtube.com/channel/UCYSDQEcxAppePTn5E7iNpFg"
				items={ byType.video }
			/>
		</>
	);
}

function Section( { title, icon, link, items } ) {
	return (
		<Card
			className={ classnames( 'itsec-help-list-section', {
				'itsec-help-list-section--has-content': !! items,
			} ) }
		>
			<header>
				<a href={ link }>
					<Dashicon
						icon={ icon }
						className="itsec-help-list-section__icon"
					/>
					<h3>{ title }</h3>
					<Dashicon
						icon="arrow-right-alt"
						className="itsec-help-list-section__more"
					/>
				</a>
			</header>
			<section>
				{ items ? (
					items.map( ( item ) => (
						<article key={ item.title }>
							<a href={ item.link }>
								<h4>{ item.title }</h4>
								<Markup
									content={ item.description }
									tagName="p"
								/>
							</a>
						</article>
					) )
				) : (
					<p>{ __( 'No relevant content at this time.', 'better-wp-security' ) }</p>
				) }
			</section>
		</Card>
	);
}
