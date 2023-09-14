const REQUIRED_FIELD_SYMBOL = '*';

export default function TitleField( props ) {
	const { id, title, required } = props;
	return (
		<span className="itsec-rjsf-title-field" id={ id }>
			{ title }
			{ required && (
				<span className="required">{ REQUIRED_FIELD_SYMBOL }</span>
			) }
		</span>
	);
}
