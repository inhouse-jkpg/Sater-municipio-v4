import React from 'react';
import { __ } from '@wordpress/i18n';
import classnames from 'classnames';
import Notice from './notices/notice';
import Button from './button';
import ConfigValues from '../es6/config-values';

export default class DisabledComponent extends React.Component {
	static defaultProps = {
		imagePath: false,
		message: '',
		notice: '',
		component: '',
		button: false,
		inner: false,
		premium: false,
		upgradeTag: '',
	};

	render() {
		const {
			imagePath,
			message,
			notice,
			component,
			button,
			inner,
			premium,
			upgradeTag,
		} = this.props;

		const nonce = ConfigValues.get('settings_nonce', 'admin');
		const referer = this.props.referer
			? this.props.referer
			: ConfigValues.get('referer', 'admin');
		const isMember = ConfigValues.get('is_member', 'admin') === '1';

		return (
			<div
				className={classnames(
					'sui-message',
					'sui-message-lg',
					!!inner || 'sui-box'
				)}
			>
				{!!imagePath && (
					<img
						src={imagePath}
						aria-hidden="true"
						className="wds-disabled-image"
						alt={__('Disabled component', 'wds')}
					/>
				)}
				<div className="sui-message-content">
					<p>{message}</p>

					{!!notice && <Notice message={notice}></Notice>}

					{premium && !isMember && (
						<Button
							color="purple"
							target="_blank"
							href={
								'https://wpmudev.com/project/smartcrawl-wordpress-seo/?utm_source=smartcrawl&utm_medium=plugin&utm_campaign=' +
								upgradeTag
							}
							text={__('Upgrade to Pro', 'wds')}
						></Button>
					)}

					{(!premium || isMember) && (
						<React.Fragment>
							{component && (
								<input
									type="hidden"
									name="wds-activate-component"
									value={component}
								/>
							)}
							{nonce && (
								<input
									type="hidden"
									id="_wds_nonce"
									name="_wds_nonce"
									value={nonce}
								/>
							)}
							{referer && (
								<input
									type="hidden"
									name="_wp_http_referer"
									value={referer}
								/>
							)}
							{button}
						</React.Fragment>
					)}
				</div>
			</div>
		);
	}
}
