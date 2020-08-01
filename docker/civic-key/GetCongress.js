/**
 * 
 */

const http = require('https');
const io = require('./IO');

const CONGRESS_NUMBER = "116";
const CONGRESS_DOCKER = "http://static/congress/";
const CONGRESS_URL = "https://static.state-strong.org/congress/";
const PUBLIC_STATIC_URL = 'https://static.state-strong.org/';

function getCongressURL (chamber)
{
	return CONGRESS_URL + CONGRESS_NUMBER + "/" + chamber + "/members.json";
}

var senate, house, states, maps;
var todo, count = 0;

function getDetails( member ) {

    http.get(CONGRESS_URL + "/members/" + member.id + ".json", (res) => {
        --count;
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
            detail = JSON.parse(data.join(""));
            if (detail.status == 'OK') {
                detail = detail.results[0];
                let subtitle = new Array();
                let role = detail.roles[0];
                if (!maps)
                    console.log('bingo');
                state = maps.state4( role.state ).attributes['NAME'];
                if (role.chamber == 'Senate')
                    subtitle.push('Senator for ' + state);
                else
                    subtitle.push(state + ' Congressional District ' + role.district);
            
                var html;
                switch (detail.current_party) {
                    case 'R':
                        html = 'Rebpulican';
                        break;
                    case 'D':
                        html = 'Democratic';
                        break;
                    default:
                        html = 'Fix Me';
                        break;
                }
                let leadership = role.leadership_role;
                if (leadership)
                    html += ', ' + leadership;
                subtitle.push(html);
            
                let lexicon = new Object();
                lexicon['bioguide_id'] = detail.id;
                const keys = [ 'times_tag', "govtrack_id", "cspan_id", "votesmart_id", 
                    "icpsr_id", "twitter_account", "facebook_account", 
                    "youtube_account", "crp_id", "google_entity_id", ];
                keys.forEach( key => {
                    if (detail[key])
                        lexicon[key] = detail[key];
                })

                let committees = new Array();
                detail.roles[0].committees.forEach( committee =>
                    committees .push( committee.name)
                );
                detail.roles[0].subcommittees.forEach( committee =>
                    committees .push( committee.name)
                );

                member.subtitle = subtitle;
                member.committees = committees;
                member.lexicon = lexicon;
                setImmediate(bootstrap);
                console.log(subtitle[0]);
            }
            else
                throw new Error("congress oops");
        })
    }).on('error', (err) => {
        console.log(err);
        setImmediate(bootstrap);
    });
}

function getSenate () {
    senate = new Array();
    io.readArray("senate", senate, (result) => {
        if (result > 0) {
            console.log("propublica " + result + " senators read");
            setImmediate(bootstrap);
        }
        else {
            http.get(getCongressURL('senate'), (res) => {
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
                        if (!todo)
                            todo = new Array();
                        senate = senate.results[0].members;
                        count += senate.length;
                        senate.forEach( member => todo.push( member) );
                        setImmediate(bootstrap);
                    }
                    else
                        throw new Error("congress oops");
                })
            }).on('error', (err) => {
                console.log(err);
                setImmediate(bootstrap);
            });
        };
    });
}

function getHouse () {
    house = new Array();
    io.readArray("house", house, (result) => {
        if (result > 0) {
            console.log("propublica " + result + " representatives read");
            setImmediate(bootstrap);
        }
        else {
            http.get(getCongressURL('house'), (res) => {
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
                        if (!todo)
                            todo = new Array();
                        house = house.results[0].members;
                        count += house.length;s
                        house.forEach( member => todo.push(member) );
                        setImmediate(bootstrap);
                    }
                    else
                        throw new Error("congress oops");
                }).on('error', (err) => {
                    console.log(err);
                    setImmediate(bootstrap);
                });
            });
        };
    });
}

function linkCongressToCensus() {

	function getcontact( member ) {
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

	senate.forEach( (member) => {
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
			contact: getcontact( member ),
            subtitle: member.subtitle,
            committees: member.committees,
			lexicon: member.lexicon,
		});
	});

	house.forEach((member, index) => {
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
				contact: getcontact( member ),
				subtitle: member.subtitle,
                committees: member.committees,
				lexicon: member.lexicon,
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
					contact: getcontact( member ),
					subtitle: member.subtitle,
                    committees: member.committees,
					lexicon: member.lexicon,
				});
			}
			else
				console.log("Can't find " + member.district);
		}
	});
}

var bootstrap_finished = () => {
	console.log("bootstrap");
};

function bootstrap() {
    if (!senate) {
        getSenate();
        return;
    }
    if (!house) {
        getHouse();
        return;
    }

    if (todo) {
        if (todo.length > 0)
            setImmediate(getDetails, todo.pop() );
        else if (count == 0) {
            console.log('propublica loaded ' + (senate.length + house.length));
            setImmediate(io.writeArray, 'senate', senate, ( count ) => {
                console.log( count + ' senators written');
                setImmediate(io.writeArray, 'house', house, (count) => { 
                    console.log( count + ' representatives written');
                    linkCongressToCensus();
                    setImmediate(bootstrap_finished); 
                    });
                }
            );
        }
        return;
    } else {
        linkCongressToCensus();
        setImmediate(bootstrap_finished); 
    }
}

exports.bootstrap = (maps_in, callback) => {
    maps = maps_in;
    states = maps.states();
    bootstrap_finished = callback;

    bootstrap();
}
