import React from 'react';
import { __, sprintf } from '@wordpress/i18n';
import classnames from 'classnames';
import { uniqueId } from 'lodash-es';

export default class Toggle extends React.Component {
	static defaultProps = {
		id: '',
		name: '',
		label: '',
		labelPlacement: 'end', // enum values: start, end
		description: '',
		checked: false,
		disabled: false,
		inverted: false,
		wrapped: false,
		wrapperClass: '',
		fullWidth: false,
		onChange: () => false,
	};

	constructor(props) {
		super(props);

		this.state = { checked: this.props.checked };
	}

	handleChange(e) {
		this.props.onChange(e.target.checked, e.target);

		this.setState({ checked: e.target.checked });
	}

	render() {
		if (this.props.wrapped) {
			return (
				<div
					className={classnames(
						'sui-toggle-wrapper',
						this.props.wrapperClass,
						this.props.fullWidth && 'sui-toggle-wrapper-full'
					)}
				>
					{this.inner()}
				</div>
			);
		}
		return this.inner();
	}

	inner() {
		const {
			name,
			label,
			labelPlacement,
			description,
			children,
			disabled,
			inverted,
			fullWidth,
		} = this.props;

		const { checked } = this.state;

		const inputProps = {};

		const uniqId = uniqueId();
		let { id } = this.props;

		if (!id) {
			id = 'wds-toggle-' + uniqId;
		}

		inputProps.id = id;

		if (name) {
			inputProps.name = name;
		}

		if (label) {
			inputProps['aria-labelledby'] = `sui-toggle-label-${uniqId}`;
		}

		if (description) {
			inputProps['aria-describedby'] = `sui-toggle-description-${uniqId}`;
		}

		if (children) {
			inputProps['aria-controls'] = `sui-toggle-children-${uniqId}`;
		}

		const hasChildren = (inverted ? !checked : checked) && children;

		return (
			<React.Fragment>
				<label
					htmlFor={id}
					className={classnames(
						'sui-toggle',
						labelPlacement !== 'end' &&
							`sui-toggle-label-${labelPlacement}`,
						{
							'sui-toggle-full': fullWidth,
							'sui-toggle-inverted': inverted,
						}
					)}
				>
					<input
						type="checkbox"
						checked={checked}
						disabled={disabled}
						onChange={(e) => this.handleChange(e)}
						{...inputProps}
					/>

					<span className="sui-toggle-slider" aria-hidden="true" />

					{!!label && (
						<span
							id={`sui-toggle-label-${uniqId}`}
							className="sui-toggle-label"
						>
							{label}
						</span>
					)}

					{!!description && (
						<span
							id={`sui-toggle-description-${uniqId}`}
							className="sui-description"
						>
							{description}
						</span>
					)}
				</label>
				{!!hasChildren && (
					<div
						id={`sui-toggle-content-${uniqId}`}
						className={classnames(
							'sui-toggle-content',
							'sui-border-frame'
						)}
						aria-label={sprintf(
							/* translators: %s: toggle label. */
							__("Children of '%s'", 'wds-texdomain'),
							label
						)}
					>
						{children}
					</div>
				)}
			</React.Fragment>
		);
	}
}
