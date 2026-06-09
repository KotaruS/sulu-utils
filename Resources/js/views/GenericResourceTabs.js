// @flow
import React from 'react';
import { autorun, computed, observable, toJS } from 'mobx';
import { observer } from 'mobx-react';
import jexl from 'jexl';
import { Route } from 'sulu-admin-bundle/services/Router';
import { ResourceTabs } from 'sulu-admin-bundle/views'

@observer
class GenericResourceTabs extends ResourceTabs {

  @computed get id() {
    return '-';
  }

}

export default GenericResourceTabs 