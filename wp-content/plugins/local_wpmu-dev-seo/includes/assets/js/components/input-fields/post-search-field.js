import React from 'react';
import $ from 'jQuery';
import SelectField from '../form-fields/select-field';
import { __, sprintf } from '@wordpress/i18n';
import ConfigValues from '../../es6/config-values';
import SUI from 'SUI';
import update from 'immutability-helper';

const ajaxURL = ConfigValues.get('ajax_url', 'admin');

export default class PostSearchField extends React.Component {
	static defaultProps = {
		type: 'post',
		field: 'id',
	};

	constructor(props) {
		super(props);

		this.state = {
			selected: false,
		};
	}

	getAjaxUrl() {
		const params = new URLSearchParams();
		params.append('action', 'wds_search_post');
		params.append('type', this.props.type);
		params.append('field', this.props.field);

		return ajaxURL + '?' + params.toString();
	}

	getLoadTextUrl() {
		const params = new URLSearchParams();
		params.append('action', 'wds_search_post');
		params.append('type', this.props.type);
		params.append('field', this.props.field);
		params.append('request_type', 'text');

		return ajaxURL + '?' + params.toString();
	}

	templateResult(data) {
		let markup;

		const label = SUI.select.escapeJS(data.text);

		if (!data.id) {
			return label;
		}

		markup = $(
			sprintf(
				// translators: 1: Post title, 2: Post links
				__(
					'<span class="sui-search-name">%1$s</span><span class="sui-search-url">%2$s</span>'
				),
				label,
				data.url
			)
		);

		return markup;
	}

	render() {
		return (
			<SelectField
				{...this.props}
				ajaxUrl={this.getAjaxUrl()}
				loadTextAjaxUrl={this.getLoadTextUrl()}
				templateResult={(data) => this.templateResult(data)}
			/>
		);
	}
}
