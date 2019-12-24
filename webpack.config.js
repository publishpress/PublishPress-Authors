const NODE_ENV = process.env.NODE_ENV || 'development';

var path = require('path');

module.exports = {
    mode: NODE_ENV,
    entry: './modules/multiple-authors/assets/js/coauthors-migration.jsx',
    output: {
        path: path.join(__dirname, 'modules/multiple-authors/assets/js'),
        filename: 'coauthors-migration.min.js'
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
