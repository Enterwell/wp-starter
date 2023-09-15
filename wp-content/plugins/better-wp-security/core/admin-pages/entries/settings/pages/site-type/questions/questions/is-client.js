/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { SelectableCard } from '../../../../components';
import Question from '../question';

export default function IsClient( { question, onAnswer } ) {
	return (
		<Question
			prompt={ question.prompt }
			description={ question.description }
		>
			<div className="itsec-site-type-questions-list">
				<SelectableCard
					onClick={ () => onAnswer( false ) }
					title={ __( 'Self', 'better-wp-security' ) }
					description={ __(
						'This is my own personal site.',
						'better-wp-security'
					) }
					direction="vertical"
					icon="admin-users"
					className="itsec-site-type-question"
				/>
				<SelectableCard
					onClick={ () => onAnswer( true ) }
					title={ __( 'Client', 'better-wp-security' ) }
					description={ __(
						"I'm making this for a client.",
						'better-wp-security'
					) }
					direction="vertical"
					icon="businessperson"
					className="itsec-site-type-question"
				/>
			</div>
		</Question>
	);
}
