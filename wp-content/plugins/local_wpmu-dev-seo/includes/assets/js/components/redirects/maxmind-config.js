import React from 'react';
import ConfigValues from '../../es6/config-values';
import MaxmindConfigActivation from './maxmind-config-activation';
import MaxmindConfigDeactivation from './maxmind-config-deactivation';

export default class MaxmindConfig extends React.Component {
	render() {
		const licenseKey = ConfigValues.get('maxmind_license', 'redirects');

		return (
			<>
				{licenseKey ? (
					<MaxmindConfigDeactivation reloadPage={true} />
				) : (
					<MaxmindConfigActivation reloadPage={true} />
				)}
			</>
		);
	}
}
