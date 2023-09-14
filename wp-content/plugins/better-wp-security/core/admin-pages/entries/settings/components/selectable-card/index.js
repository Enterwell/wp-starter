/**
 * External dependencies
 */
import { Link } from 'react-router-dom';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { Card, CardBody, Icon } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './style.scss';

export default function SelectableCard( {
	to,
	onClick,
	title,
	description,
	icon,
	fillIcon,
	recommended,
	direction = 'horizontal',
	className: userClassName,
} ) {
	const className = classnames(
		'itsec-selectable-card',
		`itsec-selectable-card--${ direction }`,
		userClassName,
		{
			'itsec-selectable-card--fill-icon': fillIcon,
			'itsec-selectable-card--recommended': recommended,
		}
	);

	const card = (
		<Card>
			<CardBody>
				<div className="itsec-selectable-card__content">
					<Icon icon={ icon } />
					<div className="itsec-selectable-card__text">
						<h4 className="itsec-selectable-card__title">
							{ title }
						</h4>
						<p className="itsec-selectable-card__description">
							{ description }
						</p>
					</div>
				</div>
			</CardBody>
		</Card>
	);

	if ( to ) {
		return (
			<Link to={ to } className={ className }>
				{ card }
			</Link>
		);
	}

	if ( onClick ) {
		return (
			<button
				aria-label={ title }
				type="button"
				onClick={ onClick }
				className={ className }
			>
				{ card }
			</button>
		);
	}

	return card;
}
