import React from 'react';

export default class Checkbox extends React.Component {
	static defaultProps = {
		id: '',
		checked: false,
		disabled: false,
		label: '',
		onChange: () => false,
	};

	render() {
		return (
			<label className="sui-checkbox">
				<input
					id={this.props.id}
					type="checkbox"
					checked={this.props.checked}
					disabled={this.props.disabled}
					onChange={(e) => this.props.onChange(e.target.checked)}
				/>
				<span aria-hidden="true" />
				{this.props.label && <span>{this.props.label}</span>}
			</label>
		);
	}
}
