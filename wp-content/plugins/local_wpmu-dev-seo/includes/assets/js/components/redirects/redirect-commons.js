import ConfigValues from '../../es6/config-values';
import fieldWithValidation from '../field-with-validation';
import TextInputField from '../form-fields/text-input-field';
import {
	isNonEmpty,
	isUrlValid,
	isValuePlainText,
	Validator,
} from '../../utils/validators';
import { __ } from '@wordpress/i18n';

export function getDefaultRedirectType() {
	return ConfigValues.get('default_redirect_type', 'autolinks');
}

export const UrlField = fieldWithValidation(TextInputField, [
	isNonEmpty,
	isValuePlainText,
	new Validator(
		isUrlValid,
		__(
			'You need to use an absolute URL like https://domain.com/new-url or start with a slash /new-url.',
			'wds'
		)
	),
]);
