/**
 * External dependencies
 */
import { useParams } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { useEffect, useLayoutEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { SchemaQuestion } from './question';
import useQuestions from './questions';
import { useNavigation } from '../../../page-registration';
import { STORE_NAME } from '../../../stores/onboard';

export default function Questions() {
	useQuestions();
	const { goNext } = useNavigation();
	const { siteType } = useParams();

	const { selectedSiteTypeId } = useSelect(
		( select ) => ( {
			selectedSiteTypeId: select( STORE_NAME ).getSelectedSiteTypeId(),
		} ),
		[]
	);
	const { selectSiteType, applyAnswerResponse } = useDispatch( STORE_NAME );
	const next = useNextQuestion();

	useLayoutEffect( () => {
		if ( selectedSiteTypeId !== siteType ) {
			selectSiteType( siteType );
		}
	}, [ selectSiteType, selectedSiteTypeId, siteType ] );

	useEffect( () => {
		if ( next === null ) {
			applyAnswerResponse();
			goNext();
		}
	}, [ applyAnswerResponse, goNext, next ] );

	return next;
}

function useNextQuestion() {
	const { question, component, isAnswering } = useSelect(
		( select ) => {
			const _question = select( STORE_NAME ).getNextQuestion();

			return {
				question: _question,
				component: select( STORE_NAME ).getQuestionComponent( _question?.id ),
				isAnswering: select( STORE_NAME ).isAnswering(),
			};
		},
		[]
	);
	const { answerQuestion, repeatQuestion } = useDispatch( STORE_NAME );

	if ( ! question ) {
		return question;
	}

	const Component = component || SchemaQuestion;

	return (
		<Component
			question={ question }
			onAnswer={ answerQuestion }
			goBack={ repeatQuestion }
			isAnswering={ isAnswering }
		/>
	);
}
