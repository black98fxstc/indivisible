/**
 * 
 */

const http = require('http');
const url = require('url');

const maps = require("./GetCensusMaps.js");
var os = require("./GetOpenStates2.js");
var cong = require("./GetCongress.js");

const PUBLIC_STATIC_URL = 'https://static.state-strong.org/';

var states;
var boundary4 = new Array();
var division4 = new Array();

function doStateLegislature(req, res, q) {
	let response = new Object();
	try {
		let code = q['state'];
		if (!code)
			throw new Error("state missing");

		let state = maps.state4(code);
		let legislators = new Array();
		state.legislators.forEach( legislator => legislators.push(legislator) );
		state.congressional.forEach( district => {
			if (district.legislators)
				district.legislators.forEach( legislator => legislators.push(legislator) );
		});
		state.upperHouse.forEach( district => {
			if (district.legislators)
				district.legislators.forEach( legislator => legislators.push(legislator) );
		});
		state.lowerHouse.forEach( district => {
			if (district.legislators)
				district.legislators.forEach( legislator => legislators.push(legislator) );
		});

		response['politicians'] = legislators;

		response['status'] = "success";
	} catch (error) {
		response['status'] = "failure";
		response['reason'] = error.message;
	}
	res.setHeader('Access-Control-Allow-Origin', '*');
	res.writeHead(200, { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': "*" });
	res.end(JSON.stringify(response));
}

function doLookupPolitician(req, res, q) {
	let response = new Object();
	try {
		if (!(q && q.id))
			throw new Error("ocd identifier missing");
		let politician = legislator4[ q.id ];
		if (!politician)
			throw new Error("identifier not recognized");

		response['politician'] = politician;

		response['status'] = "success";
	} catch (error) {
		response['status'] = "failure";
		response['reason'] = error.message;
	}
	res.setHeader('Access-Control-Allow-Origin', '*');
	res.writeHead(200, { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': "*" });
	res.end(JSON.stringify(response));
}

function doFrontPage(req, res, q) {
	let response = new Object();
	try {
		if (!(q && q.lat && q.lng))
			throw new Error("Lng/Lat missing");
		q.lat = Number.parseFloat(q.lat);
		q.lng = Number.parseFloat(q.lng);
		response['q'] = q;
		if (Number.isNaN(q.lat) || Number.isNaN(q.y))
			throw new Error("Lng/Lat undefined");

		let p = maps.fromLatLngToPoint(q);
		if (p.x > 0)				// if we happen to be the other side of
			p.x -= 2 * Math.PI * maps.semimajorAxis;	// the date line in Alaska
		response['p'] = p;

		for (s in states) {
			let state = states[s];
			if (maps.isInside(p, state)) {
				response['state'] = state.attributes["NAME"];
				response['state_division'] = state.division.id;;
				response['state_boundary'] = state.simplified;;
 
				let federal = new Array();
				state.legislators.forEach( (senator) => {
					federal.push( senator );
				});
				
				for (d in state.congressional) {
					let district = state.congressional[d];
					if (maps.isInside(p, district)) {
						response['congressional_district'] = district.attributes["NAME"];
						response['congressional_division'] = district.division.id;;
						response['congressional_boundary'] = district.simplified;;
						district.legislators.forEach( (representative) => {
							federal.push(representative);
						})
						response['federal_legislators'   ] = federal;
						break;
					}
				}

				for (d in state.upperHouse) {
					let district = state.upperHouse[d];
					if (maps.isInside(p, district)) {
						response['state_upper_district'] = district.attributes["NAME"];
						response['state_upper_division'] = district.division.id;;						
 						response['state_upper_legislators'] = district.legislators;
						response['state_upper_boundary'] = district.simplified;;
						break;
					}
				}

				for (d in state.lowerHouse) {
					let district = state.lowerHouse[d];
					if (maps.isInside(p, district)) {
						response['state_lower_district'] = district.attributes["NAME"];
						response['state_lower_division'] = district.division.id;;						
						response['state_lower_legislators'] = district.legislators;
						response['state_lower_boundary'] = district.simplified;
						break;
					}
				}

				break;
			}
		}

		response['status'] = "success";
	} catch (error) {
		response['status'] = "failure";
		response['reason'] = error.message;
	}
	res.setHeader('Access-Control-Allow-Origin', '*');
	res.writeHead(200, { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': "*" });
	res.end(JSON.stringify(response));
}

function doLocationSearch(req, res, q) {
	let response = new Object();
	try {
		if (!(q && q.lat && q.lng))
			throw new Error("Lng/Lat missing");
		q.lat = Number.parseFloat(q.lat);
		q.lng = Number.parseFloat(q.lng);
		response['q'] = q;
		if (Number.isNaN(q.lat) || Number.isNaN(q.y))
			throw new Error("Lng/Lat undefined");

		let p = maps.fromLatLngToPoint(q);
		if (p.x > 0)				// if we happen to be the other side of
			p.x -= 2 * Math.PI * maps.semimajorAxis;	// the date line in Alaska
		response['p'] = p;

		let divisions = new Array();
		let boundaries = new Array();
		for (s in states) {
			let state = states[s];
			if (maps.isInside(p, state)) {
				divisions.push(state.division);
				boundaries.push(state.simplified);

				for (d in state.congressional) {
					let district = state.congressional[d];
					if (maps.isInside(p, district)) {
						divisions.push(district.division);
						boundaries.push(district.simplified);
						break;
					}
				}

				for (d in state.upperHouse) {
					let district = state.upperHouse[d];
					if (maps.isInside(p, district)) {
						divisions.push(district.division);
						boundaries.push(district.simplified);
					}
				}

				for (d in state.lowerHouse) {
					let district = state.lowerHouse[d];
					if (maps.isInside(p, district)) {
						divisions.push(district.division);
						boundaries.push(district.simplified);
					}
				}

				break;
			}
		}

		let politicians = new Array();
		divisions.forEach((value, index, array) => {
			if (value.legislators)
				value.legislators.forEach((legislator) => {
					if (legislator.id)
						politicians.push(legislator.id);
				});
		});

		response['divisions'] = divisions;
		if (q.boundaries != null)
			response['boundaries'] = boundaries;
		response['politicians'] = politicians;
		response['status'] = "success";
	} catch (error) {
		response['status'] = "failure";
		response['reason'] = error.message;
	}
	res.setHeader('Access-Control-Allow-Origin', '*');
	res.writeHead(200, { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': "*" });
	res.end(JSON.stringify(response));
}

function doDistrictLookup(req, res, q) {
	let response = new Object();
	try {
		if (!(q && q.district))
			throw new Error("district missing");

		let divisions = new Array();
		divisions.push(boundary4[q.district].division);

		let politicians = new Array();
		divisions.forEach((value, index, array) => {
			if (value.legislators)
				value.legislators.forEach((legislator) => {
					if (legislator.id)
						politicians.push(legislator.id);
				});
		});

		response['divisions'] = divisions;
		response['politicians'] = politicians;
		response['status'] = "success";
	} catch (error) {
		response['status'] = "failure";
		response['reason'] = error.message;
	}
	res.setHeader('Access-Control-Allow-Origin', '*');
	res.writeHead(200, { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': "*" });
	res.end(JSON.stringify(response));
}

function linkDistrictsToBoundaries() {
	states.forEach( (state) => {
		state.upperHouse.forEach( (district) => {
			let code =  (state.attributes['STUSAB'] + '-upper-' + district.attributes['BASENAME']).toLowerCase();
			boundary4[code] = district;
		})
		state.lowerHouse.forEach( (district) => {
			let code =  (state.attributes['STUSAB'] + '-lower-' + district.attributes['BASENAME']).toLowerCase();
			boundary4[code] = district;
		})
	})
}

var legislator4 = new Array();
function indexLegislators( states ) {
	states.forEach( (state) => {
		if (state.legislators)
			state.legislators.forEach( (legislator) => legislator4[ legislator.id ] = legislator );
		state.congressional.forEach( (district) => {
			if (district.legislators)
				district.legislators.forEach( (legislator) => legislator4[ legislator.id ] = legislator );
		});
		state.upperHouse.forEach( (district) => {
			if (district.legislators)
				district.legislators.forEach( (legislator) => legislator4[ legislator.id ] = legislator );
		});
		state.lowerHouse.forEach( (district) => {
			if (district.legislators)
				district.legislators.forEach( (legislator) => legislator4[ legislator.id ] = legislator );
		});
	})
}

let startServer = function () {

	linkDistrictsToBoundaries();

	http.createServer(function (req, res) {
		let req_url = url.parse(req.url, true);
		let op = req_url.pathname;
		if (op.endsWith("/"))
			op = op.substr(0, op.length - 1);
		let n = op.lastIndexOf("/");
		if (!(n < 0))
			op = op.substr(n + 1);
		switch (op) {
			case 'front-page':
				doFrontPage(req, res, req_url.query);
				return;
			case 'location-search':
				doLocationSearch(req, res, req_url.query);
				return;
			case 'district-lookup':
				doDistrictLookup(req, res, req_url.query);
				return;
			case 'lookup-politician':
				doLookupPolitician(req, res, req_url.query);
				return;
			case 'us-state-legislators':
				doStateLegislature(req, res, req_url.query);
				return;
			default:
		}

		res.writeHead(200, { 'Content-Type': 'text/html' });

		let txt = "oops";

		res.end(txt);
	}).listen(8080);

	console.log("server started");
	console.log(process.memoryUsage());
}

function kick_it () {
	setTimeout( () => {
		maps.bootstrap( () => { 
			states = maps.states();
			os.bootstrap(  maps, () => { 
				cong.bootstrap( maps, () => { 
					indexLegislators( maps.states() );
					startServer(); 
				} ) 
			} )
		},
		2000)
	});
}

console.log("start");

kick_it();

// cong.bootstrap(() => { maps.bootstrap(startServer) });
// var os = require("./GetOpenStates2.js");
// os.bootstrap(() => { maps.bootstrap(startServer) });
// var cong = require("./GetCongress.js");
// cong.bootstrap(() => { os.bootstrap(() => { maps.bootstrap(startServer) }) });
// startServer();
