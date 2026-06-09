// @flow
import React from 'react';
import { action, observable, computed, reaction, toJS, } from 'mobx';
import { observer } from 'mobx-react';
import { Button, Form, Input, Number, Overlay } from 'sulu-admin-bundle/components';
import { translate, arrayMove } from 'sulu-admin-bundle/utils';
import { MapContainer, Marker, Popup, TileLayer } from 'react-leaflet';
import { Map, latLngBounds } from 'leaflet';
import { SingleAutoComplete } from 'sulu-admin-bundle/containers';
import { SingleSelectionStore, MultiSelectionStore } from 'sulu-admin-bundle/stores';
import mapOverlayStyles from './mapOverlay.scss';
import NumberedIcon from '../components/NumberedIcon';

@observer
class MapPointsOverlay extends React.Component {
  @observable points = [];
  @observable zoom;
  @observable index = 0;
  @observable markers = [];
  @observable icons = [];

  /** @type {typeof Map|null} */
  map;

  geolocatorSelectionStore;
  /** @type {()=>*} */
  updateDataOnGeolocatorSelectDisposer;
  /** @type {()=>*} */
  updateDataOnOpenDisposer;

  constructor(props) {
    super(props);

    this.geolocatorSelectionStore = new SingleSelectionStore(
      'geolocator_locations',
      undefined,
      observable.box(props.locale)
    );


    this.updateDataOnGeolocatorSelectDisposer = reaction(
      () => this.geolocatorSelectionStore.item,
      this.handleAutoCompleteChange
    );


    this.updateDataOnOpenDisposer = reaction(() => this.props.open, (newOpenValue) => {
      if (newOpenValue === true) {
        this.initMarkers()
        this.initIcons()
        this.initPoints()

        this.zoom = this.props.value ? this.props.value.zoom : this.defaultZoom;

        // give it time to insert map into dom
        setTimeout(() => {
          this.setIndex(0)
          this.updateMapToData();
        }, 20);
      }
    }, { fireImmediately: true });
  }

  componentWillUnmount() {
    this.updateDataOnGeolocatorSelectDisposer();
    this.updateDataOnOpenDisposer();
  }
  @action initMarkers() {
    const refs = [];
    for (let i = 0; i < (this.props.value?.points ?? ['']).length; i++) {
      refs.push(React.createRef(null));
    }
    this.markers = refs;
  }
  @action initIcons() {
    const icons = [];
    for (let i = 0; i < (this.props.value?.points ?? ['']).length; i++) {
      icons.push(this.createIcon(i));
    }
    this.icons = icons;
  }
  @action initPoints() {
    this.points = this.props.value ? toJS(this.props.value.points) : [{
      lat: null,
      long: null,
      title: null,
      street: null,
      number: null,
      code: null,
      town: null,
      country: null,
    }]
  }
  setLeafletMap = (map) => {
    map.on('zoomanim', this.handleMapZoom);
    map.on('click', this.handleMapClick);
    this.map = map;
  };

  updateMapToCurrentMarker = () => {
    if (this.map) {
      this.map.setView([this.lat ?? this.center[0] ?? 0, this.long ?? this.center[1] ?? 0], this.zoom || this.defaultZoom);
    }
  };
  updateMapToData = () => {
    const latitudes = this.points.map(p => p.lat ?? this.center[0] ?? 0)
    const longitudes = this.points.map(p => p.long ?? this.center[1] ?? 0)
    const southWest = [Math.min(...latitudes), Math.min(...longitudes)]
    const northEast = [Math.max(...latitudes), Math.max(...longitudes)]
    const bounds = latLngBounds(southWest, northEast)

    if (this.map) {
      /** we must check that map is in the DOM
       *  @see https://github.com/PaulLeCam/react-leaflet/issues/1136
       * */
      if (document.contains(this.map.getContainer())) {
        this.map.fitBounds(bounds, { maxZoom: this.zoom ?? this.defaultZoom, padding: [12, 12] });
      } else {
        setTimeout(() => {
          this.map.fitBounds(bounds, { maxZoom: this.zoom ?? this.defaultZoom, padding: [12, 12] });
        }, 20);
      }
    }
  };
  @action setIndex(idx) {
    for (let i = 0; i < this.markers.length; i++) {
      const refValue = this.markers[i].current
      if (refValue) {
        const markerEl = refValue.getElement();
        markerEl.classList.remove(mapOverlayStyles.activeMarker);
      }
    }
    const ref = this.markers[idx]
    if (ref.current !== null) {
      const marker = ref.current.getElement();
      marker.classList.add(mapOverlayStyles.activeMarker);
    }
    this.index = idx

  }

  handleConfirm = () => {
    const { onConfirm } = this.props;
    const { points, zoom } = this;

    if (points.length === 0) {
      onConfirm({
        points: null,
        zoom: zoom ?? 10
      });

      return;
    }

    onConfirm({
      points: points.map((p) => ({
        long: p?.long ?? null,
        lat: p?.lat ?? null,
        title: p?.title ?? null,
        street: p?.street ?? null,
        number: p?.number ?? null,
        code: p?.code ?? null,
        town: p?.town ?? null,
        country: p?.country ?? null,
      })),
      zoom: zoom ?? this.defaultZoom,
    });
  };

