/**
 * 
 */

const http = require('http');
const url = require('url');
const crypto = require('crypto');
const pepper = Buffer.alloc( 16 );

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
		var civic_key;
		let divisions = new Array();
		let boundaries = new Array();
		
		if (q && q.civic_key)
			civic_key = q.civic_key;
		else if (!(q && q.lat && q.lng))
			throw new Error("Lng/Lat missing");
		else {
			q.lat = Number.parseFloat(q.lat);
			q.lng = Number.parseFloat(q.lng);
			if (Number.isNaN(q.lat) || Number.isNaN(q.y))
				throw new Error("Lng/Lat undefined");
		}
		response['q'] = q;

		if (civic_key) {
			let buffer = Buffer.from( civic_key, 'base64' );
			let salt = buffer.slice( 0, 16 );
			let encrypted = buffer.slice( salt.length );
			const decipher = crypto.createDecipheriv( 'aes-128-cbc', pepper, salt );
			let decrypted = decipher.update(encrypted);
			try {
				decrypted = Buffer.concat( [ decrypted, decipher.final() ] );
			}
			catch (error) {
				throw new Error( "Civic Key is stale" );
			}
			let true_key = new Array(decrypted.length / 2);
			let array = new ArrayBuffer( decrypted.length );
			decrypted.copy( Buffer.from( array ) );
			let view = new DataView( array );
			for (i = 0; i < true_key.length; ++i)
				true_key[i] = view.getUint16(2 * i);
						
			let state = states[true_key[0]];
			divisions.push(state.division);
			boundaries.push(state.simplified);

			let district = state.congressional[true_key[1]];
			divisions.push(district.division);
			boundaries.push(district.simplified);

			district = state.upperHouse[true_key[2]];
			divisions.push(district.division);
			boundaries.push(district.simplified);

			district = state.lowerHouse[true_key[3]];
			divisions.push(district.division);
			boundaries.push(district.simplified);
		} else {
			let p = maps.fromLatLngToPoint(q);
			if (p.x > 0)				// if we happen to be the other side of
				p.x -= 2 * Math.PI * maps.semimajorAxis;	// the date line in Alaska
			response['p'] = p;

			let true_key = new Array();
			for (s in states) {
				let state = states[s];
				if (maps.isInside(p, state)) {
					true_key.push(s);
					divisions.push(state.division);
					boundaries.push(state.simplified);

					for (d in state.congressional) {
						let district = state.congressional[d];
						if (maps.isInside(p, district)) {
							true_key.push(d);
							divisions.push(district.division);
							boundaries.push(district.simplified);
							break;
						}
					}

					for (d in state.upperHouse) {
						let district = state.upperHouse[d];
						if (maps.isInside(p, district)) {
							true_key.push(d);
							divisions.push(district.division);
							boundaries.push(district.simplified);
						}
					}

					for (d in state.lowerHouse) {
						let district = state.lowerHouse[d];
						if (maps.isInside(p, district)) {
							true_key.push(d);
							divisions.push(district.division);
							boundaries.push(district.simplified);
						}
					}

					break;
				}
			}

			let salt = Buffer.alloc( 16 );
			crypto.randomFillSync(salt, 0, 16);
			let array = new ArrayBuffer( 2 * true_key.length );
			let view = new DataView( array );
			for (i = 0; i < true_key.length; ++i)
				view.setUint16( 2 * i, true_key[i] );
			let sugar = Buffer.from( array );
			const cipher = crypto.createCipheriv('aes-128-cbc', pepper, salt);
			let encrypted = cipher.update( sugar );
			encrypted = Buffer.concat( [ encrypted, cipher.final() ] );
			array = new ArrayBuffer( salt.length + encrypted.length );
			civic_key = Buffer.from( array );
			salt.copy( civic_key, 0 );
			encrypted.copy( civic_key, salt.length );
			civic_key = civic_key.toString('base64');
		}

		let politicians = new Array();
		divisions.forEach((value, index, array) => {
			if (value.legislators)
				value.legislators.forEach((legislator) => {
					if (legislator.id)
						politicians.push(legislator.id);
				});
		});

		response['civic_key'] = civic_key;
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
	crypto.randomFillSync(pepper, 0, 16);

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


// let n = 4;
// array = new ArrayBuffer( 16 + 2 * n );
// let salt = Buffer.from( array ).slice( 0, 16);
// let sugar = Buffer.from( array, 16 );
// crypto.randomFillSync(salt, 0, 16);

// let view = new DataView( array, 16 );
// for (i = 0; i < n; ++i)
// 	view.setUint16( 2 * i, i );

// console.log(crypto.defaultCipherList);
// const cipher = crypto.createCipheriv('aes-128-cbc', new Uint8Array(pepper), new Uint8Array(salt));
// let encrypted = cipher.update( new Uint8Array(sugar) );
// encrypted = Buffer.concat( [ encrypted, cipher.final() ] );
// encrypted.copy( sugar, 0, 0 );

// let civic_key = Buffer.from( array).toString('base64');
// let buffer = Buffer.from( civic_key, 'base64' );

// salt2 = buffer.slice( 0, 16 );
// n = (buffer.length - 16) / 2;

// encrypted2 = buffer.slice( 16 );
// if (salt.compare(salt2))
// 	console.log('oops');
// if (encrypted.compare(encrypted2))
// console.log('oops');
// let decipher = crypto.createDecipheriv( 'aes-128-cbc', new Uint8Array(pepper), new Uint8Array(salt2) );
// let decrypted = decipher.update(new Uint8Array(encrypted));
// decrypted = Buffer.concat( [ decrypted, decipher.final() ] );
// decipher = crypto.createDecipheriv( 'aes-128-cbc', new Uint8Array(pepper), new Uint8Array(salt2) );
// decrypted = decipher.update(new Uint8Array(encrypted2));
// decrypted = Buffer.concat( [ decrypted, decipher.final() ] );

// array = new ArrayBuffer( 2 * n );
// sugar = Buffer.from( array );
// decrypted.copy( sugar, 0, 0 );
// view = new DataView( array );
// for (i = 0; i < n; ++i)
// 	console.log(view.getUint16(2 * i));



	// const salt = new Uint8Array( buffer.slice( 0, 16 ) );
// sugar = new Uint16Array( buffer.slice( 16 ) );
// for (index of sugar)
// 	console.log(sugar[index]);

// const buf = Buffer.alloc(16);
// let salt = crypto.randomFillSync(buf);
// console.log(salt.toString('hex'));
// let pepper = crypto.randomFillSync(buf);
// console.log(pepper.toString('hex'));
// console.log(crypto.getCiphers());
// const algorithm = 'aes-128-cbc';
// const password = 'Password used to generate key';

// const cipher = crypto.createCipheriv(algorithm, pepper, salt);
// let encrypted = cipher.update('some clear text data', 'utf8', 'hex');
// encrypted += cipher.final('hex');
// console.log(encrypted);
// const decipher = crypto.createDecipheriv(algorithm, pepper, salt);
// let decrypted = decipher.update(encrypted, 'hex', 'utf8');
// decrypted += decipher.final('utf8');
// console.log(decrypted);

console.log("start");

kick_it();

// cong.bootstrap(() => { maps.bootstrap(startServer) });
// var os = require("./GetOpenStates2.js");
// os.bootstrap(() => { maps.bootstrap(startServer) });
// var cong = require("./GetCongress.js");
// cong.bootstrap(() => { os.bootstrap(() => { maps.bootstrap(startServer) }) });
// startServer();
