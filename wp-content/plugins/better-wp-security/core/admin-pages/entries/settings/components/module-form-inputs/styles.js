/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * Internal dependencies
 */
import { ErrorList, PrimarySchemaFormInputs } from '@ithemes/security-ui';

export const StyledErrorList = styled( ErrorList )`
	margin: 0 1.5rem 1rem;
`;

export const StyledPrimarySchemaFormInputs = styled( PrimarySchemaFormInputs )`
	& .itsec-rjsf-object-fieldset > .form-group,
	& .itsec-rjsf-object-fieldset > .itsec-rjsf-section-title {
		padding: 0 1.5rem;
	}
`;