  createIcon(index) {
    return NumberedIcon.icon({
      number: index + 1,
    })
  }

  @action handleSorted = (oldItemIndex, newItemIndex) => {
    this.points = arrayMove(this.points, oldItemIndex, newItemIndex);
  };

  @action handleAutoCompleteChange = (data) => {
    if (!data) {
      return;
    }
    const point = toJS(this.points[this.index]) ?? {}
    point.lat = data.latitude;
    point.long = data.longitude;
    this.updateMapToCurrentMarker();

    point.title = data.displayTitle;
    point.street = data.street;
    point.number = data.number;
    point.code = data.code;
    point.town = data.town;
    point.country = data.country;
    this.markers[this.index] ??= React.createRef(null);
    this.icons[this.index] ??= this.createIcon(Math.min(this.index, this.points.length));
    this.points[Math.min(this.index, this.points.length)] = point
    this.setIndex(Math.min(this.index, this.points.length))
  };

  @action handleMapZoom = (event) => {
    this.zoom = event.zoom;
  };

  @action handleMarkerDrag = (event) => {
    this.points[this.index].long = event.latlng.lng;
    this.points[this.index].lat = event.latlng.lat;
  };

  @action handleMarkerDragEnd = () => {
    this.updateMapToCurrentMarker();
  };

  @action handleMarkerDragStart = (event, index) => {
    this.setIndex(index)
  };

  @action handleMarkerClick = (index) => {
    this.setIndex(index)
  };

  removeMarker = (index) => {
    setTimeout(action(() => {
      if (this.points.length > 1) {
        this.points.splice(index, 1)
        this.markers.splice(index, 1)
        this.icons.pop()
        if (index === this.points.length) {
          this.setIndex(index - 1)
        }
      } else {
        this.points = [{
          long: null,
          lat: null,
          title: null,
          street: null,
          number: null,
          code: null,
          town: null,
          country: null,
        }];
        this.markers = [React.createRef(null)]
        this.icons = [this.createIcon(0)]
        this.setIndex(0);
      }
    }), 20);
  }

  @action handleMapClick = (event) => {
    this.markers.push(React.createRef(null));
    this.points.push({
      lat: event.latlng.lat,
      long: event.latlng.lng,
      title: null,
      street: null,
      number: null,
      code: null,
      town: null,
      country: null,
    });
    const index = this.points.length - 1;
    this.icons.push(this.createIcon(index));
    setTimeout(() => {
      this.setIndex(index)
      this.updateMapToData();
    }, 20);
  };

  @action handleResetLocation = () => {
    this.points = [{
      long: null,
      lat: null,
      title: null,
      street: null,
      number: null,
      code: null,
      town: null,
      country: null,
    }];
    this.markers = [React.createRef(null)]
    this.icons = [this.createIcon(0)]
    this.setIndex(0);
    this.points[this.index].long = null;
    this.points[this.index].lat = null;
    this.updateMapToData();

    this.points[this.index].title = null;
    this.points[this.index].street = null;
    this.points[this.index].number = null;
    this.points[this.index].code = null;
    this.points[this.index].town = null;
    this.points[this.index].country = null;
  };

  @computed get center() {
    return this.props.center ?? [0, 0];
  }
  @computed get defaultZoom() {
    return this.props.defaultZoom ?? 1;
  }
  @computed get order() {
    return this.index + 1;
  }
  @computed get lat() {
    if (this.points.length === 0) { return null; }
    return this.points[this.index]?.lat ?? this.center[0] ?? null;
  }
  @computed get long() {
    if (this.points.length === 0) { return null; }
    return this.points[this.index]?.long ?? this.center[1] ?? null;
  }
  @computed get title() {
    if (this.points.length === 0) { return null; }
    return this.points[this.index]?.title ?? null;
  }
  @computed get street() {
    if (this.points.length === 0) { return null; }
    return this.points[this.index]?.street ?? null;
  }
  @computed get number() {
    if (this.points.length === 0) { return null; }
    return this.points[this.index]?.number ?? null;
  }
  @computed get code() {
    if (this.points.length === 0) { return null; }
    return this.points[this.index]?.code ?? null;
  }
  @computed get town() {
    if (this.points.length === 0) { return null; }
    return this.points[this.index]?.town ?? null;
  }
  @computed get country() {
    if (this.points.length === 0) { return null; }
    return this.points[this.index]?.country ?? null;
  }

  @action handleTitleChange = (title) => {
    this.points[this.index].title = title;
  };

  @action handleStreetChange = (street) => {
    this.points[this.index].street = street;
  };

  @action handleNumberChange = (number) => {
    this.points[this.index].country = number;
  };

  @action handleCodeChange = (code) => {
    this.points[this.index].code = code;
  };

  @action handleTownChange = (town) => {
    this.points[this.index].town = town;
  };

