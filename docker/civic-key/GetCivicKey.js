/**
 * 
 */

const http = require('http');
const url = require('url');

const maps = require("./GetCensusMaps.js");

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

const MAsingle = {
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
const MAdouble = {
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

// function uuidv4() {
//   return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
//     var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
//     return v.toString(16);
//   });
// }

crypto = require('crypto');

function jumble (byte) {
	crypto.randomFillSync(byte);
	return byte;
}
function uuidv4() {
	let byte = new Uint8Array(1);
	return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
	  (c ^ jumble(byte)[0] & 15 >> c / 4).toString(16)
	);
  }

function linkCongressToCensus( senate, house ) {

	function getContactDetails( member ) {
		let contact = new Array();
		if (member.office) contact.push( {
			'type': 'address',
			'value': member.office + '; Washington DC',
			'note': '',
			'label': 'Office',
		});
		if (member.phone) contact.push( {
			'type': 'voice',
			'value': member.phone,
			'note': '',
			'label': 'Office',
		});
		if (member.contact_form) contact.push( {
			'type': 'url',
			'value': member.contact_form,
			'note': 'Contact Form',
			'label': '',
		});
		if (member.twitter_account) contact.push( {
			'type': 'social',
			'value': member.twitter_account,
			'note': '',
			'label': 'Twitter',
		});
		if (member.facebook_account) contact.push( {
			'type': 'social',
			'value': member.facebook_account,
			'note': '',
			'label': 'Facebook',
		});
		return contact;
	}

	states.forEach( (state) => {
		state.legislators = new Array();
		state.division = {
			state: state.attributes["NAME"],
			state_abbr: state.attributes["STUSAB"],
			legislators: state.legislators,
		}
		state.congressional.forEach( (district) => {
			district.legislators = new Array();
			district.division = {
				state: state.attributes["NAME"],
				state_abbr: state.attributes["STUSAB"],
				name: district.attributes["NAME"],
				label: district.attributes["BASENAME"],
				legislators: district.legislators,
			}
		})
	})

	senate.members.forEach( (member) => {
		if (!member.in_office)
			return;
		let state = maps.state4(member.state);

		state.division.id = member.ocd_id;

		let links = new Array();
		if (member.url) links.push( {
			'url': member.url,
			'text': '',
		});

		state.legislators.push({
			government: "Federal",
			chamber: "Senate",
			type: 'upper',
			id: 'ocd-person/bioguide.congress.gov/' + member.id,
			name: member.first_name + (member.middle_name ? (' ' + member.middle_name) : '') 
				+ ' ' + member.last_name + (member.suffix      ? (' ' + member.suffix)      : ''),
			image: PUBLIC_STATIC_URL + 'theunitedstates/images/congress/450x550/' + member.id +'.jpg',
			division_id: state.division.id,
			links: links,
			contact: getContactDetails( member ),
		});
	});

	house.members.forEach((member, index) => {
		if (!member.in_office)
			return;
		let state = maps.state4(member.state);

		let links = new Array();
		if (member.url) links.push( {
			'url': member.url,
			'text': '',
		});

		if (member.at_large)
			state.legislators.push({
				government: "Federal",
				chamber: "House",
				type: 'lower',
				id: 'ocd-person/bioguide.congress.gov/' + member.id,
				name: member.first_name + (member.middle_name ? (' ' + member.middle_name) : '') 
					+ ' ' + member.last_name + (member.suffix      ? (' ' + member.suffix)      : ''),
				image: PUBLIC_STATIC_URL + 'theunitedstates/images/congress/450x550/' + member.id +'.jpg',
				division_id: state.division.id,
				links: links,
				contact: getContactDetails( member ),
		});
		else {
			let district = state.congressional[member.district];
			if (district) {
				district.division.id = member.ocd_id;
				district.legislators.push({
					government: "Federal",
					chamber: "House",
					type: 'lower',
					id: 'ocd-person/bioguide.congress.gov/' + member.id,
					name: member.first_name + (member.middle_name ? (' ' + member.middle_name) : '') 
						+ ' ' + member.last_name + (member.suffix      ? (' ' + member.suffix)      : ''),
					image: PUBLIC_STATIC_URL + 'theunitedstates/images/congress/450x550/' + member.id +'.jpg',
					division_id: member.ocd_id,
					links: links,
					contact: getContactDetails( member ),
				});
			}
			else
				console.log("Can't find " + member.district);
		}
	});

	states.forEach((state) => {
		division4[ state.division.id ] = state.district;
		state.congressional.forEach((district) => {
			division4[ district.id ] = district;
		})
	})
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

function linkOpenStatesToCensus() {
	let stateDistricts = os.districts();
	for (st in stateDistricts) {
		let districts = stateDistricts[st];
		for (d in districts) {
			let district = districts[d];
			let state = maps.state4(district.abbr.toUpperCase());
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
							simplified: state.simplified,
						};
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
							simplified: state.simplified,
						};
						state.lowerHouse.push(cd);
					}
					if (!cd) {
						var k, s;
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
							simplified: state.simplified,
						};
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
					chamber: metadata.chambers[district.chamber] ?
						metadata.chambers[district.chamber].name :
						district.chamber,
					state: state.attributes["NAME"],
					state_abbr: state.attributes["STUSAB"],
					name: cd.attributes["NAME"],
					type: district.chamber,
					id: district.name,
					ocd_id: district.division_id,
					boundary: cd.simplified.rings,
					boundingBox: cd.simplified.box,
					legislators: cd.legislators,
				};
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

function linkOpenStates2ToCensus( stateDistricts ) {
	let missing = new Array();
	stateDistricts.forEach( (district) => {
		if (district.person == null)
			return;
		let state = maps.state4(district.state);
		let districtID = district.post.label;
		let cd = null;
		switch (district.classification) {
			case "upper":
				cd = state.upperHouse[districtID];
				if (!cd && districtID == 'At-Large') {
					cd = {
						attributes: state.attributes,
						geometry: state.geometry,
						simplified: state.simplified,
					};
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
						simplified: state.simplified,
					};
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
						simplified: state.simplified,
					};
					state.upperHouse.push(cd);
				}
				break;
		}
		if (cd) {
			if (!cd.legislators) {
				cd.legislators = new Array();
				cd.os_district = district;
				cd.division = {
					government: "State",
					chamber: district.chamber,
					state: district.state,
					state_abbr: state.attributes["STUSAB"],
					name: district.name,
					label: district.label,
					type: district.classification,
					id: district.post.division.id,
					legislators: cd.legislators,
				};
			}
			cd.legislators.push({
				government: "State",
				state: district.state,
				state_abbr: state.attributes["STUSAB"],
				chamber: district.chamber,
				type: district.classification,
				id: district.person.id,
				division_id: cd.division.id,
				image: district.person.image,
				name: district.person.name,
				links: district.person.links,
				contact: district.person.contactDetails,
			})
		}
		else
			missing.push(district.state + ' ' + district.post.label);
	});
	if (missing.length > 0)
		console.log(missing.length + ' missing districts');
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

console.log("start");

// cong.bootstrap(() => { maps.bootstrap(startServer) });
// os.bootstrap(() => { maps.bootstrap(startServer) });
//cong.bootstrap(() => { os.bootstrap(() => { maps.bootstrap(startServer) }) });
setTimeout( () => {
	maps.bootstrap( () => { 
		states = maps.states();
		var os = require("./GetOpenStates2.js");
		os.bootstrap( () => { 
			linkOpenStates2ToCensus( os.districts() );
			os = null;
			var cong = require("./GetCongress.js");
			cong.bootstrap( () => { 
				linkCongressToCensus( cong.senate(), cong.house() );
				cong = null;
				indexLegislators( maps.states() );
				startServer(); 
			} ) 
		} )
	},
	2000)
});
// startServer();
