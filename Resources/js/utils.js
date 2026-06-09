
export function toWGS84(coords) {
  let lng, lat
  if (coords instanceof Array) {
    let [lng, lat] = coords
  } else {
    if (Object.hasOwn(coords, 'x')) {
      lng = coords.x
    } else if (Object.hasOwn(coords, 'lng')) {
      lng = coords.lng
    }
    if (Object.hasOwn(coords, 'y')) {
      lat = coords.y
    } else if (Object.hasOwn(coords, 'lat')) {
      lat = coords.lng
    }
  }
  if (lng === undefined || lat === undefined) {
    throw new Error("Provided invalid coordinates, they must be in one of the formats [lng,lat] or {x:lng,y:lat} or {lng:lng,lat:lat}");
  }
  const latitude = Number(lat).toPrecision(7);
  const longitude = Number(lng).toPrecision(7);
  return [
    longitude > 0 ? longitude + 'W' : Math.abs(longitude) + 'E',
    latitude > 0 ? latitude + 'N' : Math.abs(latitude) + 'S'
  ];
}
export function longitudeToWGS84(lng) {
  const longitude = Number(lng).toPrecision(7);
  if (isNaN(Number(lng))) {
    return lng
  }
  if (lng === 0) { return lng }
  return longitude > 0 ? longitude + 'W' : Math.abs(longitude) + 'E';
}
export function latitudeToWGS84(lat) {
  const latitude = Number(lat).toPrecision(7);
  if (isNaN(Number(lat))) {
    return lat
  }
  if (lat === 0) { return lat }
  return latitude > 0 ? latitude + 'N' : Math.abs(latitude) + 'S';
}

export function clamp(value, min, max) {
  return Math.min(max, Math.max(value, min))
}

export function uuid() {
  return Math.random().toString(36).substring(5) + (new Date()).getTime().toString(36);
}
