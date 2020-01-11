/**
 * 
 */

const https = require('https');
const io = require('./IO');
const keys = require('./KEYS');
const simplify = require("./simplify.js");

const BASE_URL = "https://tigerweb.geo.census.gov/arcgis/rest/services/";
const MAP_STATES = "TIGERweb/State_County";
const MAP_LEGISLATIVE = "TIGERweb/Legislative";

const LAYER_STATES = 0;
const LAYER_CONGRESS = 0;
const LAYER_UPPER = 1;
const LAYER_LOWER = 2;
const TICK = 500;

const semimajorAxis = 6378137.0; // WGS84 spheriod semimajor axis

var censusStates, censusCongress, censusStateUpper, censusStateLower;
var stateForIndex;

function getCensusURL(map, layer, feature) {
	let url = BASE_URL + map + "/MapServer";
	if (layer != null)
		url += "/" + layer;
	if (feature != null)
		url += "/" + feature;
	url += "/?f=json&key=" + keys.API_KEYS.census;

	console.log(url);
	return url;
}

fromLatLngToPoint = function (geo) {
	if (Number.isNaN(geo.lng) || Number.isNaN(geo.lat))
		throw new Error("Lng/Lat undefined");
	if (Math.abs(geo.lng) > 180 || Math.abs(geo.lat) > 90)
		throw new Error("Lng/Lat our of range");

	let x = geo.lng * Math.PI / 180; // degrees to radians
	let y = geo.lat * Math.PI / 180;

	y = .5 * Math.log((1.0 + Math.sin(y)) / (1.0 - Math.sin(y))); // to
	// mercator

	x = semimajorAxis * x; // to meters
	y = semimajorAxis * y;

	return { "x": x, "y": y };
};

fromPointToLatLng = function (point) {
	let lng = point.x / semimajorAxis; // from meters to radians
	let lat = point.y / semimajorAxis; // from meters to mercator

	lat = Math.atan(Math.sinh(lat)); // to radians

	lng = lng * 180 / Math.PI; // to degrees
	lat = lat * 180 / Math.PI;

	return { "lat": lat, "lng": lng };
};

function inBox(point, box) {
	if (point.x < box.minx)
		return false;
	if (point.x > box.maxx)
		return false;
	if (point.y < box.miny)
		return false;
	if (point.y > box.maxy)
		return false;
	return true;
}

function inRings(point, rings) {
	let wind = 0;

	for (r = 0; r < rings.length; ++r) {
		let ring = rings[r];
		for (v = 0; v < ring.length - 1; ++v) {
			if (ring[v][1] <= point.y) {
				if (ring[v + 1][1] > point.y)
					if ((ring[v][0] - point.x) * (ring[v + 1][1] - point.y) > (ring[v][1] - point.y) * (ring[v + 1][0] - point.x))
						wind++;
			} else {
				if (ring[v + 1][1] <= point.y)
					if ((ring[v][0] - point.x) * (ring[v + 1][1] - point.y) < (ring[v][1] - point.y) * (ring[v + 1][0] - point.x))
						wind--;
			}
		}
	}

	return wind != 0;
}

function isInside(point, geometry) {
	if (inBox(point, geometry.box))
		if (inRings(point, geometry.rings))
			return true;

	return false;
}

function readMaps(map, layer, name, array, callback) {
	let featureCount = 1;
	function read() {
		https.get(getCensusURL(map, layer, featureCount), (res) => {
			const statusCode = res.statusCode;
			if (statusCode !== 200) {
				res.resume();
				throw new Error("Request failed " + statusCode);
			}

			let data = new Array();
			res.setEncoding('utf8');
			res.on('data', (chunk) => {
				data.push(chunk);
			});
			res.on('end', () => {
				let parsedData = JSON.parse(data.join(""));
				let feature = parsedData.feature;
				if (feature) {
					if (stateForIndex)
						console.log("%s %s", stateForIndex[Number.parseInt(feature.attributes["STATE"])].attributes["NAME"], feature.attributes["NAME"]);
					else
						console.log(feature.attributes["NAME"]);
					array.push(feature);
					++featureCount;
					setTimeout(read, TICK);
				} else {
					setImmediate(io.writeArray, name, array, callback);
				}
			})
		}).on('error', (err) => {
			throw err;
		});
	}
	read();
}

function avoidDateLine(rings) {
	for (r = 0; r < rings.length; ++r) {
		let ring = rings[r];
		for (let v = 0; v < ring.length; ++v)
			if (ring[v][0] > 0)
				ring[v][0] -= 2 * Math.PI * semimajorAxis;
	}
}

function boundingBox(rings) {
	let minx = miny = Number.POSITIVE_INFINITY;
	let maxx = maxy = Number.NEGATIVE_INFINITY;
	for (r = 0; r < rings.length; ++r) {
		let ring = rings[r];
		for (let v = 1; v < ring.length; ++v) {
			let x = ring[v][0];
			let y = ring[v][1];
			if (x < minx)
				minx = x;
			if (x > maxx)
				maxx = x;
			if (y < miny)
				miny = y;
			if (y > maxy)
				maxy = y;
		}
	}
	return { "minx": minx, "miny": miny, "maxx": maxx, "maxy": maxy };
}

