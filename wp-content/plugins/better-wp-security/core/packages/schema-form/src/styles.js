/**
 * External dependencies
 */
import styled from '@emotion/styled';
import { withTheme } from '@rjsf/core';

/**
 * Internal dependencies
 */
import Theme from '@ithemes/security-rjsf-theme';

const SchemaForm = withTheme( Theme );

export const StyledSchemaForm = styled( SchemaForm )`
	.itsec-rjsf-object-fieldset {
		display: grid;
		grid-template-columns: [label fields] minmax(0, 1fr);
		grid-template-rows: auto;
		grid-gap: .5rem 0;

		@media (min-width: ${ ( { theme: { breaks } } ) => breaks.small }px) {
			grid-template-columns: [label] 10rem [fields] minmax(0, 1fr);
		}
	}

	.itsec-rjsf-section-description {
		margin-top: 0;
		grid-column: fields;
	}

	> .field-object > .itsec-rjsf-object-fieldset {
		& > .itsec-rjsf-title-field,
		& > .itsec-rjsf-section-title {
			grid-column: label;
			font-size: 1rem;
			padding-right: 1rem;
			margin-top: 0;

			&:not(:first-child) {
				border-top: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
				margin-top: .5rem;
				padding-top: 1rem;

				@media (min-width: ${ ( { theme: { breaks } } ) => breaks.small }px) {
					& + * {
						border-top: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
						margin-top: .5rem;
						padding-top: 1rem;
					}
				}
			}
		}

		& > .form-group:not(.field-object) {
			grid-column: label / fields-end;
		}

		& > .itsec-rjsf-section-title ~ .form-group {
			grid-column: fields;
		}

		& > .itsec-rjsf-section-title:has(+ .itsec-highlighted-search-result) {
			border-left: 5px solid ${ ( { theme } ) => theme.colors.border.info };
			background: #f9f7fd;
			margin-bottom: 0;
			padding-top: 1rem !important;
		}

		& > .field-object {
			grid-column: label / fields-end;
		}

		& > .field-description {
			margin-top: 0;
		}
	}

	.itsec-rjsf-object-fieldset > * > .itsec-rjsf-object-fieldset {
		& > .itsec-rjsf-title-field {
			grid-column: label;
			font-size: 1rem;
			padding-right: 1rem;
		}

		& > .form-group {
			grid-column: fields;
		}

		& > .field-description {
			grid-column: fields;
			margin-top: 0;
		}
	}

	.components-base-control__label,
	.components-input-control__label,
	label,
	caption,
	legend {
		color: ${ ( { theme } ) => theme.colors.text.dark };
	}

	.itsec-highlighted-search-result {
		background: #f9f7fd;
		padding-bottom: 1rem;
		padding-top: 1rem !important;
		
		.components-base-control__label,
		.components-input-control__label,
		label,
		caption {
			border-bottom: 3px solid ${ ( { theme } ) => theme.colors.border.info };
			padding-bottom: 3px;
			margin-bottom: 6px;
		}

		.components-base-control__field {
			margin-bottom: 12px;
		}
	}

	.field-object:not(:first-child):not(:empty) {
		border-top: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
		margin-top: 1rem;
		padding-top: 1rem;
	}

	.field-object:empty {
		display: none;
	}

	.itsec-rjsf-title-field + .field-object:not(:first-child),
	.itsec-rjsf-section-description + .field-object:not(:first-child) {
		border-top: none;
		margin-top: 0;
		padding-top: 0;
	}
`;
