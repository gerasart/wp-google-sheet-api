'use strict'; // eslint-disable-line

const webpack = require('webpack');
const merge = require('webpack-merge');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

const path = require('path');
const DotEnv = require('webpack-dotenv-plugin');
const BrowserSync = require('./webpack/browser-sync');

const WebpackBuildNotifierPlugin = require('webpack-build-notifier');
const Styles = require('./webpack/styles');
const Scripts = require('./webpack/scripts');
const CopyAssets = require('./webpack/copy-assets');
const VueLoader = require('./webpack/vue-loader');
// const FtpUpload = require('./webpack/ftp-upload');

const UglifyJsPlugin = require('uglifyjs-webpack-plugin');

const gitRoot = require('git-root');

// PATHS______________________________________________________________________________________________________________
const relPath = path.relative(gitRoot(), __dirname);
const assets = path.join(__dirname, './inc');
const PATHS = {
    assets: assets,
    dist: path.join(__dirname, './dist'),
    fonts: path.join(assets, './fonts'),
    icons: path.join(assets, './icons'),
    vue: path.join(assets, './vue'),
    relDist: path.join(relPath, './dist'),
    sep: path.sep,
    relPath
};

if (process.env.NODE_ENV === undefined) {
    // process.env.NODE_ENV = isProduction ? 'production' : 'development';
    process.env.NODE_ENV = 'development';
}

const common = merge([
    {
        entry: {
            "admin": [
                PATHS.assets + '/scripts/main.js',
                PATHS.assets + '/styles/main.scss',
            ],
            'vue': PATHS.assets + '/vue/vue.js',
        },
        output: {
            path: PATHS.dist,
            filename: 'scripts/[name].js',
            publicPath: `/${PATHS.relDist}/`,
        },
        // cache: true,
        stats: {children: false},
        resolve: {
            extensions: ['.js', '.css', '.scss', '.vue', '.json'],
            modules: [
                PATHS.assets,
                'node_modules',
            ],
            enforceExtension: false,
            alias: {
                'scss': PATHS.assets + '/styles/',
                'classes': PATHS.assets + '/scripts/classes',
                'img': PATHS.assets + '/img',
                'fonts': PATHS.assets + '/fonts',
            },
        },
        watch: true,
        watchOptions: {
            aggregateTimeout: 500, // The default
            ignored: PATHS.dist,
        },

        // PLUGINS________________________________________________________________________________________________________
        plugins: [
            // new DotEnv({
            //     sample: '',
            //     path: './.env'
            // }),
            new WebpackBuildNotifierPlugin({
                title: "Services",
                logo: path.resolve("./img/favicon.png"),
                suppressWarning: true
            }),
            new CleanWebpackPlugin({verbose: false}),
            new webpack.LoaderOptionsPlugin({
                test: /\.js$/,
                options: {
                    eslint: {
                        failOnWarning: false,
                        failOnError: true
                    },
                },
            }),
        ],
    },

    // PLUGINS__________________________________________________________________________________________________________
    CopyAssets(PATHS),

    Scripts(PATHS),
    Styles(PATHS),

    VueLoader(PATHS),


    // opn('http://wp.docker.localhost:8000'),
]);

// DEVELOPMENT / PRODUCTION __________________________________________________________________________________________
module.exports = function (env) {

    let newRel = relPath;
    if ( process.env.EXCLUDE_WPAPP ) {
        newRel = newRel.replace(`wp-app`, '');
    }

    switch (env.NODE_ENV) {
        case 'production':
            return merge([
                common,
                {
                    output: {
                        publicPath:  path.join(newRel, './dist/'),
                    },
                    optimization: {
                        concatenateModules: true,
                        noEmitOnErrors: true,
                        minimize: true,
                        minimizer: [
                            new UglifyJsPlugin({
                                extractComments: true,
                                uglifyOptions: {
                                    compress: {
                                        drop_console: true,
                                    },
                                }
                            })
                        ]
                    },
                },
                // UglifyParallel(true),
            ]);

        case 'stage':
            return merge([
                common,
                {
                    output: {
                        publicPath:  path.join(newRel, './dist/'),
                    },
                    devtool: 'source-map',
                    stats: {
                        warnings: false,
                        moduleTrace: false,
                    },
                },
                BrowserSync(PATHS.resources, env),
                // FtpUpload(PATHS, '/styles/'),
                // FtpUpload(PATHS, '/scripts/'),
                // FtpUpload(PATHS.dist + '/styles/admin.css'),
                // FtpUpload(PATHS.dist + '/scripts/admin.js'),
                // opn('http://wp.docker.localhost:8000'),
            ]);

        case 'development':
            return merge([
                common,
                {
                    devtool: 'source-map'
                },
                BrowserSync(PATHS.resources),
            ]);
    }
};