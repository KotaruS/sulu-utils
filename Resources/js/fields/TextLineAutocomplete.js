// @flow
import React from 'react';
import { uuid } from '../utils';
import { action, observable, computed } from 'mobx';
import SingleLineAutoComplete from '../components/SingleLineAutoComplete';

export default class TextLineAutocomplete extends React.Component {
  static defaultProps = {
    disabled: false,
  };
  constructor(props) {
    super(props);
    this.dataId = `tla-${uuid()}`;
  }

  @action handleChange = (value) => {
    const { onChange, onFinish } = this.props;
    if (value) {
      onChange(typeof value === 'object' ? value.name : value);
    } else {
      onChange(null);
    }
  };

  @action handleSearch = (value) => {
    const { onChange, onFinish } = this.props;
    onChange(value)
  }

  @action handleFinish = (event) => {
    const { onChange, onFinish } = this.props;
    const value = event.currentTarget.value
    onChange(value)
    onFinish();
  }

  @computed get suggestions() {
    const { schemaOptions: { suggestions: { value: opts } = {} } = {} } = this.props;
    if (!opts) {
      return [];
    }

    return opts.map((opt) => ({ id: opt.name, name: opt.value }));
  }

  @computed get filteredSuggestions() {
    const value = this.props.value;
    const data = this.suggestions;

    if (!value) {
      return data.slice(0, 10);
    }
    const regexp = new RegExp(value, 'gi');

    return data.filter(({ name }) => name.match(regexp) && name !== value).slice(0, 10);
  }

  render() {
    const { disabled, value, id,
      schemaOptions: {
        headline = false
      } = {}
    } = this.props;
    const suggestions = this.filteredSuggestions;

    return (
      <SingleLineAutoComplete
        disabled={disabled}
        displayProperty="name"
        id={this.dataId}
        loading={false}
        headline={headline}
        onChange={this.handleChange}
        onFinish={this.handleFinish}
        onSearch={this.handleSearch}
        searchProperties={['name']}
        suggestions={suggestions}
        value={value}
      />
    );
  }
}
