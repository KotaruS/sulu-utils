// @flow
import React from 'react';
import debounce from 'debounce';
import { computed } from 'mobx';
import RangeComponent from '../components/Range';
import { clamp } from '../utils';

export default class Range extends React.Component {
  constructor(props) {
    super(props);

    const { onChange, schemaOptions, value } = this.props;

    const {
      default_value: {
        value: defaultValue,
      } = {},
      min: minStr,
      max: maxStr,
      step: stepStr,
    } = schemaOptions;
    const min = !isNaN(parseFloat(minStr?.value)) ? parseFloat(minStr?.value) : undefined;
    const max = !isNaN(parseFloat(maxStr?.value)) ? parseFloat(maxStr?.value) : undefined;
    const step = !isNaN(parseFloat(stepStr?.value)) ? parseFloat(stepStr?.value) : undefined;

    if (defaultValue === undefined || defaultValue === null || defaultValue === '') {
      return;
    }

    if (typeof min !== 'number' || typeof max !== 'number') {
      throw new Error('The "min" and "max" value be a valid number!');
    }

    if (typeof min === 'number' && typeof max === 'number' && min > max) {
      throw new Error('The "min" schema option must be higher number than "max"!');
    }

    if (typeof step === 'number' && step > (max - min)) {
      throw new Error('The "step" schema option must be equal or less than difference between "max" and "min"!');
    }

    if (typeof defaultValue !== 'number' && typeof defaultValue !== 'string') {
      throw new Error('The "default_value" schema option must be a string or a number!');
    }

    if (value === undefined) {
      onChange(clamp(defaultValue, min, max), { isDefaultValue: true });
    }
  }

  handleChange = (value) => {
    const { onChange, onFinish } = this.props;

    onChange(value);
    this.handleFinishDebounced(onFinish);
  }

  handleFinishDebounced = debounce((callback) => {
    callback();
  }, 500)

  @computed get hasLabels() {
    const {
      schemaOptions: {
        titles,
      } = {},
    } = this.props;
    return !!titles
  }

  @computed get min() {
    const {
      schemaOptions: {
        min: minRaw,
      } = {}
    } = this.props;

    const min = !isNaN(parseFloat(minRaw?.value)) ? parseFloat(minRaw?.value) : 0;
    return min;
  }

  @computed get max() {
    const {
      schemaOptions: {
        max: maxRaw,
      } = {}
    } = this.props;

    const max = !isNaN(parseFloat(maxRaw?.value)) ? parseFloat(maxRaw?.value) : 10;
    return max;
  }

  @computed get step() {
    const {
      schemaOptions: {
        step: stepRaw,
      } = {}
    } = this.props;

    const step = !isNaN(parseFloat(stepRaw?.value)) ? parseFloat(stepRaw?.value) : 1;
    return step;
  }

  render() {
    const {
      dataPath, disabled, value,
      schemaOptions: {
        default_value,
        titles,
        min,
        max,
        step,
        marks: {
          value: marks,
        } = {},
        ticks: {
          value: ticks,
        } = {},
      } = {},
    } = this.props;
    const rangeValue = value ?? clamp(default_value?.value, min?.value, max?.value) ?? min?.value;

    return (
      <RangeComponent
        value={rangeValue}
        id={dataPath}
        disabled={disabled}
        labels={this.hasLabels}
        onChange={this.handleChange}
        min={this.min}
        marks={marks}
        max={this.max}
        step={this.step}
        showTicks={ticks}
      />
    );
  }
}
