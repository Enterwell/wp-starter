export function registerProvider( slug, title, priority, callback ) {
	return {
		type: REGISTER_PROVIDER,
		slug,
		title,
		priority,
		callback,
	};
}

export const REGISTER_PROVIDER = 'REGISTER_PROVIDER';
