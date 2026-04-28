import React from 'react';
import Dropdown from '../dropdown';
import DropdownButton from '../dropdown-button';
import { __, sprintf } from '@wordpress/i18n';
import Checkbox from '../checkbox';
import classnames from 'classnames';
import GeoUtil from '../../utils/geo-util';

export default class RedirectItem extends React.Component {
	static defaultProps = {
		id: '',
		title: '',
		source: '',
		destination: '',
		permalink: '',
		type: '',
		rules: [],
		options: [],
		selected: false,
		onToggle: () => false,
		onEdit: () => false,
		onDelete: () => false,
	};

	render() {
		const {
			selected,
			title,
			source,
			destination,
			permalink,
			type,
			rules,
			onToggle,
			onEdit,
			onDelete,
		} = this.props;

		return (
			<div
				className={classnames('wds-redirect-item sui-builder-field', {
					'wds-redirect-has-title': !!title,
				})}
			>
				<div className="wds-redirect-item-checkbox">
					<Checkbox
						checked={selected}
						onChange={(isChecked) => onToggle(isChecked)}
					/>
				</div>

				<div className="wds-redirect-item-source">
					<div className="sui-tooltip" data-tooltip={source}>
						<div className="wds-redirect-item-source-trimmed">
							{source}
						</div>
					</div>
					{title && <small>{title}</small>}
				</div>

				<div className="wds-redirect-item-destination">
					<small>
						{permalink ||
							destination ||
							(rules?.length
								? __(
										'Location-based Redirection',
										'wds'
								  )
								: '')}
					</small>
				</div>

				<div className="wds-redirect-item-options">
					{type === 301 && (
						<span className="sui-tag sui-tag-sm">
							{__('Permanent', 'wds')}
						</span>
					)}
					{type === 302 && (
						<span className="sui-tag sui-tag-sm">
							{__('Temporary', 'wds')}
						</span>
					)}
					{this.options()}
					{this.rulesTooltip()}
				</div>

				<div className="wds-redirect-item-dropdown">
					<Dropdown
						buttons={[
							<DropdownButton
								key={0}
								className="wds-edit-redirect-item"
								icon="sui-icon-pencil"
								text={__('Edit', 'wds')}
								onClick={() => onEdit()}
							/>,
							<DropdownButton
								key={1}
								className="wds-remove-redirect-item"
								icon="sui-icon-trash"
								text={__('Remove', 'wds')}
								red={true}
								onClick={() => onDelete()}
							/>,
						]}
					/>
				</div>
			</div>
		);
	}

	options() {
		const labels = {
			regex: __('Regex', 'wds'),
		};

		return this.props.options.map(
			(option) =>
				labels.hasOwnProperty(option) && (
					<span
						className="sui-tag sui-tag-yellow sui-tag-sm"
						key={option}
					>
						{labels[option]}
					</span>
				)
		);
	}

	rulesTooltip() {
		const { rules } = this.props;

		if (!rules?.length) {
			return '';
		}

		let froms = [],
			notFroms = [];

		rules.forEach((rule) => {
			if (rule.indicate === '1') {
				notFroms = notFroms.concat(rule.countries);
			} else {
				froms = froms.concat(rule.countries);
			}
		});

		let content = '';

		froms = froms
			.filter((fr, ind) => froms.indexOf(fr) === ind)
			.map((fr) => GeoUtil.getCountries()[fr])
			.sort();

		if (froms.length) {
			if (froms.length > 3) {
				content = sprintf(
					// translators: %s: comma separated country names.
					__('From %s, etc.', 'wds'),
					froms.slice(0, 3).join(', ')
				);
			} else {
				content = sprintf(
					// translators: %s: comma separated country names.
					__('From %s.', 'wds'),
					froms.join(', ')
				);
			}
		}

		notFroms = notFroms
			.filter((nf, ind) => notFroms.indexOf(nf) === ind)
			.map((nf) => GeoUtil.getCountries()[nf])
			.sort();

		if (notFroms.length) {
			if (content.length) {
				content += '\n';
			}

			if (notFroms.length > 3) {
				content += sprintf(
					// translators: %s: comma separated country names.
					__('Not from %s, etc.', 'wds'),
					notFroms.slice(0, 3).join(', ')
				);
			} else {
				content += sprintf(
					// translators: %s: comma separated country names.
					__('Not from %s.', 'wds'),
					notFroms.join(', ')
				);
			}
		}
		return (
			<span
				className="sui-tooltip sui-tooltip-constrained"
				data-tooltip={content}
				style={{ '--tooltip-width': '170px' }}
			>
				<span className="sui-icon-web-globe-world" aria-hidden="true" />
			</span>
		);
	}
}
