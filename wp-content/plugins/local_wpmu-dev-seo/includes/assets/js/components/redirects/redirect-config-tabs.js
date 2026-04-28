import React from 'react';
import Tabs from '../tabs';
import { __ } from '@wordpress/i18n';
import RedirectConfigPlain from './redirect-config-plain';
import RedirectConfigAdvanced from './redirect-config-advanced';
import RedirectConfigRules from './redirect-config-rules';
import ConfigValues from '../../es6/config-values';
import RequestUtil from '../../utils/request-util';

export default class RedirectConfigTabs extends React.Component {
	static defaultProps = {
		title: '',
		source: '',
		destination: '',
		type: '',
		rules: [],
		ruleKeys: [],
		isRegex: false,
		options: false,
		inProgress: false,
		isRulesValid: false,
		onTitleChange: () => false,
		onSourceChange: () => false,
		onDestinationChange: () => false,
		onTypeChange: () => false,
		onRegExChange: () => false,
		onRulesChange: () => false,
		onAskRuleDelete: () => false,
	};

	constructor(props) {
		super(props);

		this.state = {
			selectedTab: 'default',
			geoVisited: ConfigValues.get('visited_features', 'admin').includes(
				'url-redirection'
			),
		};
	}

	handleTabChange(tab) {
		event.preventDefault();
		event.stopPropagation();

		this.setState({
			selectedTab: tab,
		});

		if (!this.state.geoVisited && tab === 'geolocation') {
			const isNew =
				ConfigValues.get('new_feature_status', 'admin') !== '3';

			if (isNew) {
				RequestUtil.post(
					'wds_update_new_feature_status',
					ConfigValues.get('nonce', 'admin'),
					{
						step: 3,
					}
				).then(() => {
					this.setState({
						geoVisited: true,
					});
				});
			}
		}
	}

	render() {
		const {
			title,
			source,
			destination,
			type,
			options,
			rules,
			ruleKeys,
			isRegex,
			isRulesValid,
			inProgress,
			onTitleChange,
			onSourceChange,
			onDestinationChange,
			onTypeChange,
			onRegExChange,
			onRulesChange,
			onAskRuleDelete,
			deleteRule,
		} = this.props;

		const isMember = ConfigValues.get('is_member', 'admin') === '1';

		return (
			<Tabs
				tabs={{
					default: {
						label: __('Redirect', 'wds'),
						component: (
							<RedirectConfigPlain
								source={source}
								destination={destination}
								type={type}
								isRegex={isRegex}
								isRulesValid={isRulesValid}
								options={options}
								onSourceChange={onSourceChange}
								onDestinationChange={onDestinationChange}
								onTypeChange={onTypeChange}
								inProgress={inProgress}
							/>
						),
					},
					advanced: {
						label: __('Advanced', 'wds'),
						component: (
							<RedirectConfigAdvanced
								title={title}
								isRegex={isRegex}
								onTitleChange={onTitleChange}
								onRegExChange={onRegExChange}
								inProgress={inProgress}
							/>
						),
					},
					geolocation: {
						label: (
							<>
								{__('Location Rules', 'wds')}
								{!isMember && (
									<span
										className="sui-tag sui-tag-pro sui-tooltip"
										data-tooltip={__(
											'Upgrade to SmartCrawl Pro',
											'wds'
										)}
									>
										{__('Pro', 'wds')}
									</span>
								)}
								{!!isMember && (
									<span className="sui-tag sui-tag-green sui-tag-sm">
										{__('New', 'wds')}
									</span>
								)}
							</>
						),
						component: (
							<RedirectConfigRules
								rules={rules || []}
								ruleKeys={ruleKeys}
								onUpdate={onRulesChange}
								onAskRuleDelete={onAskRuleDelete}
								inProgress={inProgress}
								deleteRule={deleteRule}
							/>
						),
					},
				}}
				value={this.state.selectedTab}
				flushed={true}
				onChange={(tab) => this.handleTabChange(tab)}
			></Tabs>
		);
	}
}
