const NODE_ENV = process.env.NODE_ENV || 'development';

let path = require('path');

module.exports = {
    mode: NODE_ENV,
    entry: {
        'coauthors-migration': './modules/multiple-authors/assets/js/coauthors-migration.jsx',
        'byline-migration': './modules/byline-migration/assets/js/byline-migration.jsx',
        'bylines-migration': './modules/bylines-migration/assets/js/bylines-migration.jsx'
    },
    output: {
        path: path.join(__dirname, 'assets/js'),
        filename: '[name].min.js'
    },
    module: {
        rules: [
            {
                test: /.jsx$/,
                exclude: /node_modules/,
                loader: 'babel-loader'
            }
        ]
    }
};
