// @flow
import React from 'react';
import { computed, observable } from 'mobx';
import userStore from 'sulu-admin-bundle/stores/userStore';
import { observer } from 'mobx-react';
import LocationComponent from '../containers/Location';
import type { FieldTypeProps } from 'sulu-admin-bundle/types';
import type { Location as LocationValue } from 'sulu-location-bundle/types';
import type { IObservableValue } from 'mobx/lib/mobx';

@observer
export default class Location extends React.Component<FieldTypeProps<?LocationValue>> {
    static fallbackDefaultZoom;
    static fallbackDefaultCenter;

    handleChange = (value: ?LocationValue) => {
        const { onChange, onFinish } = this.props;

        onChange(value);
        onFinish();
    };
    @computed get centerLatLng() {
        const {
            schemaOptions: {
                center: {
                    value: center = Location.fallbackDefaultCenter.join(','),
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
                    value: zoom = Location.fallbackDefaultZoom,
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
            <LocationComponent
                disabled={!!disabled}
                locale={this.locale.get()}
                onChange={this.handleChange}
                center={this.centerLatLng}
                defaultZoom={this.defaultZoom}
                value={value}
            />
        );
    }

    @computed get locale(): IObservableValue<string> {
        const { formInspector } = this.props;

        return formInspector.locale ? formInspector.locale : observable.box(userStore.contentLocale);
    }
}
