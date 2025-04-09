/**
 * External dependencies
 */
import classnames from 'classnames';
import { castArray } from 'lodash';
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Button, Dashicon } from '@wordpress/components';

/**
 * Internal dependencies
 */
import RecommendedIcon from '../recommended-icon';

const types = {
	error: { primary: '#551515', secondary: '#F7ABAB', icon: 'warning' },
	info: { primary: '#005169', secondary: '#ecfaff', icon: 'info' },
	warning: { primary: '#a9582e', secondary: '#fdddcd', icon: 'flag' },
	success: { primary: '#237739', secondary: '#ddf1e2', icon: 'yes-alt' },
};

const StyledMessageList = styled.div`
	display: flex;
	padding: .75rem .5rem;
	border-radius: 4px;
	margin-bottom: ${ ( { noMargins } ) => ! noMargins && '1rem' };
	background: ${ ( { type } ) => types[ type ].secondary };
	border: ${ ( { type, hasBorder } ) => hasBorder && `1px solid ${ types[ type ].primary }` };
`;

const StyledTitle = styled.h3`
	font-size: 1.25rem;
	margin: 0 0 0.5rem 0;
	color: ${ ( { type } ) => types[ type ].primary };
`;

const StyledIcon = styled( Dashicon, { shouldForwardProp: ( prop ) => prop !== 'type' } )`
	color: ${ ( { type } ) => types[ type ].primary };
	margin-right: 0.5rem;
`;

const StyledRecommendedIcon = styled( RecommendedIcon )`
	margin-right: 0.5rem;
`;

const StyledList = styled.ul`
	margin: 0;
`;

const StyledListItem = styled.li`
	margin: 0 0 0.25rem 0;
	color: ${ ( { type } ) => types[ type ].primary };

	&:last-child {
		margin-bottom: 0;
	}
`;

const StyledDismissButton = styled( Button, { shouldForwardProp: ( prop ) => prop !== 'type' } )`
	margin-left: auto;
	padding: 0 !important;
	min-width: 0 !important;
	min-height: 0 !important;
	height: min-content !important;

	&:hover {
		color: ${ ( { theme } ) => theme.colors.primary.base };
	}

	.dashicon {
		color: ${ ( { type } ) => types[ type ].primary };
		margin-left: 0;
		margin-right: 0;
	  
		&:hover {
			color: ${ ( { theme } ) => theme.colors.primary.base };
		}
	}
`;

export default function MessageList( {
	type = 'info',
	title,
	messages = [],
	className,
	onDismiss,
	hasBorder,
	recommended,
	noMargins,
} ) {
	messages = castArray( messages );

	if ( ! messages.length ) {
		return null;
	}

	return (
		<StyledMessageList
			type={ type }
			hasBorder={ hasBorder }
			recommended={ recommended }
			noMargins={ noMargins }
			className={ classnames(
				'itsec-message-list',
				`itsec-message-list--type-${ type }`,
				className,
			) }
		>
			{ recommended ? <StyledRecommendedIcon /> : <StyledIcon icon={ types[ type ].icon } type={ type } /> }
			<div>
				{ title && <StyledTitle>{ title }</StyledTitle> }
				<StyledList>
					{ messages.map( ( message, i ) => (
						<StyledListItem key={ i } type={ type }>
							{ message }
						</StyledListItem>
					) ) }
				</StyledList>
			</div>
			{ onDismiss && <StyledDismissButton icon="dismiss" type={ type } onClick={ onDismiss } /> }
		</StyledMessageList>
	);
}
