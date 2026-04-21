import React from 'react';
import { __, sprintf } from '@wordpress/i18n';
import VerticalTab from '../../components/vertical-tab';
import UrlUtil from '../../utils/url-util';
import Deactivate from '../../components/deactivate';
import DisabledComponent from '../../components/disabled-component';
import Button from '../../components/button';
import ConfigValues from '../../es6/config-values';
import CodeType from './breadcrumb/code-type';
import Previews from './breadcrumb/previews';
import Separators from './breadcrumb/separators';
import Configs from './breadcrumb/configs';
import LabelFormat from './breadcrumb/label-format';

export default class Breadcrumb extends React.Component {
	constructor(props) {
		super(props);

		const options = ConfigValues.get('options', 'breadcrumb'),
			previews = options.map((opt) => ({
				type: opt.type,
				label: opt.label,
				snippets: opt.snippets,
				value: opt.value,
				default: opt.placeholder,
			})),
			configs = ConfigValues.get('configs', 'breadcrumb'),
			prefix = ConfigValues.get('prefix', 'breadcrumb'),
			separator = ConfigValues.get('separator', 'breadcrumb'),
			custom = ConfigValues.get('custom_sep', 'breadcrumb'),
			homeText = ConfigValues.get('home_label', 'breadcrumb')
				? ConfigValues.get('home_label', 'breadcrumb')
				: 'Home';
		this.state = {
			previews,
			configs,
			prefix,
			separator,
			custom,
			homeText,
		};
	}

	render() {
		const isActive =
			UrlUtil.getQueryParam('tab') &&
			UrlUtil.getQueryParam('tab') === 'tab_breadcrumb';

		const enabled = ConfigValues.get('enabled', 'breadcrumb');

		return (
			<VerticalTab
				id="tab_breadcrumb"
				title={__('Breadcrumbs', 'wds')}
				children={enabled ? this.getSettings() : this.getDeactivated()}
				buttonText={enabled && __('Save Settings', 'wds')}
				isActive={isActive}
			></VerticalTab>
		);
	}

	getSettings() {
		const { previews, configs, prefix, separator, custom, homeText } =
			this.state;

		const options = ConfigValues.get('options', 'breadcrumb'),
			formats = options.map((opt) => ({
				type: opt.type,
				/* translators: %s: Breadcrumb type name */
				label: sprintf(__('%s Label Format'), opt.title || opt.label),
				value: opt.value,
				placeholder: opt.placeholder,
				variables: opt.variables,
			}));
		return [
			<CodeType key={0} />,
			<Previews
				key={1}
				previews={previews}
				options={configs}
				homeTrail={configs?.home_trail?.value}
				prefix={prefix}
				separator={separator}
				custom={custom}
				homeText={homeText}
			/>,
			<Separators
				key={2}
				updateSeparator={(value) => this.updateSeparator(value)}
				updateCustomSeparator={(value) =>
					this.updateCustomSeparator(value)
				}
			/>,
			<Configs
				key={3}
				configs={configs}
				prefix={prefix}
				homeText={homeText}
				onChange={(updated) => this.handleConfigChange(updated)}
				onHandlePrefix={(value) => this.handlePrefixChange(value)}
				onHandleHomeText={(value) => this.handleHomeTextChange(value)}
			/>,
			<LabelFormat
				key={4}
				formats={formats}
				onChange={(index, value) =>
					this.handlePreviewChange(index, value)
				}
			/>,
			<Deactivate
				key={5}
				description={__(
					'No longer need breadcrumbs? This will deactivate this feature.',
					'wds'
				)}
				name="deactivate-breadcrumb-component"
			></Deactivate>,
		];
	}

	getDeactivated() {
		return (
			<DisabledComponent
				imagePath={ConfigValues.get('image', 'breadcrumb')}
				message={__(
					"Breadcrumbs provide an organized trail of links showing a visitor's journey on a website, improving the user experience and aiding search engines in understanding the site's structure for enhanced SEO.",
					'wds'
				)}
				component="breadcrumb"
				nonce={ConfigValues.get('settings_nonce', 'breadcrumb')}
				referer={ConfigValues.get('referer', 'breadcrumb')}
				button={
					<Button
						name="submit"
						type="submit"
						color="blue"
						text={__('Activate', 'wds')}
					/>
				}
				inner
			/>
		);
	}

	handleConfigChange(key) {
		this.setState({
			configs: {
				...this.state.configs,
				[key]: {
					...this.state.configs[key],
					value: !this.state.configs[key].value,
				},
			},
		});
	}
	handlePreviewChange(i, v) {
		const originalPreview = this.state.previews;
		this.setState({
			previews: originalPreview.map((item, index) => {
				if (index === i) {
					return { ...item, value: v };
				}
				return item;
			}),
		});
	}
	handlePrefixChange(value) {
		this.setState({ prefix: value });
	}
	handleHomeTextChange(value) {
		this.setState({ homeText: value });
	}
	updateSeparator(value) {
		this.setState({ separator: value });
	}
	updateCustomSeparator(value) {
		this.setState({ custom: value });
	}
}
