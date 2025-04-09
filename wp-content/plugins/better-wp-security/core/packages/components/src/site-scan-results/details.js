export default function Details( { id, isVisible, children } ) {
	return (
		<div
			className="itsec-site-scan__details"
			id={ id }
			style={ { display: isVisible ? 'block' : 'none' } }
		>
			<ul>{ children }</ul>
		</div>
	);
}
