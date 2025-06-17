import blockManifest from '../../manifest.json';
import {PanelBody, SelectControl} from '@wordpress/components';

/**
 * Container options partial
 * @returns {*}
 * @constructor
 * @param props
 */
export const ContainerOptions = (props) => {
    const {attributes, setAttributes} = props;
    const {align} = attributes;

    return (
        <PanelBody title="Container options" icon={blockManifest.icon || 'block-default'} initialOpen={true}>
            <SelectControl
                label="Max Width"
                value={align}
                options={[
                    {label: "Content", value: "none"},
                    {label: "Wide", value: "wide"},
                    {label: "Full", value: "full"}
                ]}
                onChange={(newAlign) => setAttributes({align: newAlign})}
            />
        </PanelBody>
    );
};
