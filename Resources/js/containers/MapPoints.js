// @flow
import React from 'react';
import { observer } from 'mobx-react';
import { action, observable, computed, toJS } from 'mobx';
import { CroppedText, Icon, MultiItemSelection } from 'sulu-admin-bundle/components';
import { translate, arrayMove } from 'sulu-admin-bundle/utils';
import { MapContainer, Marker, TileLayer, Tooltip } from 'react-leaflet';
import { Map, latLngBounds } from 'leaflet';
import equals from 'fast-deep-equal';
import classNames from 'classnames';
import mapStyles from './map.scss';
import MapPointsOverlay from './MapPointsOverlay';
import NumberedIcon from '../components/NumberedIcon';
import { latitudeToWGS84, longitudeToWGS84 } from '../utils';

@observer
class MapPoints extends React.Component {
  @observable overlayOpen = false;

  map;

  constructor(props) {
    super(props);
  }


  componentDidUpdate(prevProps) {
    const prevValue = toJS(prevProps.value);
    const newValue = toJS(this.props.value);
    if (!equals(prevValue, newValue) && newValue && this.map) {
      const latitudes = newValue.points.map(p => p.lat ?? this.props.center[0] ?? 0)
      const longitudes = newValue.points.map(p => p.long ?? this.props.center[1] ?? 0)
      const southWest = [Math.min(...latitudes), Math.min(...longitudes)]
      const northEast = [Math.max(...latitudes), Math.max(...longitudes)]
      const bounds = latLngBounds(southWest, northEast)
      this.map.fitBounds(bounds, { maxZoom: newValue.zoom ?? this.props.defaultZoom ?? 10, padding: [12, 12] });
      // this.map.setView([newValue.lat || 0, newValue.long || 0], newValue.zoom || 1);
    }
  }

  setLeafletMap = (map) => {
    this.map = map;
  };

  @action handleEditButtonClick = () => {
    this.overlayOpen = true;
  };

  @action handleOverlayConfirm = (newValue) => {
    this.overlayOpen = false;
    this.props.onChange(newValue);
  };

  @action handleOverlayClose = () => {
    this.overlayOpen = false;
  };

  @action handlePointSort = (oldItemIndex, newItemIndex) => {
    const { value, onChange } = this.props
    if (value?.points) {
      const newPoints = arrayMove(value.points, oldItemIndex, newItemIndex);
      onChange({ points: newPoints, zoom: value.zoom });
    }
  };

  @computed get mapCenter() {
    if (this.props.value?.points) {
      const points = toJS(this.props.value.points)
      const latitudes = points.map(p => p.lat ?? this.props.center[0] ?? 0)
      const longitudes = points.map(p => p.long ?? this.props.center[1] ?? 0)
      const southWest = [Math.min(...latitudes), Math.min(...longitudes)]
      const northEast = [Math.max(...latitudes), Math.max(...longitudes)]
      const bounds = latLngBounds(southWest, northEast)
      return bounds.getCenter()
    }
    return this.props?.center ?? [0, 0]
  }

  render() {
    const {
      disabled,
      value,
      center,
      defaultZoom,
      locale,
    } = this.props;

    const locationClass = classNames(
      mapStyles.mapPointsContainer,
      {
        [mapStyles.disabled]: disabled,
      }
    );

    return (
      <div className={locationClass}>

        <MultiItemSelection
          label={translate('sulu_utils.select_points')}
          leftButton={{
            icon: 'su-map-pin',
            onClick: this.handleEditButtonClick,
          }}
          onItemsSorted={this.handlePointSort}
        >
          {value && value.points.map((point, index) =>
            <MultiItemSelection.Item
              key={`item-${index}`}
              index={index + 1}
            >
              <div style={{ height: '40px', lineHeight: '40px' }}>
                <CroppedText>
                  {(point.title ? point.title + ', ' : '')
                    + (latitudeToWGS84(point.lat ?? center[0] ?? 0) ?? '–') + ', '
                    + (longitudeToWGS84(point.long ?? center[1] ?? 0) ?? '–')}
                </CroppedText>
              </div>
            </MultiItemSelection.Item>
          )}
        </MultiItemSelection>
        {value &&
          <MapContainer
            attributionControl={false}
            center={this.mapCenter}
            className={mapStyles.mapPointsMap}
            doubleClickZoom={false}
            padding={[12, 12]}
            dragging={false}
            keyboard={false}
            scrollWheelZoom={false}
            tap={false}
            whenCreated={this.setLeafletMap}
            zoom={value.zoom}
            zoomControl={false}
          >
            <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
            {value.points ?
              value.points.map((point, i) =>
                <Marker
                  interactive={false}
                  icon={NumberedIcon.icon({
                    number: i + 1,
                  })}
                  key={`marker-${i}`}
                  position={[point.lat ?? center[0] ?? 0, point.long ?? center[1]]}>
                </Marker>
              )
              : null}
          </MapContainer>
        }
        <MapPointsOverlay
          locale={locale}
          onClose={this.handleOverlayClose}
          onConfirm={this.handleOverlayConfirm}
          open={this.overlayOpen}
          center={center}
          defaultZoom={defaultZoom}
          value={value}
        />
      </div>
    );
  }
}

export default MapPoints;
