const zlib = require('zlib');
const fs = require('fs');
const Transform = require('stream').Transform;
const StringDecoder = require('string_decoder').StringDecoder;

const MAP_BASE = (process.argv.length > 2 ? process.argv[process.argv.length-1] : "maps/");

if (!fs.existsSync(MAP_BASE))
	fs.mkdirSync(MAP_BASE);

class LineReader extends Transform {
	constructor(options) {
		super(options);
		super.readableObjectMode = true;
		this.parts = new Array();
		this.decoder = new StringDecoder('utf8');
	}

	_transform(chunk, encoding, callback) {
		let str = this.decoder.write(chunk);
		var pos = str.indexOf("\n");
		while (pos >= 0) {
			let piece = str.substring(0, pos);
			this.parts.push(piece);

			let line = this.parts.join("");
			this.parts.length = 0;
			this.push(line);

			str = str.slice(pos + 1);
			pos = str.indexOf("\n");
		}
		if (str.length > 0) {
			this.parts.push(str);
		}
		callback();
	}

	_flush(callback) {
		if (this.parts.length > 0)
			this.push(this.parts.join(""));
		this.parts.length = 0;

		callback();
	}
}

function lines() {
	return new LineReader({ readableObjectMode: true });
}

exports.writeArray = (name, array, callback) => {
	let stream = zlib.createGzip();
	let ws = fs.createWriteStream(MAP_BASE + name + '.json.gz');
	stream.pipe(ws);
	// stream.pipe(fs.createWriteStream('maps/' + name + '.json.gz'));

    let index = 0;
    let sanitized = new Array ();
    for (var item in array)
        sanitized.push(array[item]);

    function write() {
        let ok = true;
        while (index < sanitized.length && ok)
			ok = stream.write(JSON.stringify(sanitized[index++]) + "\n");
		if (index < sanitized.length || !ok)
			stream.once('drain', write);
		else if (index == sanitized.length) {
			stream.end(finished);
		}
	}

	function finished() {
		setImmediate(callback, array.length);
	}
	write();
}

exports.readArray = (name, array, callback) => {
	let unzip = zlib.createGunzip();
	let reader = fs.createReadStream(MAP_BASE + name + '.json.gz')
		.on('error', (err) => { setImmediate(callback, -1, err) })
		.pipe(unzip)
		.on('error', (err) => { throw err })
		.pipe(lines())
		.on('data', (line) => { array.push(JSON.parse(line)) })
		.on('end', () => { setImmediate(callback, array.length) })
		.on('error', (err) => { throw err });
}
