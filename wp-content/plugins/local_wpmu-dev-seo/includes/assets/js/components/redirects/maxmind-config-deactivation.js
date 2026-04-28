import React from 'react';
import { __ } from '@wordpress/i18n';
import Notice from '../notices/notice';
import Button from '../button';
import ConfigValues from '../../es6/config-values';
import RequestUtil from '../../utils/request-util';
import TextInputField from '../form-fields/text-input-field';

export default class MaxmindConfigDeactivation extends React.Component {
	static defaultProps = {
		reloadPage: false,
	};

	constructor(props) {
		super(props);

		this.state = {
			licenseKey: ConfigValues.get('maxmind_license', 'redirects') || '',
			errMsg: '',
			loading: false,
		};
	}

	handleDisconnect(e) {
		e.preventDefault();

		this.setState({ loading: true }, () => {
			RequestUtil.post(
				'wds_reset_geodb',
				ConfigValues.get('nonce', 'redirects')
			)
				.then(() => {
					if (this.props.reloadPage) {
						window.location.reload();
						return;
					}

					this.setState({
						isActivated: false,
						licenseKey: '',
					});
				})
				.catch((err) => {
					this.setState({ errMsg: err.message });
				})
				.finally(() => {
					this.setState({ loading: false });
				});
		});

		return false;
	}

	render() {
		const { errMsg, licenseKey, loading } = this.state;

		return (
			<>
				<TextInputField
					label={
						<>
							{__('Maxmind License Key', 'wds')}
							<span className="sui-tag sui-tag-green sui-tag-sm">
								{__('Connected', 'wds')}
							</span>
						</>
					}
					description={__(
						'Your site is connected to above Maxmind license key. SmartCrawl automatically downloads latest GeoLite2 data weekly. You can use the disconnect button above to change the license key.',
						'wds'
					)}
					prefix={
						<span className="sui-icon-key" aria-hidden="true" />
					}
					suffix={
						<>
							<Button
								icon="sui-icon-plug-disconnected"
								text={__('Disconnect', 'wds')}
								onClick={(e) => this.handleDisconnect(e)}
								loading={loading}
							></Button>
						</>
					}
					value={licenseKey}
					readOnly={true}
					loading={loading}
				></TextInputField>

				{!!errMsg && <Notice type="error" message={errMsg} />}
			</>
		);
	}
}
