import React from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';
import fieldWithValidation from '../field-with-validation';
import TextInputField from '../form-fields/text-input-field';
import {
	isAbsoluteUrlValid,
	isNonEmpty,
	isRegexStringValid,
	isRelativeUrlValid,
	isUrlValid,
	isValuePlainText,
	Validator,
} from '../../utils/validators';
import SelectField from '../form-fields/select-field';
import { getDefaultRedirectType, UrlField } from './redirect-commons';
import Notice from '../notices/notice';
import SideTabsField from '../side-tabs-field';
import ConfigValues from '../../es6/config-values';
import PostSearchField from '../input-fields/post-search-field';

const homeUrl = ConfigValues.get('home_url', 'redirects') || '';

const sourceField = fieldWithValidation(TextInputField, [
	isNonEmpty,
	isValuePlainText,
	new Validator(
		isUrlValid,
		__(
			'You need to use an absolute URL like https://domain.com/new-url or start with a slash /new-url.',
			'wds'
		)
	),
	new Validator((url) => {
		const isRelative = isRelativeUrlValid(url);
		const startsWithHome = url.startsWith(homeUrl);

		return isRelative || startsWithHome;
	}, __('You need to enter a URL belonging to the current site.', 'wds')),
]);

const sourceRegexField = fieldWithValidation(TextInputField, [
	isNonEmpty,
	new Validator(
		isRegexStringValid,
		__('This regex is invalid.', 'wds')
	),
]);

const UrlFieldEmptyAvailable = fieldWithValidation(TextInputField, [
	isValuePlainText,
	new Validator(
		(value) =>
			value === '' ||
			isRelativeUrlValid(value) ||
			isAbsoluteUrlValid(value),
		__(
			'You need to use an absolute URL like https://domain.com/new-url or start with a slash /new-url.',
			'wds'
		)
	),
]);

export default class RedirectConfigPlain extends React.Component {
	static defaultProps = {
		source: '',
		destination: '',
		type: getDefaultRedirectType(),
		options: false,
		isRegex: false,
		isRulesValid: false,
		inProgress: false,
		onSourceChange: () => false,
		onDestinationChange: () => false,
		onTypeChange: () => false,
	};

	constructor(props) {
		super(props);

		const { source, destination, options } = this.props;

		const stateObj = {
			loading: false,
			isSrcValid: isNonEmpty(source),
			isDstValid: false,
			dstType: 'plain',
			plainDst: '',
			postDst: '',
			pageDst: '',
		};

		const dstTypes = ['post', 'page'];

		for (let i = 0; i < dstTypes.length; i++) {
			if (options.includes(dstTypes[i])) {
				stateObj.dstType = dstTypes[i];
				stateObj[dstTypes[i] + 'Dst'] = destination;
				break;
			}
		}

		if (stateObj.dstType === 'plain') {
			stateObj.plainDst = destination;
		}

		this.state = stateObj;
	}

	handleDstTypeChange(dstType) {
		this.setState({ dstType }, () => {
			this.handleDstChange(
				dstType,
				this.state[dstType + 'Dst'],
				!!isNonEmpty(this.state[dstType + 'Dst'])
			);
		});
	}

	handleDstChange(type, val, isValid = true) {
		let url;

		switch (type) {
			case 'post':
				url = val;
				break;
			case 'page':
				url = val;
				break;
			default:
				url = val;
		}

		this.setState({ [type + 'Dst']: url }, () => {
			this.props.onDestinationChange(url, isValid, type);
		});
	}

	handleLoading(loading) {
		this.setState({ loading });
	}

	renderDestination() {
		const { isRulesValid, inProgress } = this.props;
		const { dstType, plainDst, postDst, pageDst, loading } = this.state;

		const DestField = isRulesValid ? UrlFieldEmptyAvailable : UrlField;

		return (
			<SideTabsField
				label={__('Redirect To', 'wds')}
				tabs={{
					plain: __('Url', 'wds'),
					post: __('Post', 'wds'),
					page: __('Page', 'wds'),
				}}
				value={dstType}
				onChange={(type) => this.handleDstTypeChange(type)}
			>
				{dstType === 'plain' && (
					<DestField
						label={__('URL', 'wds')}
						value={plainDst}
						placeholder={__('E.g. /cats-new', 'wds')}
						onChange={(val, isValid) =>
							this.handleDstChange('plain', val, isValid)
						}
						disabled={inProgress}
					/>
				)}

				{dstType === 'post' && (
					<PostSearchField
						label={__('Select a post', 'wds')}
						placeholder={__('Search post', 'wds')}
						selectedValue={postDst}
						field="url"
						prefix={
							<span className="sui-icon-magnifying-glass-search" />
						}
						onSelect={(val) => this.handleDstChange('post', val)}
						onLoading={(val) => this.handleLoading(val)}
						disabled={inProgress || loading}
					/>
				)}
				{dstType === 'page' && (
					<PostSearchField
						type="page"
						label={__('Select a page', 'wds')}
						placeholder={__('Search page', 'wds')}
						selectedValue={pageDst}
						field="url"
						prefix={
							<span className="sui-icon-magnifying-glass-search" />
						}
						onSelect={(val) => this.handleDstChange('page', val)}
						onLoading={(val) => this.handleLoading(val)}
						disabled={inProgress || loading}
					/>
				)}
			</SideTabsField>
		);
	}

	render() {
		const {
			source,
			type,
			isRegex,
			inProgress,
			onSourceChange,
			onTypeChange,
		} = this.props;

		const SourceField = isRegex ? sourceRegexField : sourceField;
		const maybeIsRegex = /[\[*^$\\{|]/g.test(source);
		const sourcePlaceholder = isRegex
			? sprintf(
					// translators: %s: Home url.
					__('E.g. %s/(.*)-cats', 'wds'),
					homeUrl
			  )
			: __('E.g. /cats', 'wds');

		return (
			<>
				<SourceField
					id="wds-source-field"
					label={__('Redirect From', 'wds')}
					description={
						isRegex
							? __(
									'Enter regex to match absolute URLs.',
									'wds'
							  )
							: ''
					}
					value={source}
					placeholder={sourcePlaceholder}
					onChange={(src, isValid) => onSourceChange(src, isValid)}
					disabled={inProgress}
					validateOnInit={isNonEmpty(source)}
				/>

				{maybeIsRegex && !isRegex && (
					<Notice
						type="info"
						message={createInterpolateElement(
							__(
								'To configure a regex redirect, you must first select <strong>Regex</strong> in the Advanced settings below.',
								'wds'
							),
							{
								strong: <strong />,
							}
						)}
					/>
				)}

				{this.renderDestination()}

				<SelectField
					label={__('Redirect Type', 'wds')}
					description={__(
						'This tells search engines whether to keep indexing the old page, or replace it with the new page.',
						'wds'
					)}
					options={{
						301: __('301 Permanent', 'wds'),
						302: __('302 Temporary', 'wds'),
					}}
					selectedValue={type}
					onSelect={(tp) => onTypeChange(tp)}
					disabled={inProgress}
				/>
			</>
		);
	}
}
