import commonjs from '@rollup/plugin-commonjs';
import resolve from '@rollup/plugin-node-resolve';
import babel from '@rollup/plugin-babel';
import replace from '@rollup/plugin-replace';
import terser from '@rollup/plugin-terser';
import postcss from 'rollup-plugin-postcss';
import autoprefixer from 'autoprefixer';
import path from 'path';

export default [
  {
    input: 'assets/js/index.js',
    output: {
      file: 'dist/main.min.js',
      format: 'iife',
      sourcemap: true
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
    ]
  },
  {
    input: path.resolve('assets/scss/main.scss'),  // Ensure absolute path
    output: {
      file: 'dist/main.min.css'
    },
    plugins: [
      postcss({
        plugins: [autoprefixer],
        extract: true, // Ensures CSS gets written to `dist/main.min.css`
        minimize: true,
        sourceMap: true
      })
    ]
  }
];
