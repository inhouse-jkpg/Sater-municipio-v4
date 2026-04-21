import React from 'react';
import Modal from '../modal';
import FileUploadField from '../file-upload-field';
import { __ } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';
import Button from '../button';
import Notice from '../notices/notice';
import FileUtil from '../../utils/file-util';

export class ImportRedirectsModal extends React.Component {
	static defaultProps = {
		inProgress: false,
		csvTypes: [],
		onClose: () => false,
		onImport: () => false,
	};

	constructor(props) {
		super(props);

		this.state = {
			file: null,
			sizeError: false,
			typeError: false,
		};
	}

	handleFileChange(file) {
		this.setState({
			file,
			sizeError: file?.size && file?.size > 1000000,
			typeError: file && !this.isFileTypeValid(file),
		});
	}

	isFileTypeValid(file) {
		const fileName = file?.name + '';

		return (
			fileName.endsWith('.csv') &&
			this.props.csvTypes.includes(file?.type)
		);
	}

	render() {
		const { inProgress, onClose, onImport } = this.props;
		const { file, sizeError, typeError } = this.state;
		const description = createInterpolateElement(
			__(
				'Choose a CSV file (.csv) with a max-size of 1MB containing your redirects, formatted as follows: <strong>old-url</strong>, <strong>new-url</strong>, redirect-type <strong>301 or 302</strong>, is-regex <strong>0 or 1</strong>, <strong>label</strong> (optional). <a>Download CSV template</a>.',
				'wds'
			),
			{
				strong: <strong />,
				a: <a href="#" onClick={(e) => this.downloadTemplateFile(e)} />,
			}
		);
		const submissionDisabled = !file || sizeError || typeError;

		return (
			<Modal
				id="wds-import-redirects-modal"
				title={__('Import Redirects', 'wds')}
				description={__(
					'Import redirects from a CSV file below.',
					'wds'
				)}
				small={true}
				focusAfterOpen="wds-import-redirects-modal-close-button"
				focusAfterClose="wds-import-redirects-button"
				onClose={onClose}
				disableCloseButton={inProgress}
				footer={
					<React.Fragment>
						<div className="sui-flex-child-right">
							<Button
								text={__('Cancel', 'wds')}
								ghost={true}
								onClick={onClose}
								disabled={inProgress}
							/>
						</div>

						<div className="sui-actions-right">
							<Button
								text={__('Import', 'wds')}
								color="blue"
								onClick={() => onImport(file)}
								icon="sui-icon-upload-cloud"
								disabled={submissionDisabled}
								loading={inProgress}
							/>
						</div>
					</React.Fragment>
				}
			>
				<FileUploadField
					id="wds-import-redirects-file"
					acceptType=".csv"
					label={__('Upload CSV file', 'wds')}
					onChange={(fl) => this.handleFileChange(fl)}
				/>

				{sizeError && (
					<Notice
						type="error"
						message={__(
							'Oops! The uploaded file is too large, please select a file not larger than 1MB.',
							'wds'
						)}
					/>
				)}

				{typeError && (
					<Notice
						type="error"
						message={__(
							'Whoops! Only .csv filetypes are allowed.',
							'wds'
						)}
					/>
				)}

				<p className="sui-description" style={{ textAlign: 'left' }}>
					<small>{description}</small>
				</p>
			</Modal>
		);
	}

	downloadTemplateFile(e) {
		e.preventDefault();
		e.stopPropagation();

		FileUtil.triggerFileDownload(
			'"/old-url-1","/new-url-1",302,1,"Temporary Regex Redirect"\n"/old-url-2","/new-url-2",301,0,"Permanent Plain Redirect"',
			'smartcrawl-redirects-csv-template.csv'
		);
	}
}