  @action handleCountryChange = (country) => {
    this.points[this.index].country = country;
  };

  @action handleLatChange = (lat) => {
    this.points[this.index].lat = lat;
    this.updateMapToCurrentMarker();
  };

  @action handleLongChange = (long) => {
    this.points[this.index].long = long;
    this.updateMapToCurrentMarker();
  };

  @action handleZoomChange = (zoom) => {
    this.zoom = zoom || 1;
    this.updateMapToCurrentMarker();
  };

  @action handleOrderChange = (index) => {
    const newIndex = Math.max(0, Math.min(this.points.length - 1, index - 1))
    this.markers[this.index].current.closePopup()
    if (newIndex !== this.index) {
      this.handleSorted(this.index, newIndex)
      this.setIndex(newIndex)
      this.updateMapToCurrentMarker();
    }
  };


  render() {
    const {
      onClose,
      open,
    } = this.props;

    // enable confirm button if all marker properties are set or no property is set in case of a reset
    const confirmEnabled = (this.lat !== null && this.long !== null)
      || (this.lat === null && this.long === null);

    return (
      <Overlay
        actions={[
          {
            title: translate('sulu_admin.reset'),
            onClick: this.handleResetLocation,
          },
        ]}
        confirmDisabled={!confirmEnabled}
        confirmText={translate('sulu_admin.confirm')}
        onClose={onClose}
        onConfirm={this.handleConfirm}
        open={open}
        size="small"
        title={translate('sulu_utils.map_points.overlay_title')}
      >
        <div className={mapOverlayStyles.container}>
          <Form>
            <Form.Field>
              <SingleAutoComplete
                displayProperty="displayTitle"
                searchProperties={['displayTitle']}
                selectionStore={this.geolocatorSelectionStore}
              />
            </Form.Field>

            <Form.Field>
              <MapContainer
                attributionControl={false}
                center={this.center}
                className={mapOverlayStyles.map}
                whenCreated={this.setLeafletMap}
                zoom={this.zoom}
              >
                <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
                {this.points.length > 0 ? this.points.map((point, idx) =>
                  <Marker
                    draggable={true}
                    ref={this.markers[idx]}
                    key={`marker-${idx}`}
                    icon={this.icons[idx]}
                    eventHandlers={{
                      drag: this.handleMarkerDrag,
                      click: () => this.handleMarkerClick(idx),
                      dragstart: (e) => this.handleMarkerDragStart(e, idx),
                      dragend: this.handleMarkerDragEnd,
                    }}
                    position={[point?.lat || this.center[0], point.long || this.center[1]]}
                  >
                    <Popup
                      autoClose={true}
                      minWidth={0}
                      className={mapOverlayStyles.popup}
                      closeButton={false}
                    >
                      <Button
                        className={mapOverlayStyles.deleteButton}
                        skin="icon"
                        icon="su-trash-alt"
                        value={idx}
                        onClick={this.removeMarker} />
                    </Popup>
                  </Marker>

                ) : ''
                }
              </MapContainer>
              <p className={mapOverlayStyles.infoText}>{translate('sulu_utils.map_points.info_text')}</p>
            </Form.Field>
            <Form.Field colSpan={6} label={translate('sulu_location.latitude')} required={true}>
              <Number onChange={this.handleLatChange} step={0.001} value={this.lat} />
            </Form.Field>
            <Form.Field colSpan={6} label={translate('sulu_location.longitude')} required={true}>
              <Number onChange={this.handleLongChange} step={0.001} value={this.long} />
            </Form.Field>
            <Form.Field colSpan={6} label={translate('sulu_location.zoom')} required={true}>
              <Number max={18} min={0} onChange={this.handleZoomChange} value={this.zoom} />
            </Form.Field>
            <Form.Field colSpan={6} label={translate('sulu_utils.points_order')} required={true}>
              <Number max={this.points.length} min={1} onChange={this.handleOrderChange} value={this.order} />
            </Form.Field>

            <Form.Section label={translate('sulu_utils.point_details')}>
              <Form.Field label={translate('sulu_location.title')}>
                <Input onChange={this.handleTitleChange} value={this.title} />
              </Form.Field>
              <Form.Field colSpan={6} label={translate('sulu_location.street')}>
                <Input onChange={this.handleStreetChange} value={this.street} />
              </Form.Field>
              <Form.Field colSpan={6} label={translate('sulu_location.number')}>
                <Input onChange={this.handleNumberChange} value={this.number} />
              </Form.Field>
              <Form.Field colSpan={6} label={translate('sulu_location.code')}>
                <Input onChange={this.handleCodeChange} value={this.code} />
              </Form.Field>
              <Form.Field colSpan={6} label={translate('sulu_location.town')}>
                <Input onChange={this.handleTownChange} value={this.town} />
              </Form.Field>
              <Form.Field label={translate('sulu_location.country')}>
                <Input onChange={this.handleCountryChange} value={this.country} />
              </Form.Field>
            </Form.Section>
          </Form>
        </div>
      </Overlay>
    );
  }
}

export default MapPointsOverlay;
