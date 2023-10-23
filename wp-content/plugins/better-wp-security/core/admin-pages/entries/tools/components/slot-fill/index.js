/**
 * WordPress dependencies
 */
import { createSlotFill } from '@wordpress/components';

export const { Slot: BeforeImportExportToolsSlot, Fill: BeforeImportExportToolsFill } = createSlotFill( 'BeforeImportExportTools' );
export const { Slot: AfterImportExportToolsSlot, Fill: AfterImportExportToolsFill } = createSlotFill( 'AfterImportExportTools' );

export const { Slot: ExportSlot, Fill: ExportFill } = createSlotFill( 'Export' );
