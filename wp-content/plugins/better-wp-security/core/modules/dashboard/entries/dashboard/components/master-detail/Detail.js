/**
 * WordPress dependencies
 */
import { compose, pure } from '@wordpress/compose';

function Detail( {
	master,
	getId,
	parentInstanceId,
	isSelected,
	DetailRender,
} ) {
	return (
		<section
			key={ getId( master ) }
			role="tabpanel"
			className="itsec-component-master-detail__detail-container"
			id={ `itsec-component-master-detail-${ parentInstanceId }__detail--${ getId(
				master
			) }` }
			style={ isSelected ? {} : { display: 'none' } }
		>
			<DetailRender master={ master } isVisible={ isSelected } />
		</section>
	);
}

export default compose( [ pure ] )( Detail );
