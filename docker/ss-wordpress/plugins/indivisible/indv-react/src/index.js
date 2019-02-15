import React from 'react';
import ReactDOM from 'react-dom';
import './index.css';
import App from './App';
//import registerServiceWorker from './registerServiceWorker';

const WPAPI = require( 'wpapi' );
console.log('window location ' + window.location.href);
var promise = WPAPI.discover( window.location.href ).catch( (reason) => {
	console.log("reason " + reason.toString() );
}).then( (result) => {
	console.log(JSON.stringify( result ) );
	console.log( result );
	ReactDOM.render(<App />, document.getElementById('root'));
});
//eslint-disable-next-line
//var wp = new WPAPI( { endpoint: window.location.toString() } );
//
//WPAPI.discover( wp.site.opeions.endpoint ).then( (site) => {
//	console.log( 'site name is: ' + site);
////	wp = new WPAPI( { endpoint: site );
//});

//registerServiceWorker();
