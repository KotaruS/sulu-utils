// @flow
import React from 'react';
import { action, computed, toJS, observable, isArrayLike } from 'mobx';
import PublishIndicator from 'sulu-admin-bundle/components/PublishIndicator';
import { observer } from 'mobx-react';
import { withToolbar } from 'sulu-admin-bundle/containers/Toolbar';
import { Form } from 'sulu-admin-bundle/views'

@observer
class GenericForm extends Form {

  @computed get id() {
    return '-';
  }
  createResourceFormStore = () => {
    const { resourceStore, router } = this.props;
    const {
      route: {
        options: {
          idQueryParameter,
        },
      },
    } = router;

    if (!resourceStore) {
      throw new Error(
        'The view "Form" needs a resourceStore to work properly.'
        + 'Did you maybe forget to make this view a child of a "ResourceTabs" view?'
      );
    }

    if (this.hasOwnResourceStore) {
      let locale = resourceStore.locale;
      if (!locale && this.locales) {
        locale = observable.box();
      }

      if (idQueryParameter) {
        this.resourceStore = new ResourceStore(
          this.resourceKey,
          this.id,
          { locale },
          this.formStoreOptions,
          idQueryParameter
        );
      } else {
        this.resourceStore = new ResourceStore(this.resourceKey, this.id, { locale }, this.formStoreOptions);
      }
    } else {
      this.resourceStore = resourceStore;
    }

    this.resourceFormStore = resourceFormStoreFactory.createFromResourceStore(
      this.resourceStore,
      this.formKey,
      this.formStoreOptions,
      this.metadataOptions
    );

    if (this.resourceStore.locale) {
      router.bind('locale', this.resourceStore.locale);
    }
  };
}

export default withToolbar(GenericForm, function () {
  const { router } = this.props;
  const {
    route: {
      options: {
        backView,
      },
    },
  } = router;
  const { errors, resourceStore, showSuccess } = this;

  const backButton = backView
    ? {
      onClick: this.navigateBack,
    }
    : undefined;
  const locale = this.locales
    ? {
      value: resourceStore.locale.get(),
      onChange: (locale) => {
        router.navigate(router.route.name, { ...router.attributes, locale });
      },
      options: this.locales.map((locale) => ({
        value: locale,
        label: locale,
      })),
    }
    : undefined;

  const items = this.toolbarActions
    .map((toolbarAction) => toolbarAction.getToolbarItemConfig())
    .filter((item) => item != null);

  const icons = [];
  const formData = this.resourceFormStore.data;

  if (formData.hasOwnProperty('publishedState') || formData.hasOwnProperty('published')) {
    const { publishedState, published } = formData;
    icons.push(
      <PublishIndicator
        draft={publishedState === undefined ? false : !publishedState}
        key="publish"
        published={published === undefined ? false : !!published}
      />
    );
  }

  const warnings = [];
  if (this.collaborationStore && this.collaborationStore.collaborations.length > 0) {
    warnings.push([
      translate('sulu_admin.form_used_by'),
      this.collaborationStore.collaborations.map((collaboration) => collaboration.fullName).join(', '),
    ].join(' '));
  }

  return {
    backButton,
    errors,
    locale,
    items,
    icons,
    showSuccess,
    warnings,
  };
});