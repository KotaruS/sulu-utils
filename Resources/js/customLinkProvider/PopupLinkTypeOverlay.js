// @flow
import React from 'react';
import { Dialog, Input, Form, SingleSelect } from 'sulu-admin-bundle/components';
import { SingleSelection } from 'sulu-admin-bundle/containers';
import { translate } from 'sulu-admin-bundle/utils';
import type { LinkTypeOverlayProps } from 'sulu-admin-bundle/containers/Link/types';

export default class PopupLinkTypeOverlay extends React.Component<LinkTypeOverlayProps> {
  render() {
    const {
      href,
      locale,
      onCancel,
      onConfirm,
      onTitleChange,
      onHrefChange,
      open,
      options,
      title,
    } = this.props;


    return (
      <Dialog
        cancelText={translate('sulu_admin.cancel')}
        confirmDisabled={!href}
        confirmText={translate('sulu_admin.confirm')}
        onCancel={onCancel}
        onConfirm={onConfirm}
        open={open}
        title={translate('app_admin.popup_link')}
      >
        <Form>
          <Form.Field label={translate('app_admin.popup_link')} required={true}>
            <SingleSelection
              adapter={'table'}
              displayProperties={options?.displayProperties ?? ['title']}
              emptyText={translate('app_admin.no_popup_selected')}
              icon={'su-snippet'}
              listKey={options?.resourceKey ?? 'snippets'}
              locale={locale}
              onChange={onHrefChange}
              listOptions={{ 'types': 'popup' }}
              overlayTitle={translate('app_admin.popups')}
              resourceKey={options?.resourceKey ?? 'snippets'}
              value={href}
            />
          </Form.Field>

          {onTitleChange &&
            <Form.Field label={translate('sulu_admin.link_title')}>
              <Input onChange={onTitleChange} value={title} />
            </Form.Field>
          }

        </Form>
      </Dialog>
    );
  }
}
