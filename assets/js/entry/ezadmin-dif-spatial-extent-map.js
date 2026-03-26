(function () {
  function init() {
    const geometryField = document.querySelector('textarea[name$="[spatialExtentGeometry]"], textarea[id$="_spatialExtentGeometry"]');

    if (!geometryField || typeof L === 'undefined' || typeof L.Control.Draw === 'undefined') {
      return;
    }

    const geometryFieldContainer = geometryField.closest('.form-group, .field-codeEditor, .field-textarea, .ea-form-field') || geometryField.parentElement;
    const editForm = geometryField.closest('form');

    if (!geometryFieldContainer) {
      return;
    }

    if (document.getElementById('ea-dif-spatial-extent-map')) {
      return;
    }

    const mapWrapper = document.createElement('div');
    mapWrapper.className = 'ea-dif-geometry-map-wrapper';

    const mapTitle = document.createElement('div');
    mapTitle.className = 'ea-dif-geometry-map-title';
    mapTitle.textContent = 'Spatial Extent Geometry Map';

    const mapElement = document.createElement('div');
    mapElement.id = 'ea-dif-spatial-extent-map';
    mapElement.className = 'ea-dif-geometry-map';

    const mapHelp = document.createElement('p');
    mapHelp.className = 'ea-dif-geometry-map-help';
    mapHelp.textContent = 'Use the drawing controls to add a point, line, polygon, or rectangle. Only one geometry is stored.';

    const mapStatus = document.createElement('div');
    mapStatus.className = 'ea-dif-geometry-map-status';

    mapWrapper.appendChild(mapTitle);
    mapWrapper.appendChild(mapElement);
    mapWrapper.appendChild(mapHelp);
    mapWrapper.appendChild(mapStatus);

    geometryFieldContainer.appendChild(mapWrapper);

    geometryField.style.display = 'none';
    geometryField.setAttribute('aria-hidden', 'true');

    function getCodeMirrorInstance() {
      const sibling = geometryField.nextElementSibling;
      if (sibling && sibling.classList && sibling.classList.contains('CodeMirror') && sibling.CodeMirror) {
        return sibling.CodeMirror;
      }

      const inContainer = geometryFieldContainer.querySelector('.CodeMirror');
      if (inContainer && inContainer.CodeMirror) {
        return inContainer.CodeMirror;
      }

      return null;
    }

    const initialCodeMirrorElement = geometryField.nextElementSibling && geometryField.nextElementSibling.classList
      && geometryField.nextElementSibling.classList.contains('CodeMirror')
      ? geometryField.nextElementSibling
      : geometryFieldContainer.querySelector('.CodeMirror');

    if (initialCodeMirrorElement) {
      initialCodeMirrorElement.style.display = 'none';
    }

    let pendingSyncPromise = null;
    let submitRetryTimer = null;

    const map = L.map(mapElement.id, {
      center: [27.5, -97.5],
      zoom: 3,
      minZoom: 2,
      worldCopyJump: true,
    });

    function resetMapView() {
      map.setView([27.5, -97.5], 3);
    }

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
        resetMapView();
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

    const drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    const drawControl = new L.Control.Draw({
      position: 'topleft',
      draw: {
        circle: false,
        circlemarker: false,
        marker: true,
        polyline: true,
        polygon: true,
        rectangle: true,
      },
      edit: {
        featureGroup: drawnItems,
        remove: true,
      },
    });

    map.addControl(drawControl);

    function setStatus(message, isError) {
      mapStatus.textContent = message;
      mapStatus.classList.toggle('is-error', Boolean(isError));
      mapStatus.classList.toggle('is-ok', !isError && message.length > 0);
    }

    function updateField(gml) {
      geometryField.value = gml;

      const codeMirrorInstance = getCodeMirrorInstance();
      if (codeMirrorInstance) {
        codeMirrorInstance.setValue(gml);
        codeMirrorInstance.save();
      }

      geometryField.dispatchEvent(new Event('input', { bubbles: true }));
      geometryField.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function isSyncPending() {
      return pendingSyncPromise !== null;
    }

    function serializeLatLng(latlng) {
      return latlng.lng + ' ' + latlng.lat;
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

    function layerToWkt(layer) {
      if (layer instanceof L.Marker || layer instanceof L.CircleMarker) {
        return 'POINT(' + serializeLatLng(layer.getLatLng()) + ')';
      }

      if (layer instanceof L.FeatureGroup || layer instanceof L.GeoJSON || layer instanceof L.LayerGroup) {
        const markerWkts = [];
        const lineWkts = [];
        const polygonWkts = [];
        const allWkts = [];

        layer.eachLayer((childLayer) => {
          const childWkt = layerToWkt(childLayer);
          if (!childWkt) {
            return;
          }

          allWkts.push(childWkt);

          if (childWkt.startsWith('POINT(')) {
            markerWkts.push(childWkt.slice(6, -1));
          } else if (childWkt.startsWith('LINESTRING(')) {
            lineWkts.push(childWkt.slice(11, -1));
          } else if (childWkt.startsWith('POLYGON(')) {
            polygonWkts.push(childWkt.slice(8, -1));
          }
        });

        if (allWkts.length === 0) {
          return '';
        }

        if (markerWkts.length === allWkts.length) {
          return 'MULTIPOINT(' + markerWkts.map((pt) => '(' + pt + ')').join(',') + ')';
        }

        if (lineWkts.length === allWkts.length) {
          return 'MULTILINESTRING(' + lineWkts.map((ln) => '(' + ln + ')').join(',') + ')';
        }

        if (polygonWkts.length === allWkts.length) {
          return 'MULTIPOLYGON(' + polygonWkts.map((poly) => '(' + poly + ')').join(',') + ')';
        }

        return 'GEOMETRYCOLLECTION(' + allWkts.join(',') + ')';
      }

      if (layer instanceof L.Polygon) {
        const latlngs = layer.getLatLngs();

        if (!Array.isArray(latlngs) || latlngs.length === 0) {
          return '';
        }

        const firstValue = latlngs[0];
        const isMultiPolygon = Array.isArray(firstValue) && Array.isArray(firstValue[0]);

        if (isMultiPolygon) {
          const polygons = latlngs.map((polygonRings) => {
            const ringWkts = polygonRings.map((ring) => {
              const coords = ring.map(serializeLatLng);
              if (coords.length > 0 && coords[0] !== coords[coords.length - 1]) {
                coords.push(coords[0]);
              }
              return '(' + coords.join(',') + ')';
            });
            return '(' + ringWkts.join(',') + ')';
          });

          return 'MULTIPOLYGON(' + polygons.join(',') + ')';
        }

        const ringWkts = latlngs.map((ring) => {
          const coords = ring.map(serializeLatLng);
          if (coords.length > 0 && coords[0] !== coords[coords.length - 1]) {
            coords.push(coords[0]);
          }
          return '(' + coords.join(',') + ')';
        });

        return 'POLYGON(' + ringWkts.join(',') + ')';
      }

      if (layer instanceof L.Polyline) {
        const latlngs = layer.getLatLngs();

        if (!Array.isArray(latlngs) || latlngs.length === 0) {
          return '';
        }

        if (Array.isArray(latlngs[0])) {
          const lines = latlngs.map((line) => '(' + line.map(serializeLatLng).join(',') + ')');
          return 'MULTILINESTRING(' + lines.join(',') + ')';
        }

        const coords = latlngs.map(serializeLatLng);
        return 'LINESTRING(' + coords.join(',') + ')';
      }

      return '';
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

    function syncLayerToField() {
      const layers = drawnItems.getLayers();

      if (layers.length === 0) {
        updateField('');
        setStatus('Spatial extent geometry cleared.', false);
        resetMapView();
        return;
      }

      const wkt = layerToWkt(layers.length === 1 ? layers[0] : drawnItems);

      if (!wkt) {
        setStatus('Could not serialize the drawn geometry.', true);
        return;
      }

      pendingSyncPromise = fetch('/wkttogml', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body: new URLSearchParams({ wkt }).toString(),
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error('Failed to convert WKT to GML.');
          }
          return response.json();
        })
        .then((payload) => {
          if (!payload.gml) {
            throw new Error('The WKT to GML response did not include geometry.');
          }

          updateField(payload.gml);
          setStatus('Spatial extent geometry updated.', false);
        })
        .catch((error) => {
          setStatus(error.message, true);
        })
        .finally(() => {
          pendingSyncPromise = null;
        });

      return pendingSyncPromise;
    }

    function loadInitialGeometry() {
      const gml = geometryField.value.trim();

      if (!gml) {
        setStatus('No spatial extent geometry is set yet.', false);
        return;
      }

      fetch('/gmltowkt', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body: new URLSearchParams({ gml }).toString(),
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error('Failed to convert existing GML geometry.');
          }
          return response.json();
        })
        .then((payload) => {
          const layer = wktToLayer(payload.wkt || '');

          if (!layer) {
            setStatus('Existing geometry loaded in the field, but map rendering for this geometry type is not supported.', true);
            return;
          }

          drawnItems.clearLayers();
          if (layer instanceof L.FeatureGroup || layer instanceof L.LayerGroup || layer instanceof L.GeoJSON) {
            layer.eachLayer((childLayer) => {
              drawnItems.addLayer(childLayer);
            });
          } else {
            drawnItems.addLayer(layer);
          }

          if (drawnItems.getLayers().length > 0) {
            fitMapToLayer(drawnItems);
          }

          setStatus('Loaded existing spatial extent geometry.', false);
        })
        .catch((error) => {
          setStatus(error.message, true);
        });
    }

    map.on(L.Draw.Event.CREATED, (event) => {
      drawnItems.addLayer(event.layer);
      fitMapToLayer(drawnItems.getLayers().length === 1 ? event.layer : drawnItems);
      syncLayerToField();
    });

    map.on(L.Draw.Event.EDITED, () => {
      fitMapToLayer(drawnItems);
      syncLayerToField();
    });

    map.on(L.Draw.Event.DELETED, () => {
      if (drawnItems.getLayers().length > 0) {
        fitMapToLayer(drawnItems);
      } else {
        resetMapView();
      }
      syncLayerToField();
    });

    if (editForm) {
      editForm.addEventListener('submit', (event) => {
        if (!isSyncPending()) {
          return;
        }

        event.preventDefault();

        if (submitRetryTimer) {
          window.clearInterval(submitRetryTimer);
        }

        submitRetryTimer = window.setInterval(() => {
          if (isSyncPending()) {
            return;
          }

          window.clearInterval(submitRetryTimer);
          submitRetryTimer = null;
          editForm.requestSubmit();
        }, 100);
      });
    }

    setTimeout(() => {
      map.invalidateSize();
      loadInitialGeometry();
    }, 100);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
