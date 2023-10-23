/**
 * Solid dependencies
 */
import { MessageList } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { ErrorList } from '../';

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
			{ result?.success && (
				<MessageList
					messages={ result.success }
					type="success"
					hasBorder={ hasBorder }
				/>
			) }
			{ result?.warning && (
				<MessageList
					messages={ result.warning }
					type="warning"
					hasBorder={ hasBorder }
				/>
			) }
			{ result?.info && (
				<MessageList
					messages={ result.info }
					type="info"
					hasBorder={ hasBorder }
				/>
			) }
		</>
	);
}
