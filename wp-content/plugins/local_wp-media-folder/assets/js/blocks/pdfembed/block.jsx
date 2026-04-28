(function (wpI18n, wpBlocks, wpElement, wpEditor, wpBlockEditor, wpComponents) {
    const {__} = wpI18n;
    const {Component, Fragment} = wpElement;
    const {registerBlockType} = wpBlocks;
    const {InspectorControls, MediaUpload, BlockControls} = wpBlockEditor;
    const {PanelBody, SelectControl, ToolbarGroup, TextControl, Button, IconButton, Placeholder} = wpComponents;
    const $ = jQuery;

    class wpmfPdfEmbed extends Component {
        constructor() {
            super(...arguments);
        }

        componentDidMount() {
            const {attributes, clientId} = this.props;
            const {id, embed, target, width, height} = attributes;
            this.doPdfEmbed(id, embed, target, width, height, clientId);
        }

        componentDidUpdate(prevProps) {
            const {attributes, clientId} = this.props;
            const {id, embed, target, width, height} = attributes;
            if (attributes.embed != prevProps.attributes.embed || attributes.id != prevProps.attributes.id || attributes.width != prevProps.attributes.width || attributes.height != prevProps.attributes.height) {
                this.doPdfEmbed(id, embed, target, width, height, clientId);
            }
        }

        doPdfEmbed(id, embed, target, width, height, clientId) {
            let $container = $(`#block-${clientId} .wpmf_block_pdf_wrap`);
            fetch(wpmf_pdf_blocks.vars.ajaxurl + `?action=wpmf_load_pdf_embed&id=${id}&embed=${embed}&target=${target}&width=${width}&height=${height}&wpmf_nonce=${wpmf_pdf_blocks.vars.wpmf_nonce}`)
                .then(res => res.json())
                .then(
                    (result) => {
                        if (result.status) {
                            $container.html(result.html);
                            $container.find('.wpmf-pdfemb-viewer').pdfEmbedder();
                        }
                    },
                    // errors
                    (error) => {
                    }
                );
        }

        render() {
            const {attributes, setAttributes, className} = this.props;
            const {id, embed, target, width, height} = attributes;
            const controls = (
                <BlockControls>
                    {id !== 0 && (
                        <ToolbarGroup>
                            <MediaUpload
                                onSelect={(file) => setAttributes({id: parseInt(file.id)})}
                                accept="application/pdf"
                                allowedTypes={'application/pdf'}
                                render={({open}) => (
                                    <IconButton
                                        className="components-toolbar__control wpmf-pdf-button"
                                        label={__('Edit', 'wpmf')}
                                        icon="edit"
                                        onClick={open}
                                    />
                                )}
                            />
                        </ToolbarGroup>
                    )}
                </BlockControls>
            );

            let pdf_shortcode = '[wpmfpdf';
            pdf_shortcode += ' id="' + id + '"';
            pdf_shortcode += ' embed="' + embed + '"';
            pdf_shortcode += ' target="' + target + '"';
            if (width !== '') {
                pdf_shortcode += ' width="' + width + '"';
            }
            if (height !== '') {
                pdf_shortcode += ' height="' + height + '"';
            }
            pdf_shortcode += ']';

            if (id == 0) {
                return (
                    <Placeholder
                        icon="pdf"
                        label={__('WP Media Folder PDF Embed', 'wpmf')}
                        instructions={__('Select a PDF file from your media library.', 'wpmf')}
                        className={className}
                    >
                        <MediaUpload
                            onSelect={(file) => setAttributes({id: parseInt(file.id)})}
                            accept="application/pdf"
                            allowedTypes={'application/pdf'}
                            render={({open}) => (
                                <Button
                                    islarge="true"
                                    className="is-tertiary editor-media-placeholder__button wpmfLibrary"
                                    onClick={open}
                                >
                                    {__('Add PDF', 'wpmf')}
                                </Button>
                            )}
                        />
                    </Placeholder>

                );
            }

            return (
                <Fragment>
                    <div className="wp-block-shortcode">
                        {
                            (id !== 0) && <div className="wpmf-pdf-block">
                                <InspectorControls>
                                    <PanelBody title={__('PDF Settings', 'wpmf')}>
                                        <SelectControl
                                            label={__('Embed', 'wpmf')}
                                            value={embed}
                                            options={[
                                                {label: __('On', 'wpmf'), value: 1},
                                                {label: __('Off', 'wpmf'), value: 0}
                                            ]}
                                            onChange={(value) => setAttributes({embed: parseInt(value)})}
                                        />

                                        <SelectControl
                                            label={__('Target', 'wpmf')}
                                            value={target}
                                            options={[
                                                {label: __('Same Window', 'wpmf'), value: ''},
                                                {label: __('New Window', 'wpmf'), value: '_blank'}
                                            ]}
                                            onChange={(value) => setAttributes({target: value})}
                                        />
                                        <TextControl
                                            label={__('Width', 'wpmf')}
                                            value={ width }
                                            onChange={ ( value ) => setAttributes({width: value})}
                                        />
                                        <TextControl
                                            className="wpmf_pdf_embed_shortcode_input"
                                            label={__('Height', 'wpmf')}
                                            value={ height }
                                            onChange={ ( value ) => setAttributes({height: value})}
                                        />
                                    </PanelBody>
                                </InspectorControls>
                                <TextControl
                                    value={pdf_shortcode}
                                    className="wpmf_pdf_value"
                                    autoComplete="off"
                                    readOnly
                                />
                                {
                                    (id !== 0) && <div className="wpmf_block_pdf_wrap"></div>
                                }
                            </div>
                        }
                    </div>
                    {controls}
                </Fragment>
            );
        }
    }

    registerBlockType(
        'wpmf/pdfembed', {
            title: wpmf_pdf_blocks.l18n.block_pdf_title,
            icon: 'media-code',
            category: 'wp-media-folder',
            attributes: {
                id: {
                    type: 'number',
                    default: 0
                },
                embed: {
                    type: 'number',
                    default: 1,
                },
                target: {
                    type: 'string',
                    default: '',
                },
                width: {
                    type: 'string',
                    default: ''
                },
                height: {
                    type: 'string',
                    default: ''
                }
            },
            edit: wpmfPdfEmbed,
            save: ({attributes}) => {
                const {id, embed, target, width, height} = attributes;
                let pdf_shortcode = '[wpmfpdf';
                pdf_shortcode += ' id="' + id + '"';
                pdf_shortcode += ' embed="' + embed + '"';
                pdf_shortcode += ' target="' + target + '"';
                if (width !== '') {
                    pdf_shortcode += ' width="' + width + '"';
                }
                if (height !== '') {
                    pdf_shortcode += ' height="' + height + '"';
                }
                pdf_shortcode += ']';
                return pdf_shortcode;
            }
        }
    );
})(wp.i18n, wp.blocks, wp.element, wp.editor, wp.blockEditor, wp.components);