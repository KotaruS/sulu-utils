// @flow
import React from 'react';
import { Dialog, Input, Form, SingleSelect } from 'sulu-admin-bundle/components';
import { translate } from 'sulu-admin-bundle/utils';
import type { LinkTypeOverlayProps } from 'sulu-admin-bundle/containers/Link/types';

export default class LocalLinkTypeOverlay extends React.Component<LinkTypeOverlayProps> {
  render() {
    const {
      href,
      onCancel,
      onConfirm,
      onTitleChange,
      onHrefChange,
      open,
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
        title={translate('sulu_admin.link')}
      >
        <Form>
          <Form.Field label={translate('app_admin.link_hash')} required={true}>
            <Input
              onChange={onHrefChange}
              icon="fa-hashtag"
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
