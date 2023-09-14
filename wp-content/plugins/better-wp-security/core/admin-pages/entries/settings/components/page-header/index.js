/**
 * External dependencies
 */
import classnames from 'classnames';
import { useLocation } from 'react-router-dom';

/**
 * Internal dependencies
 */
import { HelpPopover, Markup } from '@ithemes/security-components';
import { Breadcrumbs } from '../';
import './style.scss';

export default function PageHeader( {
	title,
	subtitle,
	description,
	help,
	align = 'left',
	breadcrumbs = true,
	children,
} ) {
	const location = useLocation();

	return (
		<>
			<header
				className={ classnames(
					'itsec-page-header',
					`itsec-page-header--align-${ align }`,
					{
						'itsec-page-header--has-actions': !! children,
						'itsec-page-header--has-help': !! help,
					}
				) }
			>
				{ breadcrumbs === true && <Breadcrumbs title={ title } /> }
				{ breadcrumbs }

				<div className="itsec-page-header__text">
					<h1 id="itsec-page-header">
						{ title }
						{ help && (
							<HelpPopover
								help={ help }
								to={ { ...location, hash: '#help' } }
							/>
						) }
					</h1>
					{ subtitle && <h2>{ subtitle }</h2> }
					{ description && (
						<Markup content={ description } tagName="p" />
					) }
				</div>

				{ children && (
					<div className="itsec-page-header__actions">{ children }</div>
				) }
			</header>
		</>
	);
}
