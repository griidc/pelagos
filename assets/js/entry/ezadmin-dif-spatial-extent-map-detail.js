(function () {
  function init() {
    const wrapper = document.getElementById('ea-dif-spatial-extent-map-readonly-wrapper');
    const mapElement = document.getElementById('ea-dif-spatial-extent-map-readonly');
    const statusElement = document.getElementById('ea-dif-spatial-extent-map-readonly-status');

    if (!wrapper || !mapElement || typeof L === 'undefined') {
      return;
    }

    const gml = wrapper.dataset.gml || '';

    if (!gml.trim()) {
      if (statusElement) {
        statusElement.textContent = 'No spatial extent geometry is set.';
      }
      return;
    }

    function setStatus(message, isError) {
      if (!statusElement) {
        return;
      }

      statusElement.textContent = message;
      statusElement.classList.toggle('is-error', Boolean(isError));
    }

    function splitTopLevel(text) {
      const chunks = [];
      let depth = 0;
      let start = 0;

      for (let i = 0; i < text.length; i += 1) {
        const ch = text[i];

        if (ch === '(') {
          depth += 1;
        } else if (ch === ')') {
          depth -= 1;
        } else if (ch === ',' && depth === 0) {
          chunks.push(text.slice(start, i).trim());
          start = i + 1;
        }
      }

      const tail = text.slice(start).trim();
      if (tail.length > 0) {
        chunks.push(tail);
      }

      return chunks;
    }

    function stripSingleParens(text) {
      const trimmed = text.trim();
      if (trimmed[0] === '(' && trimmed[trimmed.length - 1] === ')') {
        return trimmed.slice(1, -1).trim();
      }
      return trimmed;
    }

    function parseCoordinatePairs(text) {
      return text.split(',').map((pair) => {
        const points = pair.trim().split(/\s+/);
        return [parseFloat(points[1]), parseFloat(points[0])];
      });
    }

    function parseParenCollection(text) {
      return splitTopLevel(text).map((item) => stripSingleParens(item));
    }

    function parsePolygonRings(text) {
      return text.split(/\)\s*,\s*\(/).map((ringText) => parseCoordinatePairs(ringText));
    }

    function wktToLayer(wkt) {
      const value = (wkt || '').trim();

      let match = value.match(/^POINT\s*\(([^)]+)\)$/i);
      if (match) {
        const point = match[1].trim().split(/\s+/);
        return L.marker([parseFloat(point[1]), parseFloat(point[0])]);
      }

      match = value.match(/^MULTIPOINT\s*\((.+)\)$/i);
      if (match) {
        const points = parseParenCollection(match[1]);
        const markers = points.map((pointText) => {
          const point = pointText.trim().split(/\s+/);
          return L.marker([parseFloat(point[1]), parseFloat(point[0])]);
        });
        return L.featureGroup(markers);
      }

      match = value.match(/^LINESTRING\s*\((.+)\)$/i);
      if (match) {
        return L.polyline(parseCoordinatePairs(match[1]));
      }

      match = value.match(/^MULTILINESTRING\s*\((.+)\)$/i);
      if (match) {
        const lines = parseParenCollection(match[1]);
        const polylines = lines.map((lineText) => L.polyline(parseCoordinatePairs(lineText)));
        return L.featureGroup(polylines);
      }

      match = value.match(/^POLYGON\s*\(\((.+)\)\)$/i);
      if (match) {
        return L.polygon(parsePolygonRings(match[1]));
      }

      match = value.match(/^MULTIPOLYGON\s*\((.+)\)$/i);
      if (match) {
        const polygons = splitTopLevel(match[1]);
        const polygonLayers = polygons.map((polygonText) => {
          const polygonBody = polygonText.trim().replace(/^\(\(/, '').replace(/\)\)$/, '');
          return L.polygon(parsePolygonRings(polygonBody));
        });

        return L.featureGroup(polygonLayers);
      }

      return null;
    }

    const map = L.map(mapElement.id, {
      center: [27.5, -97.5],
      zoom: 3,
      minZoom: 2,
      worldCopyJump: true,
      scrollWheelZoom: false,
      dragging: true,
      doubleClickZoom: true,
      touchZoom: true,
      boxZoom: true,
      keyboard: false,
    });

    function getLayerBounds(layer) {
      if (!layer) {
        return null;
      }

      if (typeof layer.getBounds === 'function') {
        const bounds = layer.getBounds();
        if (bounds && typeof bounds.isValid === 'function' && bounds.isValid()) {
          return bounds;
        }
      }

      if (typeof layer.getLatLng === 'function') {
        const latlng = layer.getLatLng();
        return L.latLngBounds([latlng, latlng]);
      }

      return null;
    }

    function fitMapToLayer(layer) {
      const bounds = getLayerBounds(layer);

      if (!bounds || !bounds.isValid()) {
        return;
      }

      const southWest = bounds.getSouthWest();
      const northEast = bounds.getNorthEast();
      const isSinglePoint = southWest.equals(northEast);

      if (isSinglePoint) {
        map.setView(bounds.getCenter(), 10);
        return;
      }

      map.fitBounds(bounds, { padding: [20, 20] });
    }

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    fetch('/gmltowkt', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
      },
      body: new URLSearchParams({ gml }).toString(),
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error('Failed to convert GML geometry for map display.');
        }
        return response.json();
      })
      .then((payload) => {
        const layer = wktToLayer(payload.wkt || '');

        if (!layer) {
          throw new Error('Map rendering for this geometry type is not supported.');
        }

        if (layer instanceof L.FeatureGroup || layer instanceof L.LayerGroup || layer instanceof L.GeoJSON) {
          layer.eachLayer((childLayer) => childLayer.addTo(map));
        } else {
          layer.addTo(map);
        }

        fitMapToLayer(layer);

        setStatus('Displaying stored spatial extent geometry.', false);
      })
      .catch((error) => {
        setStatus(error.message, true);
      })
      .finally(() => {
        window.setTimeout(() => {
          map.invalidateSize();
        }, 100);
      });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
