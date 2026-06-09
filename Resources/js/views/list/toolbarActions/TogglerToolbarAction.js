// @flow
import { action, computed, observable } from 'mobx';
import { translate } from 'sulu-admin-bundle/utils';
import AbstractListToolbarAction from 'sulu-admin-bundle/views/List/toolbarActions/AbstractListToolbarAction';

export default class TogglerToolbarAction extends AbstractListToolbarAction {
  @observable loading: boolean = false;
  toggleValue: IObservableValue<boolean> = observable.box(false);

  constructor(
    listStore: ListStore,
    list: List,
    router: Router,
    locales?: Array<string>,
    resourceStore?: ResourceStore,
    options: { [key: string]: mixed }
  ) {

    if (typeof options.property !== 'string') {
      throw new Error('The "property" option must be a string value!');
    }

    if (typeof options.label !== 'string') {
      throw new Error('The "label" option must be a string value!');
    }
    const reservedKeys = ['active', 'filter', 'limit', 'locale', 'sortColumn', 'sortOrder']
    for (const reservedKey in reservedKeys) {
      if (reservedKey === options.property) {
        throw new Error('The "property" option uses reserved keyword value "' + reservedKey + '"!' + "\n" + 'Reserved keys: [' + reservedKeys.join(',') + ']');
      }
    }

    super(listStore, list, router, locales, resourceStore, options);
    router.bind(options.property, this.toggleValue, false);
    listStore.options[options.property] = this.toggleValue.get()

    if (options.default && typeof options.default === 'boolean') {
      this.toggleValue.set(options.default)
    }
  }

  @computed get property() {
    return this.options.property;
  }

  @computed get label() {
    return translate(this.options.label);
  }

  getToolbarItemConfig() {
    if (this.listStore.loading) {
      return null;
    }

    return {
      type: 'toggler',
      onClick: this.handleTogglerClick,
      label: this.label,
      value: !!this.toggleValue.get(),
    };
  }

  @action handleTogglerClick = () => {

    this.toggleValue.set(!this.toggleValue.get())
    this.listStore.options[this.property] = this.toggleValue.get()
    this.listStore.reload()
  };
}