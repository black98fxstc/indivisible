const fs = require('fs');
const http = require('https');
const url = require('url');

var legislatures_gql, legislatures;
var posts_gql, posts;

function bootstrap () {
    if (!legislatures_gql) {
        fs.readFile(
            'civic-key/legislatures.gql',
            {
                encoding: 'utf8',
            },
            ( err, data) => {
                if (err) throw err;
                legislatures_gql = data;
            }
        )
        setImmediate(bootstrap);
        return;
    }
}

bootstrap();