import React from 'react';
import classnames from 'classnames';

export default class Button extends React.Component {
	static defaultProps = {
		id: '',
		name: '',
		text: '',
		color: '',
		dashed: false,
		icon: false,
		loading: false,
		ghost: false,
		disabled: false,
		href: '',
		target: '',
		className: '',
	};

	handleClick(e) {
		e.preventDefault();

		this.props.onClick(e);
	}

	render() {
		const {
			id,
			name,
			type,
			href,
			target,
			disabled,
			text,
			tooltip,
			className,
			color,
			loading,
			ghost,
			dashed,
			onClick,
		} = this.props;

		let HtmlTag, props;

		if (href) {
			HtmlTag = 'a';
			props = { href };

			if (target) {
				props.target = target;
			}
		} else {
			HtmlTag = 'button';
			props = {
				disabled,
			};

			if (onClick) {
				props.onClick = (e) => this.handleClick(e);
			}

			if (type) {
				props.type = type;
			}
		}

		if (id) {
			props.id = id;
		}

		if (name) {
			props.name = name;
		}

		const hasText = text && text.trim();

		if (tooltip) {
			props['data-tooltip'] = tooltip;
		}

		return (
			<React.Fragment>
				<HtmlTag
					{...props}
					className={classnames(className, 'sui-button-' + color, {
						'sui-button-onload': loading,
						'sui-button-ghost': ghost,
						'sui-button-dashed': dashed,
						'sui-tooltip': !!tooltip,
						'sui-button-icon': !hasText,
						'sui-button': hasText,
					})}
				>
					{this.text()}
					{this.loadingIcon()}
				</HtmlTag>
			</React.Fragment>
		);
	}

	text() {
		const icon = this.props.icon ? (
			<span className={this.props.icon} aria-hidden="true" />
		) : (
			''
		);

		const props = {};

		if (this.props.loading) {
			props.className = classnames({
				'sui-loading-text': this.props.loading,
			});
		}

		return (
			<span {...props}>
				{icon} {this.props.text}
			</span>
		);
	}

	loadingIcon() {
		return this.props.loading ? (
			<span className="sui-icon-loader sui-loading" aria-hidden="true" />
		) : (
			''
		);
	}
}
