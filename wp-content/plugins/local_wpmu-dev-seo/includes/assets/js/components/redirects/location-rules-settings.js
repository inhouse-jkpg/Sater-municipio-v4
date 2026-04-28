import React from 'react';
import { __ } from '@wordpress/i18n';
import SettingsRow from '../settings-row';
import ConfigValues from '../../es6/config-values';
import MaxmindConfig from './maxmind-config';
import RequestUtil from '../../utils/request-util';
import $ from 'jQuery';

export default class LocationRulesSettings extends React.Component {
	constructor(props) {
		super(props);

		this.state = {
			isNew: ConfigValues.get('new_feature_badge', 'admin') !== '1',
		};
	}

	componentDidMount() {
		if (!this.state.isNew) {
			return;
		}

		$(window).on('scroll', () => {
			if (this.state.isNew && this.isElementInViewport()) {
				this.setState({ isNew: false });
				RequestUtil.post(
					'wds_update_new_feature_badge',
					ConfigValues.get('nonce', 'admin')
				);
			}
		});
	}

	isElementInViewport() {
		const $target = $('#wds-loc-rules-settings');
		const elementTop = $target.offset().top;
		const elementBottom = elementTop + $target.height();
		const viewportTop = $(window).scrollTop();
		const viewportBottom = viewportTop + $(window).height();

		return elementBottom > viewportTop && elementTop < viewportBottom;
	}

	render() {
		const isMember = ConfigValues.get('is_member', 'admin') === '1';

		return (
			<SettingsRow
				label={
					<>
						{__('Location-based Rules', 'wds')}
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
						{!!isMember && this.state.isNew && (
							<span className="sui-tag sui-tag-green sui-tag-sm">
								{__('New', 'wds')}
							</span>
						)}
					</>
				}
				description={__(
					'Add location-based redirect rules to ensure users see the most relevant content based on their locations.',
					'wds'
				)}
			>
				<MaxmindConfig />
			</SettingsRow>
		);
	}
}
