(function (wpI18n, wpBlocks, wpElement, wpEditor, wpBlockEditor, wpComponents) {
    const {__} = wpI18n;
    const {Component, Fragment} = wpElement;
    const {registerBlockType} = wpBlocks;
    const {InspectorControls, MediaUpload, BlockControls} = wpBlockEditor;
    const {mediaUpload} = wpEditor;
    const {PanelBody, SelectControl, ToolbarGroup, Button, IconButton, FormFileUpload, Placeholder} = wpComponents;

    class wpmfFileDesign extends Component {
        constructor() {
            super(...arguments);
            this.addFiles = this.addFiles.bind(this);
            this.uploadFromFiles = this.uploadFromFiles.bind(this);
        }

        /**
         * Upload files
         */
        uploadFromFiles(event) {
            this.addFiles(event.target.files);
        }

        /**
         * Add files
         */
        addFiles(files) {
            const {attributes, setAttributes} = this.props;
            mediaUpload({
                filesList: files,
                onFileChange: (file) => {
                    if (file.length && file[0] !== null && typeof file[0].id !== "undefined") {
                        let f = {};
                        f.title = file[0].title;
                        f.mime = file[0].mime_type;
                        f.filesizeInBytes = file[0].media_details.filesize;
                        f.url = file[0].url;
                        setAttributes({
                            id: file[0].id,
                            file: f
                        });
                    }
                }
            });
        }

        render() {
            const {attributes, setAttributes, className} = this.props;
            const {id, file, target, cover} = attributes;
            const controls = (
                <BlockControls>
                    {id !== 0 && (
                        <ToolbarGroup>
                            <MediaUpload
                                onSelect={(file) => setAttributes({id: file.id, file: file})}
                                render={({open}) => (
                                    <IconButton
                                        className="components-toolbar__control"
                                        label={__('Edit File', 'wpmf')}
                                        icon="edit"
                                        onClick={open}
                                    />
                                )}
                            />
                        </ToolbarGroup>
                    )}
                </BlockControls>
            );

            let mime = '';
            let size = 0;
            if (id !== 0) {
                let mimetype = file.url.split('.');
                let index = mimetype.length - 1;
                if (mimetype[index].length > 10) {
                    mimetype = file.mime.split('/');
                    index = mimetype.length - 1;
                }
                if (typeof mimetype !== "undefined" && typeof mimetype[index] !== "undefined") {
                    mime = mimetype[index].toUpperCase()
                }
                if (file.filesizeInBytes < 1024 * 1024) {
                    size = file.filesizeInBytes / 1024;
                    size = size.toFixed(1);
                    size += ' kB'
                } else if (file.filesizeInBytes > 1024 * 1024) {
                    size = file.filesizeInBytes / (1024 * 1024);
                    size = size.toFixed(1);
                    size += ' MB'
                }
            }

            if (typeof cover === "undefined" && id == 0) {
                return (
                    <Placeholder
                        icon="media-archive"
                        label={__('WPMF Media Download', 'wpmf')}
                        instructions={wpmf.l18n.media_download_desc}
                        className={className}
                    >
                        <FormFileUpload
                            islarge="true"
                            className="is-primary editor-media-placeholder__button wpmf_btn_upload_img"
                            onChange={this.uploadFromFiles}
                            accept="*"
                        >
                            {wpmf.l18n.upload}
                        </FormFileUpload>
                        <MediaUpload
                            onSelect={(file) => setAttributes({id: file.id, file: file})}
                            accept="*"
                            allowedTypes="*"
                            render={({open}) => (
                                <Button
                                    islarge="true"
                                    className="is-tertiary editor-media-placeholder__button wpmfLibrary"
                                    onClick={open}
                                >
                                    {wpmf.l18n.media_folder}
                                </Button>
                            )}
                        />
                    </Placeholder>

                );
            }

            return (
                <Fragment>
                    {
                        typeof cover !== "undefined" && <div className="wpmf-cover"><img src={cover} /></div>
                    }

                    {controls}
                        {
                            (typeof cover === "undefined" && id !== 0) && <div className="wp-block-shortcode">
                                <div className="wpmf-file-design-block">
                                    <InspectorControls>
                                        <PanelBody title={__('File Design Settings', 'wpmf')}>
                                            <SelectControl
                                                label={__('Target', 'wpmf')}
                                                value={target}
                                                options={[
                                                    {label: __('Same Window', 'wpmf'), value: ''},
                                                    {label: __('New Window', 'wpmf'), value: '_blank'}
                                                ]}
                                                onChange={(value) => setAttributes({target: value})}
                                            />
                                        </PanelBody>
                                    </InspectorControls>
                                    <div data-id={id}>
                                        <a
                                            className="wpmf-defile"
                                            href={file.url}
                                            download
                                            rel="noopener noreferrer"
                                            target={target} data-id={id}>
                                            <div className="wpmf-defile-title"><b>{file.title}</b></div>
                                            <span className="wpmf-single-infos">
                                        <b>{__('Size: ', 'wpmf')} </b>{size}
                                                <b>{__(' Format: ', 'wpmf')} </b></span>{mime}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        }
                </Fragment>
            );
        }
    }

    const fileDesignAttrs = {
        id: {
            type: 'number',
            default: 0
        },
        file: {
            type: 'object',
            default: {},
        },
        target: {
            type: 'string',
            default: '',
        },
        cover: {
            type: 'string',
            source: 'attribute',
            selector: 'img',
            attribute: 'src',
        }
    };

    registerBlockType(
        'wpmf/filedesign', {
            title: __('WPMF Media Download', 'wpmf'),
            icon: 'media-archive',
            category: 'wp-media-folder',
            example: {
                attributes: {
                    cover: wpmf_filedesign_blocks.vars.block_cover
                }
            },
            attributes: fileDesignAttrs,
            edit: wpmfFileDesign,
            save: ({attributes}) => {
                const {id, file, target} = attributes;

                let mime = '';
                let size = 0;
                if (id !== 0) {
                    let mimetype = file.url.split('.');
                    let index = mimetype.length - 1;
                    if (mimetype[index].length > 10) {
                        mimetype = file.mime.split('/');
                        index = mimetype.length - 1;
                    }
                    if (typeof mimetype !== "undefined" && typeof mimetype[index] !== "undefined") {
                        mime = mimetype[index].toUpperCase()
                    }
                    if (file.filesizeInBytes < 1024 * 1024) {
                        size = file.filesizeInBytes / 1024;
                        size = size.toFixed(1);
                        size += ' kB'
                    } else if (file.filesizeInBytes > 1024 * 1024) {
                        size = file.filesizeInBytes / (1024 * 1024);
                        size = size.toFixed(1);
                        size += ' MB'
                    }
                }

                return <div data-id={id}>
                    <a
                        className="wpmf-defile"
                        href={file.url}
                        download
                        rel="noopener noreferrer"
                        target={target} data-id={id}>
                        <div className="wpmf-defile-title"><b>{file.title}</b></div>
                        <span className="wpmf-single-infos">
                                    <b>{__('Size: ', 'wpmf')} </b>{size}
                            <b>{__(' Format: ', 'wpmf')} </b></span>{mime}
                    </a>
                </div>;
            },
            deprecated: [
                {
                    attributes: fileDesignAttrs,
                    save: ({attributes}) => {
                        const {id, file, target} = attributes;

                        let mime = '';
                        let size = 0;
                        if (id !== 0) {
                            let mimetype = file.mime.split('/');
                            if (typeof mimetype !== "undefined" && typeof mimetype[1] !== "undefined") {
                                mime = mimetype[1].toUpperCase()
                            }
                            if (file.filesizeInBytes < 1024 * 1024) {
                                size = file.filesizeInBytes / 1024;
                                size = size.toFixed(1);
                                size += ' kB'
                            } else if (file.filesizeInBytes > 1024 * 1024) {
                                size = file.filesizeInBytes / (1024 * 1024);
                                size = size.toFixed(1);
                                size += ' MB'
                            }
                        }

                        return <div data-id={id}>
                            <a
                                className="wpmf-defile"
                                href={file.url}
                                download
                                target={target} data-id={id}>
                                <div className="wpmf-defile-title"><b>{file.title}</b></div>
                                <span className="wpmf-single-infos">
                                    <b>{__('Size: ', 'wpmf')} </b>{size}
                                    <b>{__(' Format: ', 'wpmf')} </b></span>{mime}
                            </a>
                        </div>;
                    },
                }
            ]
        }
    );
})(wp.i18n, wp.blocks, wp.element, wp.editor, wp.blockEditor, wp.components);