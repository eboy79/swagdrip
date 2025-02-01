// rollup.config.js

import commonjs from '@rollup/plugin-commonjs';
import resolve from '@rollup/plugin-node-resolve';
import babel from '@rollup/plugin-babel';
import replace from '@rollup/plugin-replace';
import terser from '@rollup/plugin-terser';
import postcss from 'rollup-plugin-postcss';
import autoprefixer from 'autoprefixer';
import cssnano from 'cssnano';
import sass from 'sass';

const isProduction = process.env.NODE_ENV === 'production';

const jsConfig = {
  context: 'window',
  input: 'assets/js/index.js',
  output: {
    file: 'dist/main.min.js',
    format: 'iife',
    sourcemap: true,
  },
  onwarn: (warning, warn) => {
    if (warning.message && warning.message.includes("Can't resolve original location")) {
      return;
    }
    if (warning.code === 'THIS_IS_UNDEFINED') {
      return;
    }
    warn(warning);
  },
  plugins: [
    // Process any imported CSS (e.g. Prism's CSS)
    postcss({
      extensions: ['.css'],
      include: '**/*.css',
      plugins: [autoprefixer(), cssnano({ preset: 'default' })],
      // Set extract to false if you want the CSS to be injected into the head
      // or set a filename if you want to extract it
      extract: false,
      sourceMap: true,
    }),
    resolve({
      browser: true,
      extensions: ['.js', '.jsx', '.json', '.css'],
    }),
    commonjs(),
    babel({
      babelHelpers: 'bundled',
      exclude: 'node_modules/**',
      compact: true,
    }),
    replace({
      'process.env.NODE_ENV': JSON.stringify('production'),
      preventAssignment: true,
    }),
    isProduction &&
      terser({
        format: { comments: false },
        compress: {
          drop_console: true,
          drop_debugger: true,
          pure_funcs: ['console.log'],
        },
      }),
  ],
};

// Your CSS/SCSS config remains unchanged
const cssConfig = {
  input: 'assets/scss/main.scss',
  output: {
    file: 'dist/main.min.css.js',
    format: 'es',
  },
  plugins: [
    postcss({
      extensions: ['.scss', '.css'],
      include: ['**/*.scss', '**/*.css'],
      plugins: [autoprefixer(), cssnano({ preset: 'default' })],
      extract: 'dist/main.min.css',
      minimize: true,
      sourceMap: true,
      use: [
        [
          'sass',
          {
            implementation: sass,
          },
        ],
      ],
    }),
  ],
};

export default [jsConfig, cssConfig];