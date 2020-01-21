/**
 * 
 */

states = [
    ['AK', 'AL', 'AR', 'AZ', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA',],
    ['HI', 'IA', 'ID', 'IL', 'IN', 'KS', 'KY', 'LA', 'MA', 'MD',],
    ['ME', 'MO', 'MI', 'MN', 'MS', 'MT', 'NB', 'NC', 'ND', 'NH',],
    ['NJ', 'NM', 'NV', 'NY', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC',],
    ['SD', 'TN', 'TX', 'UT', 'VA', 'VT', 'WA', 'WI', 'WV', 'WY',],
    ['AS', 'DC', 'PR',]
];


function indv_update($) {
    $.post(indv_ajax_obj.ajax_url, {         //POST request
        _ajax_nonce: indv_ajax_obj.ajax_nonce,     //nonce
        action: "indv_action",            //action
        title: this.value,           //data
        data: 'something',
    }, function ($data) {                    //callback'
        // jQuery("#indv_plugin_update_status").text("Boo!");
        console.log('eureka');
        if ($data != null)
            console.log(JSON.stringify($data));
    });

    let todo = new Array();
    let busy = false;
    let total = 0, done = 0, added = 0;
    function create_politician ($) {
        if (busy) return;
        let politician = todo.pop();
        if (politician) {
            busy = true;
            $.post( indv_ajax_obj.rest_url + 'wp/v2/politicians', {
                'title': politician.name,
                'status': 'publish',
                'indv_id': politician.id,
                'image': politician.image,
                'contact': politician.contact,
                'committees': politician.committees,
                'lexicon': politician.lexicon,
                'subtitle': politician.subtitle,
                'legislature': politician.government === 'State' ? politician.state : 'United States',
                'chamber_name': politician.chamber,
                '_wpnonce': indv_ajax_obj.rest_nonce,
            })
            .done( () => {
                ++done;
                ++added;
                $("#indv_plugin_update_status").html( '<h4>' + done + ' out of ' + total + (added > 0 ? ', added ' + added : '') + '</h4>');
                $("#indv_plugin_update_new").append('<tr><td>' + politician.name + "</td><td> created</td></tr>");
                $("#indv_plugin_update_progress").attr( 'max', total );
                $("#indv_plugin_update_progress").attr( 'value', done );
                console.log(politician.name + " created");
                busy = false;
                if (todo.length > 0)
                    setImmediate(create_politician, $);
            }). fail( () => {
                alert('Error');
                console.log('Error');
            });
        // } else {
        //     jQuery("#indv_plugin_update_status").html( '<h4>Complete, ' + done + ' considered' + (added > 0 ? ', added ' + added : '') + '</h4>');
        //     console.log(politician.name + " created");
        }
    }
    
    let get_federal = $('#indv_settings_federal').is(':checked');
    let get_state = $('#indv_settings_state').is(':checked');
    // jQuery("#indv_plugin_ajax_button").disable();
    jQuery("#indv_plugin_ajax_button").attr( 'disabled', true );
    jQuery("#indv_plugin_update_status").html( 'Working on it ... ');
    states.forEach(row => {
        row.forEach(state => {
            let checked = $('#indv_settings_state_' + state).is(':checked');
            if (checked) {
                $.post('http://localhost:8085/us-state-legislators?state=' + state, {},
                    function (data) {
                        data.politicians.forEach(politician => {
                            if (get_federal && politician.government == 'Federal' || get_state && politician.government == 'State') {
                                ++total;
                                let xhr = $.get( indv_ajax_obj.rest_url + 'wp/v2/politicians/&indv-id=' + politician.id )
                                .done( (data) => {
                                    if (data.length > 0) {
                                        ++done;
                                        jQuery("#indv_plugin_update_old").append('<tr><td>' + politician.name + "</td><td> exists</td></tr>");
                                        jQuery("#indv_plugin_update_progress").attr( 'max', total );
                                        jQuery("#indv_plugin_update_progress").attr( 'value', done );
                                        jQuery("#indv_plugin_update_status").html( '<h4>' + done + ' out of ' + total + (added > 0 ? ', added ' + added : '') + '</h4>');
                                        console.log(politician.name + " exists");
                                    } else {
                                        todo.push(politician);
                                        if (!busy)
                                            setImmediate(create_politician, $);
                                    }
                                })
                                .fail ( () => {
                                    alert('Error');
                                    console.log('Error');
                                } )
                            }
                        });
                    }
                )
                .always( (data) => {
                    if (done == total)
                        console.log('finished');
                });
            }
        })
    });
}