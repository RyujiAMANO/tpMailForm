<?php
class Pengin_View_Html_Checkbox extends Pengin_View_Html_AbstractElement
                                implements Pengin_View_Html_ElementInterface
{
	public static function render(array $parameters = array())
	{
		$options   = self::_shiftParameter('options', $parameters, array());
		$checked   = self::_shiftParameter('checked', $parameters);
		$separator = self::_shiftParameter('separator', $parameters, ' ');
		return self::_renderInputs($parameters, $options, $checked, $separator);
	}

	protected static function _renderInputs(array $defaultParameters, array $options, $checked, $separator)
	{
		$template = '<label><input%s />%s</label>';
		$inputTags = array();

		foreach ( $options as $key => $value ) {
			$parameters = $defaultParameters;
			$parameters['value'] = $key;
			$parameters['name']  = sprintf('%s[]', $parameters['name']);

			if ( is_array($checked) === true and in_array($key, $checked) === true ) {
				$parameters['checked'] = 'checked';
			}

			$attributes = Pengin_TextFilter::buildAttributeString($parameters);
			$contents   = htmlspecialchars($value, ENT_QUOTES);

			$inputTags[] = sprintf($template, $attributes, $contents);
		}

		$inputTags = implode($separator, $inputTags);

		return $inputTags;
	}
}
