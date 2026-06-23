import Main from "./main";
import { BlockSettings } from "./types";

declare const wp: any;

const fieldContainerSelector = '[data-js-openstreetmap-field]';
const fieldMapSelector = '[data-js-openstreetmap-map]';
const fieldTypeSelector = '[data-type="openstreetmap"]';

let checkedSettings: string[] = [];
type BlockAlign = {
    align: string|undefined;
    main: Main;
}
let initiatedBlocksWithField: Record<string, BlockAlign> = {};

const init = () => {
    if (wp && wp.data && wp.data.select('core/edit-post')) {
        initGutenberg();
    } else {
        initClassic();
    }
}

const initGutenberg = () => {
    const editor = wp.data.select('core/block-editor');

    document.addEventListener('click', () => {
        const selectedBlock = wp.data.select('core/block-editor').getSelectedBlock();
        if (selectedBlock && selectedBlock.clientId && initiatedBlocksWithField[selectedBlock.clientId]) {
            if (selectedBlock.attributes.align !== initiatedBlocksWithField[selectedBlock.clientId].align) {
                initiatedBlocksWithField[selectedBlock.clientId].align = selectedBlock.attributes.align;
                initiatedBlocksWithField[selectedBlock.clientId].main.invalidateSize();
            }
        }
    });

    wp.data.subscribe(() => {
        const blocks = editor.getBlocks();
        handleAddedBlocks(blocks);
    });
};

const handleAddedBlocks = (blocks: any) => {
    blocks.forEach((block: any) => {
        if (!block.clientId) {
            return;
        }

        const settings = lookForSettings(block.clientId);

        if (checkedSettings.includes(block.clientId)) {
            return;
        }

        if (!settings) {
            return;
        } 

        checkedSettings.push(block.clientId);
        const mapFieldContainer = settings.querySelector(fieldContainerSelector);
        const openstreetmapField = settings.querySelector(fieldTypeSelector);

        if (mapFieldContainer && openstreetmapField?.getAttribute('data-name')) {
            const mapInstance = createMapInstance(
                mapFieldContainer as HTMLElement, 
                { blockId: block.clientId, fieldName: openstreetmapField.getAttribute('data-name')! }
            );

            if (mapInstance) {
                initiatedBlocksWithField[block.clientId] = {
                    align: block.attributes.align,
                    main: mapInstance
                };
            }
        }
    });
}

const lookForSettings = (clientId: string): Element|null => {
    return document.querySelector(`[data-block-id="block_${clientId}"]`);
}

const initClassic = () => {
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll(fieldContainerSelector).forEach((container) => {
            createMapInstance(container as HTMLElement);
        });
    });
}

const createMapInstance = (container: HTMLElement, blockId: BlockSettings|null = null): null|Main => {
    const map = container.querySelector(fieldMapSelector);
    const id = map?.id;

    if (!id) {
        return null;
    }

    return new Main(id, container as HTMLElement, map as HTMLElement, blockId);
}

init();