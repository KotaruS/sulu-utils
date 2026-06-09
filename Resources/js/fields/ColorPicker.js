// @flow
import React from 'react';
import ColorPickerComponent from '../components/ColorPicker';

type Props = {|
  disabled: boolean,
    id ?: string,
    name ?: string,
    onBlur ?: () => void,
    onChange: (value: ?string) => void,
      placeholder ?: string,
      valid: boolean,
        value: ?string,
          schemaOptions: ?object,
|};

export default class ColorPicker extends React.Component<Props> {
  static fallbackColors;

  constructor(props) {
    super(props);

    const { onChange, schemaOptions, value } = this.props;

    const {
      colors: {
        value: presetColors = ColorPicker.fallbackColors,
      } = {},
    } = schemaOptions;

    if (typeof presetColors?.value !== 'array' && typeof presetColors?.value !== 'undefined') {
      throw new Error('The "colors" param must be a collection!');
    }
  }

  get colors() {
    const {
      schemaOptions: {
        colors: {
          value: presetColors = ColorPicker.fallbackColors,
        } = {}
      } = {}
    } = this.props;

    return presetColors;
  }


  render() {
    const {
      disabled,
      value,
      id,
      onBlur,
      onChange,
      onFinish,
      placeholder,
      valid,
      schemaOptions,
    } = this.props;

    return (
      <ColorPickerComponent
        disabled={disabled}
        id={id}
        presetColors={this.colors}
        name={name}
        onBlur={onBlur}
        onChange={onChange}
        onFinish={onFinish}
        placeholder={placeholder}
        valid={valid}
        value={value}
        schemaOptions={schemaOptions}
      />
    );
  }
}
