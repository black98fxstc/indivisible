const fs = require('fs');
const http = require('https');
const url = require('url');
const io = require('./IO');
const keys = require('./KEYS.js')

var legislatures_gql, legislatures, posts_gql;
var posts, maps, work = new Array();

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

function getPosts() {
    if (!legislatures_gql) {
        fs.readFile(
            'civic-key/legislatures.gql',
            {
                encoding: 'utf8',
            },
            (err, data) => {
                if (err) throw err;
                legislatures_gql = data;
                setImmediate(getPosts);
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
                setImmediate(getPosts);
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
                            legislature = edge2.node;
                            break;
                        case 'upper':
                            upper = edge2.node;
                            break;
                        case 'lower':
                            lower = edge2.node;
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
            setImmediate(getPosts);
        })
        return;
    }

    let legislature = work.pop();
    if (legislature) {
        console.log(legislature.state + " " + legislature.name);
        graphQuery(posts_gql, { id: legislature.id }, response => {
            response.data.organization.members.forEach(member => {
                subtitle = new Array();
                lexicon = new Object();;
                committees = new Array();
                subtitle.push(member.post.division.name);
                if (member.person) {
                    subtitle.push(member.person.party[0].organization.name);

                    member.person.committees.forEach( committee => 
                        committees.push(committee.organization.name) );

                    member.person.identifiers.forEach(mapping => {
                        if (!lexicon[mapping.scheme])
                            lexicon[mapping.scheme] = mapping.identifier;
                        else if (Array.isArray(lexicon[mapping.scheme]))
                            lexicon[mapping.scheme].push(mapping.identifier);
                        else
                            lexicon[mapping.scheme] = [ lexicon[mapping.scheme], mapping.identifier ];
                    });
                }
                posts.push({
                    id: legislature.id,
                    state: legislature.state,
                    chamber: legislature.name,
                    classification: legislature.classification,
                    person: member.person,
                    post: member.post,
                    subtitle: subtitle,
                    committees: committees,
                    lexicon: lexicon,
                })
            })
            setImmediate(getPosts);
        });
        return;
    } else {
        let n = 0;
        for (st in  posts)
            ++n;
        console.log("open states " + n + " districts fetched");
        io.writeArray("os-districts",   posts, bootstrap);
    }
}

function linkOpenStates2ToCensus( ) {
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

    let missing = new Array();
	posts.forEach( (district) => {
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
                contact: district.person.contact,
                committees: district.committees,
				subtitle: district.subtitle,
				lexicon: district.lexicon,
			})
		}
		else
			missing.push(district.state + ' ' + district.post.label);
	});
	if (missing.length > 0)
		console.log(missing.length + ' missing districts');
}

var bootstrap_finished = () => {
    console.log("bootstrap open states v2");
};

function bootstrap() {
    if (posts == null) {
        posts = new Array();
        io.readArray("os-districts",    posts, (result) => {
            if (result > 0) {
                console.log("open states " + result + " districts read");
                setImmediate(bootstrap);
            }
            else
                setImmediate(getPosts);
        });
        return;
    }
    linkOpenStates2ToCensus();
    posts = maps = null;
    setImmediate(bootstrap_finished);
}

exports.bootstrap = ( set_maps, callback) => {
    bootstrap_finished = callback;
    maps = set_maps;

    bootstrap();
}
