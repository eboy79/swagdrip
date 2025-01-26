import commonjs from '@rollup/plugin-commonjs';
import resolve from '@rollup/plugin-node-resolve';
import babel from '@rollup/plugin-babel';
import replace from '@rollup/plugin-replace';
import terser from '@rollup/plugin-terser';
import postcss from 'rollup-plugin-postcss';
import autoprefixer from 'autoprefixer';
import cssnano from 'cssnano';
import sass from 'sass'; // Explicitly use the sass library

export default [
  {
    input: 'assets/js/index.js',
    output: {
      file: 'dist/main.min.js',
      format: 'iife',
      sourcemap: true,
    },
    plugins: [
      commonjs(),
      resolve(),
      babel({ babelHelpers: 'bundled' }),
      replace({
        'process.env.NODE_ENV': JSON.stringify('production'),
        preventAssignment: true
      }),
      terser()
    ],
    watch: {
      clearScreen: false
    }
  },
  {
    input: 'assets/scss/main.scss',
    output: {
      file: 'dist/main.min.css',
    },
    plugins: [
      postcss({
        plugins: [
          autoprefixer,
          cssnano({
            preset: ['default', { svgo: false }] // Optimize the CSS
          })
        ],
        extract: 'dist/main.min.css',
        minimize: true,
        sourceMap: true,
        use: ['sass'], // Use sass for SCSS compilation
      })
    ]
  }
];
