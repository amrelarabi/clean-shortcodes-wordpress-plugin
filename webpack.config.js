const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = {
    mode: "production",
    entry: {
        "admin-scripts": "./assets/src/js/index.js",
        "admin-styles": "./assets/src/css/admin-styles.scss",
    },
    output: {
        path: path.resolve(__dirname, "assets/dist"),
        filename: "[name].min.js",
    },
    module: {
        rules: [
            {
                test: /\.jsx?$/,
                exclude: /node_modules/,
                use: {
                    loader: "babel-loader",
                    options: {
                        presets: ["@babel/preset-env", "@babel/preset-react"],
                    },
                },
            },
            {
                test: /\.scss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    "css-loader",
                    "sass-loader",
                ],
            },
        ],
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: "[name].min.css",
        }),
    ],
    resolve: {
        extensions: [".js", ".jsx"],
    },
};
