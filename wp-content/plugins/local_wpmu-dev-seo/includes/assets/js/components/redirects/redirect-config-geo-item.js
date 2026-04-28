import React from 'react';
import AccordionItem from '../accordion-item';
import { __ } from '@wordpress/i18n';
import Button from '../button';
import AccordionItemOpenIndicator from '../accordion-item-open-indicator';
import SelectField from '../form-fields/select-field';
import GeoUtil from '../../utils/geo-util';
import update from 'immutability-helper';
import { UrlField } from './redirect-commons';

const countryList = GeoUtil.getCountries();

export default class RedirectConfigGeoItem extends React.Component {
	static defaultProps = {
		rule: {},
		inProgress: false,
		onUpdate: () => false,
		onDelete: () => false,
	};

	getTitle() {
		const { indicate, countries } = this.props.rule;

		let title = !parseInt(indicate) ? 'From' : 'Not From';

		if (Array.isArray(countries) && countries.length) {
			title += ' ' + countryList[countries[0]];

			const restCnt = countries.length - 1;

			if (restCnt) {
				title += ' +' + restCnt + ' more';
			}
		} else {
			title += __(' No Country', 'wds');
		}

		return title;
	}

	handleChange(key, value, isValid = true) {
		this.props.onUpdate(
			update(this.props.rule, {
				[key]: { $set: value },
				isValid: {
					$set: isValid,
				},
			})
		);
	}

	handleDelete(e) {
		e.preventDefault();
		e.stopPropagation();

		this.props.onDelete();
	}

	render() {
		const { rule, inProgress } = this.props;

		return (
			<AccordionItem
				header={
					<>
						<div className="sui-accordion-item-title">
							<span
								className="sui-icon-link"
								aria-hidden="true"
							/>
							{this.getTitle()}
						</div>

						<div className="sui-accordion-col-auto">
							<Button
								icon="sui-icon-trash"
								color="red"
								onClick={(e) => this.handleDelete(e)}
							></Button>
							<AccordionItemOpenIndicator />
						</div>
					</>
				}
			>
				<SelectField
					label={__('Rule', 'wds')}
					options={{
						0: __('From', 'wds'),
						1: __('Not From', 'wds'),
					}}
					selectedValue={rule.indicate}
					onSelect={(val) => this.handleChange('indicate', val)}
					disabled={inProgress}
				/>

				<SelectField
					label={__('Countries', 'wds')}
					selectedValue={rule.countries}
					multiple={true}
					onSelect={(values) =>
						this.handleChange('countries', values)
					}
					options={countryList}
					disabled={inProgress}
					prefix={
						<span
							className="sui-icon-web-globe-world"
							aria-hidden="true"
						/>
					}
				/>

				<UrlField
					label={__('Redirect URL', 'wds')}
					value={rule.url}
					onChange={(val, isValid) =>
						this.handleChange('url', val, isValid)
					}
					disabled={inProgress}
				/>
			</AccordionItem>
		);
	}
}
