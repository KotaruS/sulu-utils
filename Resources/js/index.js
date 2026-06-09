// Add project specific javascript code and import of additional bundles here:
import { when } from 'mobx';
import './styles/ckeditor-theme.css';
import { translate } from 'sulu-admin-bundle/utils';
import { initializer } from 'sulu-admin-bundle/services';
import { fieldRegistry, viewRegistry } from 'sulu-admin-bundle/containers';
import { blockPreviewTransformerRegistry, StringBlockPreviewTransformer } from 'sulu-admin-bundle/containers/FieldBlocks';
// custom fields
import Range from './fields/Range';
import TextLineAutocomplete from './fields/TextLineAutocomplete';
import MapPoints from './fields/MapPoints';
import Location from './fields/Location';
import ColorPicker from './fields/ColorPicker';

import leaflet from 'leaflet';
import leafletMarkerIcon from 'leaflet/dist/images/marker-icon.png';
import leafletMarkerIconRetina from 'leaflet/dist/images/marker-icon-2x.png';
import leafletMarkerShadow from 'leaflet/dist/images/marker-shadow.png';

import GenericForm from './views/GenericForm';
import GenericResourceTabs from './views/GenericResourceTabs';

import TogglerToolbarAction from './views/list/toolbarActions/TogglerToolbarAction';
import { listToolbarActionRegistry } from 'sulu-admin-bundle/views/List';

// Link type
import linkTypeRegistry from 'sulu-admin-bundle/containers/Link/registries/linkTypeRegistry';
import LocalLinkTypeOverlay from './customLinkProvider/LocalLinkTypeOverlay';

// ckeditor
import { ckeditorConfigRegistry } from 'sulu-admin-bundle/containers';

const CONFIG_VIEW = "sulu_utils.generic_form";
const CONFIG_RESOURCE_VIEW = "sulu_utils.generic_resource_tabs";

initializer.addUpdateConfigHook('sulu_utils', (config, initialized) => {
  if (initialized) {
    return;
  }
  linkTypeRegistry.add('local', LocalLinkTypeOverlay, translate('sulu_utils.local_link'));

  viewRegistry.add(CONFIG_VIEW, GenericForm);
  viewRegistry.add(CONFIG_RESOURCE_VIEW, GenericResourceTabs, { disableDefaultSpacing: true });

  listToolbarActionRegistry.add('sulu_admin.toggler', TogglerToolbarAction);

  ckeditorConfigRegistry.add((function (ckConfig) {
    return {
      style: {
        definitions: [
          ...ckConfig.style?.definitions ?? [],
          ...Array.from(this.styles ?? []).map((style) => {
            const styleDef = { ...style }
            styleDef.name = translate(style.label);
            delete styleDef.label
            return styleDef
          })
        ]
      },
    }
  }).bind(config));

  MapPoints.fallbackDefaultZoom = config.location.default_zoom;
  MapPoints.fallbackDefaultCenter = config.location.default_center;
  Location.fallbackDefaultZoom = config.location.default_zoom;
  Location.fallbackDefaultCenter = config.location.default_center;

})


fieldRegistry.add('range', Range);
fieldRegistry.add('text_line_autocomplete', TextLineAutocomplete);
blockPreviewTransformerRegistry.add('text_line_autocomplete', new StringBlockPreviewTransformer());

fieldRegistry.add('map_points', MapPoints);
delete leaflet.Icon.Default.prototype._getIconUrl;
leaflet.Icon.Default.mergeOptions({
  iconUrl: leafletMarkerIcon,
  iconRetinaUrl: leafletMarkerIconRetina,
  shadowUrl: leafletMarkerShadow,
});
delete fieldRegistry.fields['location']
fieldRegistry.add('location', Location);
