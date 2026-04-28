import React from 'react';
import RedirectConfigGeoItem from './redirect-config-geo-item';
import { __ } from '@wordpress/i18n';
import Button from '../button';
import update from 'immutability-helper';
import ConfigValues from '../../es6/config-values';
import MaxmindConfigActivation from './maxmind-config-activation';
import { getDefaultRedirectType } from './redirect-commons';

const isMaxMindActivated = ConfigValues.get('maxmind_license', 'redirects');

export default class RedirectConfigRules extends React.Component {
	static defaultProps = {
		rules: [],
		inProgress: false,
		onUpdate: () => false,
		onAskRuleDelete: () => false,
	};

	handleAdd() {
		this.props.onUpdate(
			update(this.props.rules, {
				$push: [{ isValid: false }],
			})
		);
	}

	handleChange(rule, ind) {
		let isValid = rule.isValid || true;

		if (!Array.isArray(rule.countries) || !rule.countries.length) {
			isValid = false;
		}

		if (!rule.url) {
			isValid = false;
		}

		rule.isValid = isValid;

		this.props.onUpdate(
			update(this.props.rules, {
				[ind]: { $set: rule },
			})
		);
	}

	askDeleting(ind = -1) {
		this.props.onAskRuleDelete(ind);
	}

	render() {
		if (!isMaxMindActivated) {
			return (
				<>
					<p
						className="sui-description"
						style={{ textAlign: 'left' }}
					>
						{__(
							'Add location-based redirect rules to ensure users see the most relevant content based on their locations.',
							'wds'
						)}
					</p>
					<MaxmindConfigActivation reloadPage={true} />
				</>
			);
		}

		const { rules, ruleKeys, inProgress } = this.props;

		return (
			<>
				<p className="sui-description" style={{ textAlign: 'left' }}>
					{__(
						'Add location-based redirect rules to ensure users see the most relevant content, based on their locations.',
						'wds'
					)}
				</p>

				<div className="wds-redirect-rules-container">
					{rules.length > 0 && (
						<div className="sui-accordion">
							{rules.map((rule, ind) => (
								<RedirectConfigGeoItem
									key={ruleKeys[ind]}
									rule={rule}
									inProgress={inProgress}
									onUpdate={(updatedRule) =>
										this.handleChange(updatedRule, ind)
									}
									onDelete={() => this.askDeleting(ind)}
								/>
							))}
						</div>
					)}

					<Button
						dashed={true}
						text={__('Add Rule', 'wds')}
						icon="sui-icon-plus"
						onClick={() => this.handleAdd()}
						disabled={inProgress}
					></Button>
				</div>
			</>
		);
	}
}
