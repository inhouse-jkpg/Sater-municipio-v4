import React from 'react';
import Modal from '../modal';
import { __, sprintf } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';
import Button from '../button';
import { getDefaultRedirectType } from './redirect-commons';
import RedirectConfigTabs from './redirect-config-tabs';
import ConfigValues from '../../es6/config-values';
import ConfirmationModal from '../confirmation-modal';
import update from 'immutability-helper';
import { uniqueId } from 'lodash-es';

const homeUrl = ConfigValues.get('home_url', 'redirects') || {};

export default class RedirectModal extends React.Component {
	static defaultProps = {
		id: '',
		title: '',
		source: '',
		destination: '',
		rules: [],
		type: getDefaultRedirectType(),
		options: [],
		inProgress: false,
		onSave: () => false,
		onClose: () => false,
	};

	constructor(props) {
		super(props);

		const { title, source, destination, type, rules, options } = this.props;

		this.state = {
			title,
			isTitleValid: true,
			source,
			isSrcValid: !!source,
			destination,
			isDstValid: !!destination,
			type,
			rules: rules || [],
			ruleKeys: (rules || []).map(() => uniqueId()),
			options,
			deletingRule: false,
			ruleInd: -1,
		};
	}

	handleTitleChange(title, isValid) {
		this.setState({
			title,
			isTitleValid: isValid,
		});
	}

	handleSourceChange(source, isValid) {
		this.setState({
			source,
			isSrcValid: isValid,
		});
	}

	handleDestinationChange(destination, isValid = false, type = 'plain') {
		this.setState({
			destination,
			isDstValid: isValid,
		});

		const types = ['plain', 'post', 'page'];

		const options = this.state.options.filter(
			(opt) => !types.includes(opt)
		);

		if (type !== 'plain') {
			options.push(type);
		}

		this.setState({ options });
	}

	handleOptionChange(option, value) {
		let { options } = this.state;

		if (value) {
			if (!options.includes(option)) {
				options.push(option);
			}
		} else {
			options = options.filter((opt) => opt !== option);
		}

		this.setState({ options });
	}

	handleRulesChange(rules) {
		const prevRules = this.state.rules || [];

		this.setState({
			rules,
			ruleKeys:
				rules.length > prevRules.length
					? update(this.state.ruleKeys, { $push: [uniqueId()] })
					: this.state.ruleKeys,
		});
	}

	askRuleDelete(ind) {
		this.setState({
			deletingRule: !this.state.deletingRule,
			ruleInd: ind,
		});
	}

	handleRuleDelete() {
		this.setState({
			deletingRule: false,
			rules: update(this.state.rules, {
				$splice: [[this.state.ruleInd, 1]],
			}),
			ruleKeys: update(this.state.ruleKeys, {
				$splice: [[this.state.ruleInd, 1]],
			}),
		});
	}

	render() {
		const {
			source,
			isSrcValid,
			destination,
			isDstValid,
			type,
			title,
			isTitleValid,
			ruleKeys,
			options,
			deletingRule,
		} = this.state;
		const { id, inProgress, onClose, onSave } = this.props;

		const rules = Array.isArray(this.state.rules) ? this.state.rules : [];

		const onSubmit = () =>
			onSave({
				id,
				title: title.trim(),
				source: source.trim(),
				destination: destination.trim(),
				type,
				options,
				rules: rules.map(({ isValid, ...rule }) => rule),
			});

		const isRulesInvalid = rules.find((rule) => rule.isValid === false);

		const submissionDisabled =
			!isTitleValid ||
			!isSrcValid ||
			(isDstValid && rules.length && isRulesInvalid) ||
			(!isDstValid && (!rules.length || isRulesInvalid)) ||
			inProgress;

		const isRegex = options.includes('regex');

		return (
			<>
				<Modal
					id="wds-add-redirect-form"
					title={__('Add Redirect', 'wds')}
					description={createInterpolateElement(
						sprintf(
							// translators: %s: Home url.
							__(
								'Allowed formats include relative URLs like <strong>/cats</strong> or absolute URLs such as <strong>%s/cats</strong>.',
								'wds'
							),
							homeUrl.replace(/\/$/, '')
						),
						{
							strong: <strong />,
						}
					)}
					onEnter={onSubmit}
					onClose={onClose}
					disableCloseButton={inProgress}
					enterDisabled={submissionDisabled}
					focusAfterOpen="wds-source-field"
					focusAfterClose="wds-add-redirect-dashed-button"
					dialogClasses={{
						'sui-modal-md': true,
						'sui-modal-sm': false,
					}}
					small={true}
					footer={
						<>
							<Button
								text={__('Cancel', 'wds')}
								ghost={true}
								onClick={onClose}
								disabled={inProgress}
							/>
							<Button
								text={__('Apply Redirect', 'wds')}
								color="blue"
								onClick={onSubmit}
								icon="sui-icon-save"
								disabled={submissionDisabled}
								loading={inProgress}
							/>
						</>
					}
				>
					<RedirectConfigTabs
						title={title}
						source={source}
						destination={destination}
						type={type}
						rules={rules}
						ruleKeys={ruleKeys}
						isRegex={isRegex}
						options={options}
						inProgress={inProgress}
						isRulesValid={rules.length && !isRulesInvalid}
						onTitleChange={(tl, isValid) =>
							this.handleTitleChange(tl, isValid)
						}
						onSourceChange={(src, isValid) =>
							this.handleSourceChange(src, isValid)
						}
						onDestinationChange={(dest, isValid, tp) =>
							this.handleDestinationChange(dest, isValid, tp)
						}
						onTypeChange={(tp) => this.setState({ type: tp })}
						onRegExChange={(opt, val) =>
							this.handleOptionChange(opt, val)
						}
						onRulesChange={(changedRules) => {
							this.handleRulesChange(changedRules);
						}}
						onAskRuleDelete={(ind) => this.askRuleDelete(ind)}
					/>
				</Modal>
				{deletingRule && (
					<ConfirmationModal
						id="geo-redirect-removing"
						title={__('Are you sure?', 'wds')}
						description={__(
							'Are you sure you want to delete this rule? This action is irreversible.',
							'wds'
						)}
						onClose={() => this.askRuleDelete()}
						onDelete={() => this.handleRuleDelete()}
					/>
				)}
			</>
		);
	}
}
