import { Icon, Browser } from 'leaflet';
import leafletMarkerIcon from 'leaflet/dist/images/marker-icon.png';
import leafletMarkerIconRetina from 'leaflet/dist/images/marker-icon-2x.png';
import leafletMarkerShadow from 'leaflet/dist/images/marker-shadow.png';
import './numberedIcon.css'
const NumberedIcon = L.NumberedIcon = {}
NumberedIcon.Icon = L.NumberedIcon.Icon = Icon.extend({
  options: {
    iconUrl: leafletMarkerIcon,
    iconRetinaUrl: leafletMarkerIconRetina,
    shadowUrl: leafletMarkerShadow,
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    tooltipAnchor: [16, -28],
    shadowSize: [41, 41],
    className: "",
    number: "",
  },
  initialize: function (options) {
    options = L.Util.setOptions(this, options);
  },
  createIcon: function (oldIcon) {
    const div = document.createElement("div");
    const options = this.options;
    const img = this._createIcon('icon', oldIcon);
    div.appendChild(img)
    if (options.number !== "" || options.number !== undefined || options.number !== null) {
      const number = document.createElement("div");
      number.className = 'marker-number';
      div.appendChild(number);
      number.innerText = options.number;
    }
    this._setIconStyles(div, 'icon');
    return div;
  },
  createShadow: function (oldIcon) {
    var div = document.createElement("div");
    const img = this._createIcon('shadow', oldIcon)
    div.appendChild(img)
    this._setIconStyles(div, "shadow");
    return div;
  },
  _createIcon: function (name, oldIcon) {
    var src = this._getIconUrl(name);

    if (!src) {
      if (name === 'icon') {
        throw new Error('iconUrl not set in Icon options (see the docs).');
      }
      return null;
    }

    var img = this._createImg(src, oldIcon && oldIcon.tagName === 'IMG' ? oldIcon : null);

    if (this.options.crossOrigin || this.options.crossOrigin === '') {
      img.crossOrigin = this.options.crossOrigin === true ? '' : this.options.crossOrigin;
    }

    return img;
  },
  _createImg: function (src, el) {
    el = el || document.createElement('img');
    el.src = src;
    el.style.width = "100%"
    el.style.height = "100%"
    return el;
  },
  _setIconStyles: function (img, name) {
    const options = this.options
    const size = L.point(options[name === "shadow" ? "shadowSize" : "iconSize"])
    let anchor, leafletName;
    if (name === "shadow") {
      anchor = L.point(options.shadowAnchor || options.iconAnchor);
      leafletName = "shadow";
    } else {
      anchor = L.point(options.iconAnchor);
      leafletName = "icon";
    }
    if (!anchor && size) {
      anchor = size.divideBy(2, true);
    }
    img.className = "leaflet-marker-" + name + " numbered-marker numbered-marker-" + name + " " + options.className;
    if (anchor) {
      img.style.marginLeft = -anchor.x + "px";
      img.style.marginTop = -anchor.y + "px";
    }
    if (size) {
      img.style.width = size.x + "px";
      img.style.height = size.y + "px";
    }
  },

  _getIconUrl: function (name) {
    return Browser.retina && this.options[name + 'RetinaUrl'] || this.options[name + 'Url'];
  },

});
NumberedIcon.icon = L.NumberedIcon.icon = function (options) {
  return new L.NumberedIcon.Icon(options);
};
export default NumberedIcon;