import path from 'path';
import { fileURLToPath } from 'url';
import { CleanWebpackPlugin } from 'clean-webpack-plugin';
import MiniCssExtractPlugin from 'mini-css-extract-plugin';
import TerserPlugin from 'terser-webpack-plugin';
import CssMinimizerPlugin from 'css-minimizer-webpack-plugin';
import WebpackWatchedGlobEntries from 'webpack-watched-glob-entries-plugin';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const NODE_ENV = process.env.NODE_ENV || 'development';

const entries = WebpackWatchedGlobEntries.getEntries([
  path.resolve(__dirname, 'ts/bundles/*.ts'),
  path.resolve(__dirname, 'scss/bundles/*.scss')
]);

const plugins = [
  new MiniCssExtractPlugin({
    filename: 'assets/css/[name].css',
    chunkFilename: 'assets/css/[name].css'
  }),
  new CleanWebpackPlugin({
    cleanOnceBeforeBuildPatterns: ['assets/fonts/*', 'assets/images/*']
  }),
  new WebpackWatchedGlobEntries()
];

export default {
  mode: NODE_ENV,
  devtool: NODE_ENV === 'development' ? 'source-map' : false,
  entry: entries,
  output: {
    path: path.resolve(__dirname, '..', '..', 'public'),
    filename: 'assets/js/[name].js',
    chunkFilename: 'assets/js/[id].js',
    publicPath: '/'
  },
  plugins,
  module: {
    rules: [
      {
        test: /\.s?[ac]ss$/,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          'sass-loader',
          'postcss-loader'
        ]
      },
      {
        test: /\.(png|jpe?g|svg|gif|webp)$/,
        use: {
          loader: 'file-loader',
          options: {
            name: 'assets/images/[name].[ext]'
          }
        }
      },
      {
        test: /\.ico$/,
        use: {
          loader: 'file-loader',
          options: {
            name: '/[name].[ext]'
          }
        }
      },
      {
        test: /\.(ttf|eot|woff|woff2)(\?v=[0-9]\.[0-9]\.[0-9])?$/,
        loader: 'file-loader',
        options: {
          name: 'assets/fonts/[name].[ext]'
        }
      },
      {
        test: /\.m?jsx?$/,
        exclude: /(node_modules|bower_components)/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env']
          }
        }
      },
      {
        test: /\.tsx?$/,
        use: 'ts-loader',
        exclude: /(node_modules|bower_components)/
      },
      {
        test: /\.json$/,
        loader: 'json-loader'
      }
    ]
  },
  optimization: {
    minimize: true,
    minimizer: [
      new TerserPlugin({ extractComments: false }),
      new CssMinimizerPlugin()
    ]
  },
  resolve: {
    extensions: ['.ts', '.js'],
    fallback: {
      fs: false,
      path: false,
      browser: false
    }
  }
};