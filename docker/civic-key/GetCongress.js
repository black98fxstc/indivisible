/**
 * 
 */

var http = require('http');

const CONGRESS_URL = "https://static.state-strong.org/congress/115/";
const CONGRESS_DOCKER = "http://static/congress/115/";

var senate;
var house;

function getCongress () {
    http.get(CONGRESS_DOCKER + 'senate/members.json', (res) => {
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
            senate = JSON.parse(data.join(""));
            if (senate.status == 'OK') {
                senate = senate.results[0];
                setImmediate(bootstrap);
            }
            else
                throw new Error("congress oops");
        })
    }).on('error', (err) => {
        console.log(err);
        setImmediate(bootstrap);
    });
    
    http.get(CONGRESS_URL + 'house/members.json', (res) => {
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
            house = JSON.parse(data.join(""));
            if (house.status == 'OK') {
                house = house.results[0];
                setImmediate(bootstrap);
            }
            else
                throw new Error("congress oops");
        })
    }).on('error', (err) => {
        console.log(err);
        setImmediate(bootstrap);
    });
    
}

var bootstrap_finished = () => {
	console.log("bootstrap");
};

function bootstrap () {
    if (!senate && !house)
        getCongress();
    else if (senate && house)
        setImmediate(bootstrap_finished);
}

exports.bootstrap = (callback) => {
    bootstrap_finished = callback;

    bootstrap();
}

exports.senate = () => {
    return senate;
}

exports.house = () => {
    return house;
}
