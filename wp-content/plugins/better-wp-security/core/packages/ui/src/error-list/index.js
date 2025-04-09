/**
 * iThemes dependencies
 */
import { MessageList } from '@ithemes/ui';
import { transformApiErrorToList } from '@ithemes/security-utils';

export default function ErrorList( {
	errors,
	apiError,
	schemaError,
	title,
	className,
	hasBorder,
} ) {
	const all = [
		...( errors || [] ),
		...transformApiErrorToList( apiError ),
		...( schemaError || [] ).map( ( error ) => error.stack ),
	];

	if ( ! all.length ) {
		return null;
	}

	return (
		<MessageList
			messages={ all }
			heading={ title }
			className={ className }
			hasBorder={ hasBorder }
			type="danger"
		/>
	);
}
