export function viewDashboard( dashboardId ) {
	return {
		type: NAVIGATE,
		page: 'view-dashboard',
		attr: {
			id: dashboardId,
		},
	};
}

export function viewCreateDashboard() {
	return {
		type: NAVIGATE,
		page: 'create-dashboard',
	};
}

export function viewHelp() {
	return {
		type: NAVIGATE,
		page: 'help',
	};
}

export function viewPrevious() {
	return {
		type: NAVIGATE_BACK,
	};
}

export function openEditCards() {
	return {
		type: OPEN_EDIT_CARDS,
	};
}

export function closeEditCards() {
	return {
		type: CLOSE_EDIT_CARDS,
	};
}

export function receiveSuggestedShareUsers( users ) {
	return {
		type: RECEIVE_SUGGESTED_SHARE_USERS,
		users,
	};
}

export function receiveUser( user ) {
	return {
		type: RECEIVE_USER,
		user,
	};
}

export function receiveStaticStats( stats, query ) {
	return {
		type: RECEIVE_STATIC_STATS,
		stats,
		query,
	};
}

export function usingTouch( isUsing = true ) {
	return {
		type: USING_TOUCH,
		isUsing,
	};
}

export function registerCard( slug, settings ) {
	return {
		type: REGISTER_CARD,
		slug,
		settings,
	};
}

export const NAVIGATE = 'NAVIGATE';
export const NAVIGATE_BACK = 'NAVIGATE_BACK';
export const RECEIVE_SUGGESTED_SHARE_USERS = 'RECEIVE_SUGGESTED_SHARE_USERS';
export const RECEIVE_USER = 'RECEIVE_USER';
export const OPEN_EDIT_CARDS = 'OPEN_EDIT_CARDS';
export const CLOSE_EDIT_CARDS = 'CLOSE_EDIT_CARDS';
export const RECEIVE_STATIC_STATS = 'RECEIVE_STATIC_STATS';
export const USING_TOUCH = 'USING_TOUCH';
export const REGISTER_CARD = 'REGISTER_CARD';
