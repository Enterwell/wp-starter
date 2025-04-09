export default function Detail( { status, children } ) {
	return (
		<li
			className={ `itsec-site-scan__detail itsec-site-scan__detail--${ status }` }
		>
			<span>{ children }</span>
		</li>
	);
}