function simplifyBoundary(rings, tol) {
	let simple = new Array();

	for (r in rings) {
		let ring = rings[r];
		let shape = simplify(ring, tol, false);
		for (let v in shape) {
			let x = { x: shape[v][0], y: shape[v][1] };
			let y = fromPointToLatLng(x);
			shape[v] = [y.lng, y.lat];
		}
		simple.push(shape);
	}

	return simple;
}

var bootstrap_finished = () => {
	console.log("bootstrap census");
};

function bootstrap() {
	if (!censusStates) {
		censusStates = new Array();
		io.readArray("states", censusStates, (result, err) => {
			if (result > 0) {
				console.log("census " + result + " states");
				setImmediate(bootstrap);
			}
			else
				readMaps(MAP_STATES, LAYER_STATES, "states", censusStates, bootstrap);
		});
		return;
	};

	if (!stateForIndex) {
		stateForIndex = new Array();
		censusStates.forEach((state, index, states) => {
			avoidDateLine(state.geometry.rings);
			state.geometry.box = boundingBox(state.geometry.rings);
			simple = simplifyBoundary(state.geometry.rings, 2000);
			state.simplified = {
				rings: simple,
				box: boundingBox(simple),
			}
			let stateIndex = Number.parseInt(state.attributes['STATE']);
			stateForIndex[stateIndex] = state;
			stateForIndex[state.attributes["STUSAB"]] = state;
			stateForIndex[state.attributes["NAME"]] = state;
			state.congressional = new Array();
			state.upperHouse = new Array();
			state.lowerHouse = new Array();
		});
	}

	if (!censusCongress) {
		censusCongress = new Array();
		io.readArray("congress", censusCongress, (result) => {
			if (result > 0) {
				console.log("census " + result + " congress");
				setImmediate(bootstrap);
			}
			else
				readMaps(MAP_LEGISLATIVE, LAYER_CONGRESS, "congress", censusCongress, bootstrap);
		});
		return;
	};

	if (!censusStateUpper) {
		censusStateUpper = new Array();
		io.readArray("upper-house", censusStateUpper, (result) => {
			if (result > 0) {
				console.log("census " + result + " upper house");
				setImmediate(bootstrap);
			}
			else
				readMaps(MAP_LEGISLATIVE, LAYER_UPPER, "upper-house", censusStateUpper, bootstrap);
		});
		return;
	};

	if (!censusStateLower) {
		censusStateLower = new Array();
		io.readArray("lower-house", censusStateLower, (result) => {
			if (result > 0) {
				console.log("census " + result + " lower house");
				setImmediate(bootstrap);
			}
			else
				readMaps(MAP_LEGISLATIVE, LAYER_LOWER, "lower-house", censusStateLower, bootstrap);
		});
		return;
	};

	censusStates.forEach( (state) => {
		avoidDateLine(state.geometry.rings);
		state.geometry.box = boundingBox(state.geometry.rings);
		simple = simplifyBoundary(state.geometry.rings, 2000);
		state.simplified = {
			rings: simple,
			box: boundingBox(simple),
		}
		let stateIndex = Number.parseInt(state.attributes['STATE']);
	});
	
	censusCongress.forEach((district) => {
		avoidDateLine(district.geometry.rings);
		district.geometry.box = boundingBox(district.geometry.rings);
		simple = simplifyBoundary(district.geometry.rings, 1000);
		district.simplified = {
			rings: simple,
			box: boundingBox(simple),
		}
		let stateIndex = Number.parseInt(district.attributes['STATE']);
		let state = stateForIndex[stateIndex];
		let name = district.attributes['BASENAME'];
		state.congressional[name] = district;
	});

	censusStateUpper.forEach((district) => {
		avoidDateLine(district.geometry.rings);
		district.geometry.box = boundingBox(district.geometry.rings);
		simple = simplifyBoundary(district.geometry.rings, 1000);
		district.simplified = {
			rings: simple,
			box: boundingBox(simple),
		}
		let stateIndex = Number.parseInt(district.attributes['STATE']);
		let state = stateForIndex[stateIndex];
		let name = district.attributes['BASENAME'];
		state.upperHouse[name] = district;
	});

	censusStateLower.forEach((district) => {
		avoidDateLine(district.geometry.rings);
		district.geometry.box = boundingBox(district.geometry.rings);
		simple = simplifyBoundary(district.geometry.rings, 1000);
		district.simplified = {
			rings: simple,
			box: boundingBox(simple),
		}
		let stateIndex = Number.parseInt(district.attributes['STATE']);
		let state = stateForIndex[stateIndex];
		let name = district.attributes['BASENAME'];
		state.lowerHouse[name] = district;
	});

	setImmediate(bootstrap_finished);
}

exports.semimajorAxis;

exports.fromLatLngToPoint = (geo) => {
	return fromLatLngToPoint(geo);
}

exports.fromPointToLatLng = (point) => {
	return fromPointToLatLng(point);
}

exports.states = () => {
	return censusStates;
}

exports.state4 = ( index ) => {
	return stateForIndex[ index ];
}

exports.isInside = (point, feature) => {
	return isInside(point, feature.geometry);
}

exports.bootstrap = (callback) => {
	bootstrap_finished = callback;

	bootstrap();
}
