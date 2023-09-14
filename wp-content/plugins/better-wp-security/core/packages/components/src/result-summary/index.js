/**
 * Internal dependencies
 */
import { ErrorList, MessageList } from '../';

export default function ResultSummary( {
	result,
	hasBorder,
	schemaError,
	errors,
} ) {
	return (
		<>
			<ErrorList
				apiError={ result?.error }
				schemaError={ schemaError }
				errors={ errors }
				hasBorder={ hasBorder }
			/>
			<MessageList
				messages={ result?.success }
				type="success"
				hasBorder={ hasBorder }
			/>
			<MessageList
				messages={ result?.warning }
				type="warning"
				hasBorder={ hasBorder }
			/>
			<MessageList
				messages={ result?.info }
				type="info"
				hasBorder={ hasBorder }
			/>
		</>
	);
}
