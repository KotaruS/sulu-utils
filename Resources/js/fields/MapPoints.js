// @flow
import React from 'react';
import { computed, observable } from 'mobx';
import userStore from 'sulu-admin-bundle/stores/userStore';
import { observer } from 'mobx-react';
import MapPointsComponent from '../containers/MapPoints';

@observer
export default class MapPoints extends React.Component {
  static fallbackDefaultZoom;
  static fallbackDefaultCenter;

  handleChange = (value) => {
    const { onChange, onFinish } = this.props;

    onChange(value);
    onFinish();
  };
  @computed get centerLatLng() {
    const {
      schemaOptions: {
        center: {
          value: center = MapPoints.fallbackDefaultCenter.join(','),
        } = {},
      } = {}
    } = this.props;
    const coordinates = center.split(',');
    if (coordinates.length > 1) {
      const [lat, lng] = coordinates;
      return [isNaN(Number(lat)) ? 0 : Number(lat), isNaN(Number(lng)) ? 0 : Number(lng)];
    }
    return [0, 0];
  }

  @computed get defaultZoom() {
    const {
      schemaOptions: {
        default_zoom: {
          value: zoom = MapPoints.fallbackDefaultZoom,
        } = {},
      } = {}
    } = this.props;
    const defaultZoom = Number(zoom);
    if (isNaN(defaultZoom)) {
      return 1;
    }
    return defaultZoom;
  }

  render() {
    const {
      disabled,
      value,
    } = this.props;
    return (
      <MapPointsComponent
        disabled={!!disabled}
        locale={this.locale.get()}
        onChange={this.handleChange}
        center={this.centerLatLng}
        defaultZoom={this.defaultZoom}
        value={value}
      />
    );
  }

  @computed get locale() {
    const { formInspector } = this.props;

    return formInspector.locale ? formInspector.locale : observable.box(userStore.contentLocale);
  }
}
