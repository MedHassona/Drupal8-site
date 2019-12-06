/*********************************
 * Environment and imports
 *********************************/
const environment = JSON.stringify(process.env.npm_lifecycle_script.substr(process.env.npm_lifecycle_script.indexOf('--mode ') + '--mode '.length, process.env.npm_lifecycle_script.substr(process.env.npm_lifecycle_script.indexOf('--mode ') + '--mode '.length).search(/($|\s)/)));

const autoprefixer = require("autoprefixer");

const webpack = require("webpack");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const path = require('path');

/*********************************
 * Entry
 *********************************/
const entry = {
    "main": ["./web/themes/ailette/src/js/main.js"],
    "main": ["./web/themes/ailette/src/scss/main.scss"],
}

/*********************************
 * Module
 *********************************/
const _module = {
    rules: [
        {
            test: /\.css|sass|scss$/,
            exclude: [path.resolve(__dirname, "node_modules")],
            use: [
                {
                    loader: MiniCssExtractPlugin.loader,
                }, 
                {
                    loader: "css-loader", options: {
                        sourceMap: true
                    }
                },
                {
                    loader: "sass-loader", options: {
                        sourceMap: true
                    }
                },
                {
                    loader: "postcss-loader"
                }
            ]
        },
        {
            test: /\.(png|jpg|jpeg|gif|ico|svg)$/,
            loader: "url-loader?limit=1&name=[name].[ext]&outputPath=/images/&publicPath=/themes/ailette/dist/images/"
        },
        {
            test: /\.(woff|woff2|eot|ttf|otf)$/,
            loader: 'url-loader?limit=1&name=[name].[ext]&outputPath=/fonts/&publicPath=/themes/ailette/dist/fonts/',
        },
        {
            test: /\.js$/,
            exclude: [path.resolve(__dirname, "node_modules")],
            use: {
                loader: 'babel-loader',
                options: {
                    presets: ['@babel/preset-env']
                }
            }
        }

    ]
}

/*********************************
 * Optimization
 *********************************/
const optimization = {
    splitChunks: {
        cacheGroups: {
            commons: { test: /[\\/]node_modules[\\/]/, name: "common", chunks: "all" }
        }
    }
};

/*********************************
 * Output
 *********************************/
const output = {
    filename: "[name].bundle.js",
    path: __dirname + "/web/themes/ailette/dist/",
    pathinfo: true,
};
if (environment === '"production"') {
    output.pathinfo = false;
}
output.publicPath = "/js/";

/*********************************
 * Plugins
 *********************************/
const plugins = [
    new MiniCssExtractPlugin({
        filename: "./[name].bundle.css",
        disable: false,
        allChunks: false
    }),
    new webpack.LoaderOptionsPlugin({
        options: {
            postcss: [
                autoprefixer()
            ]
        }
    }),
    new webpack.DefinePlugin({
        "process.env": {
            "NODE_ENV": JSON.stringify(process.env.NODE_ENV)
        }
    }),
    new webpack.ProvidePlugin({
        $: 'jquery',
        jQuery: 'jquery'
    }),
];
/*********************************
 * Devtool
 *********************************/
const devtool= 'source-map';
/*********************************
 * Exports
 *********************************/
module.exports = {
    mode: 'production',
    context: __dirname,
    entry: entry,
    output: output,
    module: _module,
    optimization: optimization,
    plugins: plugins,
    devtool: devtool
}