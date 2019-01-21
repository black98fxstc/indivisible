const fs = require('fs');
const http = require('https');
const url = require('url');
const io = require('./IO');
const keys = require('./KEYS.js')

var legislatures_gql, legislatures;
var posts_gql, posts;
var work = new Array();

var osStateDistricts;

function graphQuery(query, variables, callback) {
    request = url.parse('https://openstates.org/graphql');
    request.method = 'POST';
    request.headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-API-KEY': keys.API_KEYS.open_states,
    };

    http.request(request, (res) => {
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
            response = JSON.parse(data.join(""));
            setImmediate(callback, response);
        })
    }).on('error', (err) => {
        console.log(err);
        setImmediate(bootstrap);
    }).end(JSON.stringify({ query, variables }));
}

function getLegislature(legislature) {
    graphQuery(posts_gql, { id: edge2.node.id }, response2 => {
        console.log(response2);
    });

}

var bootstrap_finished = () => {
    console.log("bootstrap open states v2");
};

function getStateDistricts() {
    if (!legislatures_gql) {
        fs.readFile(
            'civic-key/legislatures.gql',
            {
                encoding: 'utf8',
            },
            (err, data) => {
                if (err) throw err;
                legislatures_gql = data;
                setImmediate(getStateDistricts);
            }
        )
        return;
    }

    if (!posts_gql) {
        fs.readFile(
            'civic-key/posts.gql',
            {
                encoding: 'utf8',
            },
            (err, data) => {
                if (err) throw err;
                posts_gql = data;
                setImmediate(getStateDistricts);
            }
        )
        return;
    }

    if (!legislatures) {
        graphQuery(legislatures_gql, null, response => {
            legislatures = response.data;
            response.data.jurisdictions.edges.forEach(edge1 => {
                let state_name = edge1.node.name;
                let state_id = edge1.node.id;
                let legislature = null, upper = null, lower = null;
                edge1.node.organizations.edges.forEach(edge2 => {
                    switch (edge2.node.classification) {
                        case 'legislature':
                            legislature = {
                                id: edge2.node.id,
                                name: edge2.node.name,
                            };
                            break;
                        case 'upper':
                            upper = {
                                id: edge2.node.id,
                                name: edge2.node.name,
                            };
                            break;
                        case 'lower':
                            lower = {
                                id: edge2.node.id,
                                name: edge2.node.name,
                            };
                            break;
                    }
                })
                if (upper && lower) {
                    work.push({
                        state: state_name,
                        classification: 'upper',
                        id: upper.id,
                        name: upper.name,
                    });
                    work.push({
                        state: state_name,
                        classification: 'lower',
                        id: lower.id,
                        name: lower.name,
                    });
                } else
                    work.push({
                        state: state_name,
                        classification: 'legislature',
                        id: legislature.id,
                        name: legislature.name,
                    })
            });
            setImmediate(getStateDistricts);
        })
        return;
    }

    let legislature = work.pop();
    if (legislature) {
        graphQuery(posts_gql, { id: legislature.id }, response => {
            response.data.organization.members.forEach(member => {
                osStateDistricts.push({
                    id: member.post.division.id,
                    name: member.post.division.name,
                    state: legislature.state,
                    chamber: legislature.name,
                    classification: legislature.classification,
                    label: member.post.label,
                    role: member.post.role,
                    person: member.person,
                })
            })
            setImmediate(getStateDistricts);
        });
        return;
    } else {
        let n = 0;
        for (st in osStateDistricts)
            ++n;
        console.log("open states " + n + " districts fetched");
        io.writeArray("os-districts", osStateDistricts, bootstrap);
    }
}

function bootstrap() {
    if (!osStateDistricts) {
        osStateDistricts = new Array();
        io.readArray("os-districts", osStateDistricts, (result) => {
            if (result > 0) {
                console.log("open states " + result + " districts read");
                setImmediate(bootstrap);
            }
            else
                setImmediate(getStateDistricts);
        });
        return;
    }

    setImmediate(bootstrap_finished);
}

exports.bootstrap = (callback) => {
    bootstrap_finished = callback;

    bootstrap();
}

exports.districts = () => {
    return osStateDistricts;
};