// @flow
import React from 'react';
import equals from 'fast-deep-equal';
import { observer } from 'mobx-react';
import { action, computed, observable, toJS } from 'mobx';
import debounce from 'debounce';
import Input from 'sulu-admin-bundle/components/Input';
import AutoCompletePopover from 'sulu-admin-bundle/components/AutoCompletePopover';
import singleAutoCompleteStyles from 'sulu-admin-bundle/components/SingleAutoComplete/singleAutoComplete.scss';
import type { ElementRef } from 'react';

const LENS_ICON = 'su-search';
const DEBOUNCE_TIME = 300;

type Props = {|
  disabled: boolean,
    displayProperty: string,
      icon ?: string,
      id ?: string,
      loading ?: boolean,
      onChange: (value: ?Object) => void,
        onFinish ?: () => void,
        /** Called with a debounce when text is entered inside the input */
        onSearch: (query: string) => void,
          placeholder ?: string,
          searchProperties: Array < string >,
            suggestions: Array < Object >,
              value: ?Object,
|};

@observer
export default class SingleLineAutoComplete extends React.Component<Props> {
  static defaultProps = {
    disabled: false,
  };

  @observable inputContainerRef: ElementRef<*>;

  @observable displaySuggestions = false;
  @observable inputValue: ?string = this.props.value ? this.props.value : undefined;

  overrideValue: boolean = false;

  componentDidUpdate(prevProps: Props) {
    const {
      displayProperty,
      value,
    } = this.props;

    if (!equals(toJS(prevProps.value), toJS(value))) {
      this.setInputValue(value);
    }
  }

  componentWillUnmount() {
    this.debouncedSearch.clear();
  }

  @action setInputValue(value: ?string) {
    this.inputValue = value;
  }

  @action setInputContainerRef = (inputContainerRef: ?ElementRef<*>) => {
    if (inputContainerRef) {
      this.inputContainerRef = inputContainerRef;
    }
  };

  @computed get popoverMinWidth() {
    return this.inputContainerRef ? this.inputContainerRef.scrollWidth - 10 : 0;
  }

  @action search = (query: string) => {
    this.props.onSearch(query);
    this.displaySuggestions = true;
  };

  debouncedSearch = debounce(this.search, DEBOUNCE_TIME);

  handlePopoverSelect = (value: ?string) => {
    const {
      displayProperty,
      onChange,
    } = this.props;
    const val = value ? value[displayProperty] : null
    this.setInputValue(val);
    onChange(val);
  };

  handleInputChange = (value: ?string) => {
    if (!value) {
      this.props.onChange(null);
    }

    this.setInputValue(value);
    this.debouncedSearch(this.inputValue);
  };

  @action handleInputFocus = () => {
    this.search(this.inputValue || '');
  };

  @action handlePopoverClose = () => {
    this.displaySuggestions = false;
  };

  render() {
    const {
      disabled,
      id,
      icon,
      headline,
      loading,
      onFinish,
      placeholder,
      searchProperties,
      suggestions,
    } = this.props;
    const { inputValue } = this;

    // The mousetrap class is required to allow mousetrap catch key bindings for up and down keys
    return (
      <div className={singleAutoCompleteStyles.singleAutoComplete}>
        <Input
          autocomplete="off"
          disabled={disabled}
          id={id}
          headline={headline}
          icon={icon}
          inputClass="mousetrap"
          inputContainerRef={this.setInputContainerRef}
          loading={loading}
          onBlur={onFinish}
          onChange={this.handleInputChange}
          onFocus={this.handleInputFocus}
          placeholder={placeholder}
          value={inputValue}
        />
        <AutoCompletePopover
          anchorElement={this.inputContainerRef}
          minWidth={this.popoverMinWidth}
          onClose={this.handlePopoverClose}
          onSelect={this.handlePopoverSelect}
          open={!disabled && this.displaySuggestions && suggestions.length > 0}
          query={inputValue}
          searchProperties={searchProperties}
          suggestions={suggestions}
        />
      </div>
    );
  }
}
