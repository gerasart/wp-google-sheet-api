const ImageminPlugin    = require('imagemin-webpack-plugin').default;
const imageminMozjpeg   = require('imagemin-mozjpeg');

module.exports = function(paths) {
  return {
    module: {
      rules: [
        {
          test: /\.(jpe?g|png|gif|ico)$/,
          exclude: [paths.icons, paths.dist],
          loader: 'file-loader',
          options: {
            name: 'img/[name].[ext]',
            // name: '[name].[ext]',
            // outputPath: 'img/'
          }
        },
        {
          test: /\.(png|woff|woff2|eot|ttf|svg)(\?v=[0-9]\.[0-9]\.[0-9])?$/,
          // include: paths.fonts,
          loader: 'file-loader',
          options: {
            name: 'fonts/[name].[ext]',
            // name: '[name].[ext]',
            // publicPath: paths.assets + '/fonts/',
            // outputPath: 'fonts/'
          }
        },
      ],
    },
    plugins: [
      new ImageminPlugin({
        optipng: { optimizationLevel: 7 },
        gifsicle: { optimizationLevel: 3 },
        pngquant: { quality: '65-90', speed: 4 },
        svgo: { removeUnknownsAndDefaults: false, cleanupIDs: false },
        plugins: [imageminMozjpeg({ quality: 75 })],
        disable: process.env.NODE_ENV !== 'production',
      }),
    ],
  };
};
