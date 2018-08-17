/**
 * 
 */

const https = require('https');
const io = require('./IO');
const keys = require('./KEYS');
const TICK = 500;

const BASE_URL = "https://tigerweb.geo.census.gov/arcgis/rest/services/";
const MAP_STATES = "TIGERweb/State_County";
const MAP_LEGISLATIVE = "TIGERweb/Legislative";

const LAYER_STATES = 0;
const LAYER_CONGRESS = 0;
const LAYER_UPPER = 1;
const LAYER_LOWER = 2;

var censusStates, censusCongress, censusStateUpper, censusStateLower;

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
					if (stateNameForIndex)
						console.log("%s %s", stateNameForIndex[Number.parseInt(feature.attributes["STATE"])], feature.attributes["NAME"]);
					else
						console.log(feature.attributes["NAME"]);
					array.push(feature);
					++featureCount;
					setTimeout(read, TICK);
				} else {
					setImmediate(writeArray, name, array, callback);
				}
			})
		}).on('error', (err) => {
			throw err;
		});
	}
	read();
}

//function avoidDateLine(rings) {
//	for (r = 0; r < rings.length; ++r) {
//		ring = rings[r];
//		for (v = 0; v < ring.length; ++v)
//			if (ring[v][0] > 0)
//				ring[v][0] -= 2 * Math.PI + semi
//	}
//
//}

var bootstrap_finished = () => {
	console.log("bootstrap census");
};

function bootstrap() {
	if (!censusStates) {
		censusStates = new Array();
		io.readArray( "states", censusStates, (result) => {
			if (result > 0) {
				console.log( "census " + result + " states" );
				setImmediate(bootstrap);
			}
			else
				readMaps(MAP_STATES, LAYER_STATES, "states", censusStates, bootstrap);
		});
		return;
	};

	if (!censusCongress) {
		censusCongress = new Array();
		io.readArray( "congress", censusCongress, (result) => {
			if (result > 0) {
				console.log( "census " + result + " congress" );
				setImmediate(bootstrap);
			}
			else
				readMaps(MAP_LEGISLATIVE, LAYER_CONGRESS, "congress", censusCongress, bootstrap);
		});
		return;
	};

	if (!censusStateUpper) {
		censusStateUpper = new Array();
		io.readArray( "upper-house", censusStateUpper, (result) => {
			if (result > 0) {
				console.log( "census " + result + " upper house" );
				setImmediate(bootstrap);
			}
			else
				readMaps(MAP_LEGISLATIVE, LAYER_UPPER, "upper-house", censusStateUpper, bootstrap);
		});
		return;
	};

	if (!censusStateLower) {
		censusStateLower = new Array();
		io.readArray( "lower-house", censusStateLower, (result) => {
			if (result > 0) {
				console.log( "census " + result + " lower house" );
				setImmediate(bootstrap);
			}
			else
				readMaps(MAP_LEGISLATIVE, LAYER_LOWER, "lower-house", censusStateLower, bootstrap);
		});
		return;
	};

	stateForIndex = new Array();
	stateNameForIndex = new Array();
	stateForCode = new Array();
	censusStates.forEach((state, index, states) => {
		let stateIndex = Number.parseInt(state.attributes['STATE']);
		state.index = stateIndex;
		stateForIndex[stateIndex] = state;
		stateNameForIndex[stateIndex] = state.attributes["NAME"];

		let stateCode = state.attributes['STUSAB'];
		stateForCode[stateCode] = state;

		state.congressional = new Array();
		state.upperHouse = new Array();
		state.lowerHouse = new Array();
	});

	censusCongress.forEach((district) => {
		let stateIndex = Number.parseInt(district.attributes['STATE']);
		let state = stateForIndex[stateIndex];
		let name = district.attributes['BASENAME'];
		state.congressional[name] = district;
	});

	censusStateUpper.forEach((district) => {
		let stateIndex = Number.parseInt(district.attributes['STATE']);
		let state = stateForIndex[stateIndex];
		let name = district.attributes['BASENAME'];
		state.upperHouse[name] = district;
	});

	censusStateLower.forEach((district) => {
		let stateIndex = Number.parseInt(district.attributes['STATE']);
		let state = stateForIndex[stateIndex];
		let name = district.attributes['BASENAME'];
		state.lowerHouse[name] = district;
	});

	setImmediate(bootstrap_finished);
}

exports.states = () => {
	return censusStates;
}

exports.bootstrap = (callback) => {
	bootstrap_finished = callback;

	bootstrap();
}

exports.congress = () => {
	return censusCongress;
}

exports.stateUpper = () => {
	return censusStateUpper;
}

exports.stateLower = () => {
	return censusStateLower;
}
