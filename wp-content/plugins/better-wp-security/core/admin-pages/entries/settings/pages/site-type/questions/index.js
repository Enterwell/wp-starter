/**
 * External dependencies
 */
import { useParams } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { Disabled } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useEffect, useLayoutEffect, memo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { HelpList } from '@ithemes/security-components';
import { SchemaQuestion } from './question';
import useQuestions from './questions';
import { useNavigation } from '../../../page-registration';
import { HelpFill, PageHeader } from '../../../components';
import { STORE_NAME } from '../../../stores/onboard';
import './style.scss';

export default function Questions() {
	useQuestions();
	const { goNext } = useNavigation();
	const { siteType } = useParams();

	const { isAnswering, selectedSiteTypeId, questionId } = useSelect(
		( select ) => ( {
			isAnswering: select( STORE_NAME ).isAnswering(),
			selectedSiteTypeId: select( STORE_NAME ).getSelectedSiteTypeId(),
			questionId: select( STORE_NAME ).getNextQuestion()?.id,
		} )
	);
	const { selectSiteType, applyAnswerResponse } = useDispatch( STORE_NAME );
	const next = useNextQuestion();

	useLayoutEffect( () => {
		if ( selectedSiteTypeId !== siteType ) {
			selectSiteType( siteType );
		}
	}, [ selectedSiteTypeId, siteType ] );

	useEffect( () => {
		if ( next === null ) {
			applyAnswerResponse();
			goNext();
		}
	}, [ next ] );

	if ( isAnswering ) {
		return <Disabled>{ next }</Disabled>;
	}

	if ( next ) {
		return (
			<>
				{ next }
				<HelpFill>
					<PageHeader
						title={ __( 'Site Type', 'better-wp-security' ) }
						breadcrumbs={ false }
					/>
					<HelpList
						topic={ `site-type-${ questionId }` }
						fallback="site-type"
					/>
				</HelpFill>
			</>
		);
	}

	return null;
}

function useNextQuestion() {
	const { answerQuestion, repeatQuestion } = useDispatch( STORE_NAME );
	const question = useSelect( ( select ) =>
		select( STORE_NAME ).getNextQuestion()
	);
	const id = question?.id;
	const component = useSelect(
		( select ) => select( STORE_NAME ).getQuestionComponent( id ),
		[ id ]
	);

	if ( ! question ) {
		return question;
	}

	const Component = memo( component || SchemaQuestion );

	return (
		<Component
			question={ question }
			onAnswer={ answerQuestion }
			goBack={ repeatQuestion }
		/>
	);
}
