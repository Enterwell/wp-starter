/**
 * External dependencies
 */
import { isString } from 'lodash';
import memize from 'memize';
import moment from 'moment';
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import {
	DatePicker,
	Dropdown,
	Modal,
	SelectControl,
} from '@wordpress/components';
import { calendar } from '@wordpress/icons';
import { __, sprintf } from '@wordpress/i18n';
import { dateI18n, format, getDate, isInTheFuture } from '@wordpress/date';

/**
 * SolidWP dependencies
 */
import { Button } from '@ithemes/ui';

const StyledActions = styled.div`
	display: flex;
	gap: 0.5rem;
`;

const StyledApply = styled( Button )`
	margin-left: auto;
`;

function getPeriodLabel( period ) {
	if ( ! period ) {
		return 'No dates chosen';
	}

	const now = new window.Date();
	let start, end;

	switch ( period ) {
		case '24-hours':
			return __( '24 Hours', 'better-wp-security' );
		case '30-days':
			start = dateI18n( 'M j', now.setDate( now.getDate() - 30 ) );
			end = dateI18n( 'M j, Y' );
			break;
		case 'week':
			start = dateI18n( 'M j', now.setDate( now.getDate() - 7 ) );
			end = dateI18n( 'M j, Y' );
			break;
		default:
			start = dateI18n( 'M j', period.start );
			end = dateI18n( 'M j, Y', period.end );
			break;
	}

	return sprintf(
		/* translators: 1. The start time, 2. The end time. */
		__( '%1$s - %2$s', 'better-wp-security' ),
		start,
		end
	);
}

const getDateOptions = memize( () => {
	return [
		{ value: '24-hours', label: __( '24 Hours', 'better-wp-security' ) },
		{ value: 'week', label: __( '7 Days', 'better-wp-security' ) },
		{ value: '30-days', label: __( '30 Days', 'better-wp-security' ) },
		{ value: 'custom', label: __( 'Custom', 'better-wp-security' ) },
	];
} );

export default function DateRangeControl( {
	value,
	onChange,
} ) {
	const [ isOpen, setIsOpen ] = useState( false );
	const [ start, setStart ] = useState( undefined );
	const [ end, setEnd ] = useState( undefined );
	let [ periodOption, setPeriodOption ] = useState( undefined );
	const period = value;
	const periodLabel = getPeriodLabel( period );
	periodOption = periodOption || ( isString( period ) ? period : 'custom' );

	const onApply = ( e ) => {
		e.preventDefault();

		let newPeriod;
		if ( 'custom' === periodOption ) {
			const momentStart = moment( start )
				.set( { hour: 0, minute: 0, second: 0 } );
			const momentEnd = moment( end )
				.set( { hour: 23, minute: 59, second: 59 } );
			newPeriod = {
				start: format( 'Y-m-d\\TH:i:s', momentStart ),
				end: format( 'Y-m-d\\TH:i:s', momentEnd ),
			};
		} else {
			newPeriod = periodOption;
		}
		onChange( newPeriod );
		setIsOpen( false );
	};

	return (
		<div>
			<Button
				onClick={ () => setIsOpen( ! isOpen ) }
				title={ periodLabel }
				aria-expanded={ isOpen }
				aria-label={ sprintf(
					/* translators: The current search period or interval EG, 24 hours. */
					__( '%s (click to edit)', 'better-wp-security' ),
					periodLabel
				) }
				variant="tertiary"
				text={ periodLabel }
				icon={ calendar }
				iconPosition="right"
			/>
			{ isOpen && (
				<Modal
					title={ __( 'Change Date Period', 'better-wp-security' ) }
					onRequestClose={ () => setIsOpen( false ) }
				>
					<SelectControl
						options={ getDateOptions() }
						value={ periodOption }
						onChange={ ( newPeriod ) =>
							setPeriodOption( newPeriod ) }
					/>
					<StyledActions>
						{ periodOption === 'custom' && (
							<>
								<Dropdown
									renderToggle={ ( { isOpen: isCalendarOpen, onToggle } ) => (
										<Button
											variant="secondary"
											onClick={ onToggle }
											aria-expanded={ isCalendarOpen }
											aria-label={ sprintf(
												/* translators: The selected start date. */
												__( 'From: %s (click to edit', 'better-wp-security' ), dateI18n( 'M j', start )
											) }
											text={ sprintf(
												/* translators: The selected start date */
												__( 'From %s', 'better-wp-security' ),
												dateI18n( 'M j', start )
											) }
										/>
									) }
									renderContent={ () =>
										<DatePicker
											currentDate={ start }
											onChange={ setStart }
											isInvalidDate={ ( dateToCheck ) => {
												if ( isInTheFuture( dateToCheck ) ) {
													return true;
												}
												const earliestDate = new window.Date();
												earliestDate.setDate( earliestDate.getDate() - 60 );

												return dateToCheck < earliestDate;
											} }
										/>
									}
								/>

								<Dropdown
									renderToggle={ ( { isOpen: isCalendarOpen, onToggle } ) => (
										<Button
											variant="secondary"
											onClick={ onToggle }
											aria-expanded={ isCalendarOpen }
											aria-label={ sprintf(
												/* translators: The selected end date. */
												__( 'To: %s (click to edit', 'better-wp-security' ),
												dateI18n( 'M j', end )
											) }
											text={ sprintf(
												/* translators: The selected end date. */
												__( 'To: %s (click to edit', 'better-wp-security' ),
												dateI18n( 'M j', end )
											) }
										/>
									) }
									renderContent={ () =>
										<DatePicker
											currentDate={ end }
											onChange={ setEnd }
											isInvalidDate={ ( dateToCheck ) => {
												if ( isInTheFuture( dateToCheck ) ) {
													return true;
												}
												const startDate = getDate( start );
												return startDate > dateToCheck;
											} }
										/>
									}
								/>
							</>
						) }
						<StyledApply
							variant="primary"
							onClick={ onApply }
							text={ __( 'Apply', 'better-wp-security' ) }
						/>
					</StyledActions>
				</Modal>
			) }
		</div>
	);
}
