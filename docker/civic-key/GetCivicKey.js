/**
 * 
 */

const http = require('http');
const url = require('url');

const maps = require("./GetCensusMaps.js");
const os = require("./GetOpenStates.js");
const cong = require("./GetCongress.js");
const simplify = require("./simplify.js");

const semimajorAxis = 6378137.0; // WGS84 spheriod semimajor axis

var states;
var state4 = new Array();
var congress;
var congress4 = new Array();
var lower;
var lower4 = new Array();
var upper;
var upper4 = new Array();

const keys = require('./KEYS.js');

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

function avoidDateLine(rings) {
	for (r = 0; r < rings.length; ++r) {
		ring = rings[r];
		for (v = 0; v < ring.length; ++v)
			if (ring[v][0] > 0)
				ring[v][0] -= 2 * Math.PI * semimajorAxis;
	}
}

function boundingBoxes(array) {
	for (i = 0; i < array.length; ++i) {
		feature = array[i];

		let rings = feature.geometry.rings;
		let minx = miny = Number.POSITIVE_INFINITY;
		let maxx = maxy = Number.NEGATIVE_INFINITY;
		for (r = 0; r < rings.length; ++r) {
			let ring = rings[r];
			for (v = 1; v < ring.length; ++v) {
				let x = ring[v][0];
				if (x > 0) {
					x -= 2 * Math.PI * semimajorAxis; // Alaska crosses the date line!
					ring[v][0] = x;
				}
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
		feature.boundingBox = { "minx": minx, "miny": miny, "maxx": maxx, "maxy": maxy };
	}
}

function simplifyBoundary(feature, tol) {
	let rings = feature.geometry.rings;
	let simple = new Array();

	let minx = miny = Number.POSITIVE_INFINITY;
	let maxx = maxy = Number.NEGATIVE_INFINITY;
	for (r in rings) {
		let ring = rings[r];
		let shape = simplify(ring, tol, false);
		for (v in shape) {
			let x = { x: shape[v][0], y: shape[v][1] };
			let y = fromPointToLatLng(x);
			shape[v] = [y.lng, y.lat];
			if (y.lng < minx)
				minx = y.lng;
			if (y.lng > maxx)
				maxx = y.lng;
			if (y.lat < miny)
				miny = y.lat;
			if (y.lat > maxy)
				maxy = y.lat;
		}
		simple.push(shape);
	}

	feature.simpleBoundary = simple;
	feature.simpelBoundingBox = { "minx": minx, "miny": miny, "maxx": maxx, "maxy": maxy };
	return simple;
}

function simplifyBoundaries(features, tol) {
	for (f in features) {
		feature = features[f];
		if (feature.division == null)
			continue;
		if (simplifyBoundary(feature, tol)) {
			feature.division.boundary = feature.simpleBoundary;
			feature.division.boundingBox = feature.simpelBoundingBox;
		}
	}
}

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

function isInside(point, feature) {
	if (inBox(point, feature.boundingBox))
		if (inRings(point, feature.geometry.rings))
			return true;

	return false;
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

		let p = fromLatLngToPoint(q);
		if (p.x > 0)				// if we happen to be the other side of
			p.x -= 2 * Math.PI * semimajorAxis;	// the date line in Alaska
		response['p'] = p;

		for (s in states) {
			let state = states[s];
			if (isInside(p, state)) {
				response['state'] = state.attributes["NAME"];

				for (d in state.congressional) {
					let district = state.congressional[d];
					if (isInside(p, district)) {
						response['congressional_district'] = district.attributes["NAME"];
						break;
					}
				}

				for (d in state.upperHouse) {
					let district = state.upperHouse[d];
					if (isInside(p, district)) {
						response['state_upper_district'] = district.attributes["NAME"];
						response['state_upper_legislators'] = district.legislators;
						response['state_upper_boundary'] = district.os_boundary_id;
						break;
					}
				}

				for (d in state.lowerHouse) {
					let district = state.lowerHouse[d];
					if (isInside(p, district)) {
						response['state_lower_district'] = district.attributes["NAME"];
						response['state_lower_legislators'] = district.legislators;
						response['state_lower_boundary'] = district.os_boundary_id;
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

		let p = fromLatLngToPoint(q);
		if (p.x > 0)				// if we happen to be the other side of
			p.x -= 2 * Math.PI * semimajorAxis;	// the date line in Alaska
		response['p'] = p;

		let divisions = new Array();
		for (s in states) {
			let state = states[s];
			if (isInside(p, state)) {
				divisions.push(state.division);

				for (d in congress4[state.index]) {
					let district = congress4[state.index][d];
					if (isInside(p, district)) {
						divisions.push(district.division);
						break;
					}
				}

				for (d in upper4[state.index]) {
					let district = upper4[state.index][d];
					if (isInside(p, district)) {
						divisions.push(district.division);
					}
				}

				for (d in lower4[state.index]) {
					let district = lower4[state.index][d];
					if (isInside(p, district)) {
						divisions.push(district.division);
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

function linkDistrictsToStates() {
	states.forEach((state) => {
		congress4[state.index] = new Array();
		upper4[state.index] = new Array();
		lower4[state.index] = new Array();
	})
	congress.forEach((district) => {
		congress4[Number.parseInt(district.attributes["STATE"])].push(district);
	});
	upper.forEach((district) => {
		upper4[Number.parseInt(district.attributes["STATE"])].push(district);
	});
	lower.forEach((district) => {
		lower4[Number.parseInt(district.attributes["STATE"])].push(district);
	});
}

function linkCongressToCensus() {
	states.forEach((state) => {
		state4[state.attributes["STUSAB"]] = state;
		state.legislators = new Array();
		state.division = {
			government: "Federal",
			chamber: "Senate",
			state: state.attributes["NAME"],
			state_abbr: state.attributes["STUSAB"],
			type: "senate",
			id: state.attributes["STUSAB"],
			legislators: state.legislators,
		}
		state.congressional.forEach((district) => {
			district.legislators = new Array();
			district.division = {
				government: "Federal",
				chamber: "House",
				state: state.attributes["NAME"],
				state_abbr: state.attributes["STUSAB"],
				name: district.attributes["NAME"],
				type: "house",
				id: district.attributes["BASENAME"],
				legislators: district.legislators,
			}
		})
	})

	let senate = cong.senate();
	senate.members.forEach((member, index) => {
		if (!member.in_office)
			return;
		let state = state4[member.state];
		state.ocd_id = member.ocd_id;
		state.division.ocd_id = state.ocd_id;
		state.legislators.push({
			chamber: "Senate",
			id: member.id,
			full_name: member.first_name + (member.middle_name ? (' ' + member.middle_name) : '') + ' ' + member.last_name,
		});
	});

	let house = cong.house();
	house.members.forEach((member, index) => {
		if (!member.in_office)
			return;
		let state = state4[member.state];
		if (member.at_large)
			state.legislators.push({
				chamber: "House",
				id: member.id,
				full_name: member.first_name + (member.middle_name ? (' ' + member.middle_name) : '') + ' ' + member.last_name,
			});
		else {
			district = state.congressional[member.district];
			if (district) {
				district.ocd_id = member.ocd_id;
				district.division.ocd_id = district.ocd_id;
				district.legislators.push({
					chamber: "House",
					id: member.id,
					full_name: member.first_name + (member.middle_name ? (' ' + member.middle_name) : '') + ' ' + member.last_name,
				});
			}
			else
				console.log("Can't find " + member.district);
		}
	});
}

function linkOpenStatesToCensus() {
	var MAsingle = {
		"First ": "1st ",
		"Second ": "2nd ",
		"Third ": "3rd ",
		"Fourth ": "4th ",
		"Fifth ": "5th ",
		"Sixth ": "6th ",
		"Seventh ": "7th ",
		"Eighth ": "8th ",
		"Ninth ": "9th ",
		"Tenth ": "10th ",
		"Eleventh ": "11th ",
		"Twelfth ": "12th ",
		"Thirteenth ": "13th ",
		"Fourteenth ": "14th ",
		"Fifteenth ": "15th ",
		"Sixteenth ": "16th ",
		"Seventeenth ": "17th ",
		"Eighteenth ": "18th ",
		"Nineteenth ": "19th ",
		"Twentieth ": "20th "
	};
	var MAdouble = {
		"Twenty-First ": "21st ",
		"Twenty-Second ": "22nd ",
		"Twenty-Third ": "23rd ",
		"Twenty-Fourth ": "24th ",
		"Twenty-Fifth ": "25th ",
		"Twenty-Sixth ": "26th ",
		"Twenty-Seventh ": "27th ",
		"Twenty-Eighth ": "28th ",
		"Twenty-Ninth ": "29th ",
		"Thirtieth ": "30th ",
		"Thirty-First ": "31st ",
		"Thirty-Second ": "32nd ",
		"Thirty-Third ": "33rd ",
		"Thirty-Fourth ": "34th ",
		"Thirty-Fifth ": "35th ",
		"Thirty-Sixth ": "36th ",
		"Thirty-Seventh ": "37th "
	};

	function skeleton(string) {
		string = string.trim().toLowerCase();
		string = string.replace(", ", " ");
		string = string.replace("-", " ");
		string = string.replace("-", " ");
		string = string.replace(" & ", " ");
		string = string.replace(" and ", " ");
		return string;
	}

	function isDigit(char) {
		if (char < "0".charAt(0))
			return false;
		if (char > "9".charAt(0))
			return false;
		return true;
	}

	function isNumeric(string) {
		for (i = 0; i < string.length; ++i)
			if (!isDigit(string.charAt(i)))
				return false;
		return true;
	}

	let stateDistricts = os.districts();
	for (st in stateDistricts) {
		let districts = stateDistricts[st];
		for (d in districts) {
			let district = districts[d];
			let state = state4[district.abbr.toUpperCase()];
			let metadata = os.metadata(district.abbr.toUpperCase());
			let districtID = district.name;
			let cd = null;
			switch (district.chamber) {
				case "upper":
					cd = state.upperHouse[districtID];
					if (!cd && districtID == 'At-Large') {
						cd = {
							attributes: state.attributes,
							geometry: state.geometry,
						};
						upper.push(cd);
						state.upperHouse.push(cd);
					}
					if (!cd) {
						let sk = skeleton(districtID);
						for (d in state.upperHouse) {
							if (skeleton(d) == sk) {
								cd = state.upperHouse[d];
							}
						}
					}
					if (!cd && isNumeric(districtID)) {
						if (districtID.length == 1) {
							cd = state.upperHouse["0" + districtID];
						}
					}
					if (!cd && (districtID == "Chittenden-Grand Isle") || districtID == "Grand Isle") {
						cd = state.upperHouse["Grand-Isle-Chittenden"];
					}
					break;

				case "lower":
					cd = state.lowerHouse[districtID];
					if (!cd && districtID == 'At-Large') {
						cd = {
							attributes: state.attributes,
							geometry: state.geometry,
						};
						lower.push(cd);
						state.lowerHouse.push(cd);
					}
					if (!cd) {
						var k;
						switch (state.attributes['STUSAB']) {
							case "SC":
								k = districtID;
								while (k.length < 3)
									k = "0" + k;
								cd = state.lowerHouse["HD-" + k];
								break;
							case "NH":
								let p = districtID.lastIndexOf(" ");
								k = districtID.substr(0, p) + " County No. " + districtID.substr(p + 1)
								cd = state.lowerHouse[k];
								break;
							case "MN":
								k = districtID;
								while (k.length < 3)
									k = "0" + k;
								cd = state.lowerHouse[k];
								break;
							case "MA":
								k = districtID;
								for (s in MAdouble) {
									k = k.replace(s, MAdouble[s])
								}
								cd = state.lowerHouse[k];
								if (!cd) {
									for (s in MAsingle) {
										k = k.replace(s, MAsingle[s])
									}
									cd = state.lowerHouse[k];
								}
								break;
						}
					}
					if (!cd) {
						let sk = skeleton(districtID);
						for (d in state.lowerHouse) {
							if (skeleton(d) == sk) {
								cd = state.lowerHouse[d];
							}
						}
					}
					break;

				case "legislature":
					cd = state.upperHouse[districtID];
					if (!cd && districtID.startsWith("Ward ")) {
						cd = state.upperHouse[districtID.replace("Ward ", "")];
					}
					if (!cd && (districtID == 'Chairman' || districtID == 'At-Large')) {
						cd = {
							attributes: state.attributes,
							geometry: state.geometry,
						};
						upper.push(cd);
						state.upperHouse.push(cd);
					}
					break;
			}
			if (cd) {
				cd.legislators = new Array();
				cd.os_boundary_id = district.boundary_id;
				cd.os_district = district;
				cd.division = {
					government: "State",
					state: state.attributes["NAME"],
					state_abbr: state.attributes["STUSAB"],
					name: cd.attributes["NAME"],
					type: district.chamber,
					id: district.name,
					ocd_id: district.division_id,
					legislators: cd.legislators,
				};
				if (metadata.chambers[district.chamber])
					cd.division.chamber = metadata.chambers[district.chamber].name;
				else
					cd.division.chamber = district.chamber;
				district.legislators.forEach((legislator) => {
					cd.legislators.push({
						chamber: metadata.chambers[district.chamber].name,
						id: legislator.leg_id,
						full_name: legislator.full_name,
					})
				})
			}
			else
				if (!district.id.startsWith('nh-lower'))
					console.log("cant find " + district.id);;
		}
	}
}

startServer = function () {
	states = maps.states();
	congress = maps.congress();
	upper = maps.stateUpper();
	lower = maps.stateLower();

	linkDistrictsToStates();
	linkCongressToCensus();
	linkOpenStatesToCensus();

	boundingBoxes(states);
	boundingBoxes(congress);
	boundingBoxes(upper);
	boundingBoxes(lower);

	simplifyBoundaries(states, 2000);
	simplifyBoundaries(congress, 1000);
	simplifyBoundaries(upper, 1000);
	simplifyBoundaries(lower, 1000);

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
			default:
		}

		res.writeHead(200, { 'Content-Type': 'text/html' });

		let txt = "oops";

		res.end(txt);
	}).listen(8080);

	console.log("server started");
	console.log(process.memoryUsage());
}

console.log("start");

// cong.bootstrap(() => { maps.bootstrap(startServer) });
// os.bootstrap(() => { maps.bootstrap(startServer) });
//cong.bootstrap(() => { os.bootstrap(() => { maps.bootstrap(startServer) }) });
setTimeout(() => {
	cong.bootstrap(() => { os.bootstrap(() => { maps.bootstrap(startServer) }) },
		2000)
});
// startServer();
