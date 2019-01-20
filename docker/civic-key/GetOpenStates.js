/**
 * 
 */

const http = require('http');
const io = require('./IO');

const OPEN_STATES_DEBUG = "http://127.0.0.1:8082/open-states/";
const OPEN_STATES_DOCKER = "http://static/open-states/";
const OPEN_STATES_URL = "https://api.state-strong.org/open-states/";

var osMetadata;
var osStateMetadata;
var osStateDistricts;

function getOpenStatesURL (arg)
{
	return OPEN_STATES_DOCKER + arg + "/";
}

function getMetadata(callback) {
	if (osMetadata) {
		let work = new Array();
		osMetadata.forEach((state) => {
			work.push(state.abbreviation);
		});
		setImmediate(callback, work);
		return;
	}
	
	http.get(getOpenStatesURL("metadata"), (res) => {
		const statusCode = res.statusCode;
		if (statusCode !== 200) {
			res.resume();
			console.log("Request failed " + statusCode);
			setImmediate(bootstrap);
		}

		let data = new Array();
		res.setEncoding('utf8');
		res.on('data', (chunk) => {
			data.push(chunk);
		});
		res.on('end', () => {
			osMetadata = JSON.parse(data.join(""));
			setImmediate(getMetadata, callback);
		})
	}).on('error', (err) => {
		console.log(err);
		setImmediate(bootstrap);
	});
}

function getStateMetadata(work) {
	let state = work.pop();
	if (!state) {
		var n = 0;
		for (s in osStateMetadata)
			++n;
		console.log( "open states " + n + " metadata fetched" );
		io.writeArray("os-metadata", osStateMetadata, bootstrap );
		return;
	}

	http.get(getOpenStatesURL("metadata/" + state), (res) => {
		const statusCode = res.statusCode;
		if (statusCode !== 200) {
			res.resume();
			console.log("Request failed " + statusCode);
			setImmediate(bootstrap);
		}

		let data = new Array();
		res.setEncoding('utf8');
		res.on('data', (chunk) => {
			data.push(chunk);
		});
		res.on('end', () => {
			osStateMetadata[state] = JSON.parse(data.join(""));
			setImmediate(getStateMetadata, work);
		})
	}).on('error', (err) => {
		console.log(err);
		setImmediate(bootstrap);
	});
}

function getStateDistricts(work) {
	let state = work.pop();
	if (!state) {
		let n = 0;
		for (st in osStateDistricts)
			for (d in osStateDistricts[st])
				++n;
		console.log( "open states " + n + " districts fetched" );
		io.writeArray("os-districts", osStateDistricts, bootstrap);
		return;
	}

	http.get(getOpenStatesURL("districts/" + state), (res) => {
		const statusCode = res.statusCode;
		if (statusCode !== 200) {
			res.resume();
			console.log("Request failed " + statusCode);
			setImmediate(bootstrap);
		}

		let data = new Array();
		res.setEncoding('utf8');
		res.on('data', (chunk) => {
			data.push(chunk);
		});
		res.on('end', () => {
			osStateDistricts[state] = JSON.parse(data.join(""));
			setImmediate(getStateDistricts, work);
		})
	}).on('error', (err) => {
		console.log(err);
		setImmediate(bootstrap);
	});
}

var bootstrap_finished = () => {
	console.log("bootstrap open states");
};

function bootstrap() {
	if (!osStateMetadata) {
		osStateMetadata = new Array();
		io.readArray("os-metadata", osStateMetadata, (result) => {
			if (result > 0) {
				console.log( "open states " + result + " metadata read" );
				setImmediate(bootstrap);
			}
			else 
				setImmediate(getMetadata, getStateMetadata);
		});
		return;
	}

	if (!osStateDistricts) {
		osStateDistricts = new Array();
		io.readArray("os-districts", osStateDistricts, (result) => {
			if (result > 0) {
				console.log( "open states " + result + " districts read" );
				setImmediate(bootstrap);
			}
			else 
				setImmediate(getMetadata, getStateDistricts);
		});
		return;
	}

	osStateMetadata.forEach((state) => {
		osStateMetadata[state.abbreviation.toUpperCase()] = state;
	})

	setImmediate(bootstrap_finished);
}

exports.bootstrap = (callback) => {
	bootstrap_finished = callback;

	bootstrap();
}

exports.districts = () => {
	return osStateDistricts;
}

exports.metadata = (state) => {
	return osStateMetadata[state];
}