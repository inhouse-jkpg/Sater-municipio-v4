import React from 'react';
import { __ } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';
import fieldWithValidation from '../field-with-validation';
import TextInputField from '../form-fields/text-input-field';
import { isValuePlainText } from '../../utils/validators';
import SideTabsField from '../side-tabs-field';

export default class RedirectConfigAdvanced extends React.Component {
	static defaultProps = {
		title: '',
		isRegex: false,
		onTitleChange: () => false,
		onRegExChange: () => false,
	};

	constructor(props) {
		super(props);

		this.titleField = fieldWithValidation(TextInputField, [
			isValuePlainText,
		]);
	}

	render() {
		const { title, isRegex, inProgress, onTitleChange, onRegExChange } =
			this.props;

		const TitleField = this.titleField;

		return (
			<>
				<TitleField
					id="wds-title-field"
					label={__('Label (Optional)', 'wds')}
					description={__(
						'Use labels to differentiate long or similar URLs.',
						'wds'
					)}
					value={title}
					placeholder={__('E.g. Press release', 'wds')}
					onChange={(tl, isValid) => onTitleChange(tl, isValid)}
					disabled={inProgress}
				/>

				<SideTabsField
					label={__('Regular Expression', 'wds')}
					description={createInterpolateElement(
						__(
							'Choose whether the strings entered into the Redirect From and Redirect To fields above should be treated as plain text URLs or regular expressions (Regex). Note that only valid regular expressions are allowed. <a>Learn more</a> about Regex.',
							'wds'
						),
						{
							a: (
								<a
									target="_blank"
									href="https://wpmudev.com/docs/wpmu-dev-plugins/smartcrawl/#about-regex-redirects"
									rel="noreferrer"
								/>
							),
						}
					)}
					tabs={{
						0: __('Plain Text', 'wds'),
						1: __('Regex', 'wds'),
					}}
					value={isRegex ? '1' : '0'}
					onChange={(checked) =>
						onRegExChange('regex', checked === '1')
					}
				/>
			</>
		);
	}
}
