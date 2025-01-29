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
            resolve({
                browser: true
            }),
            postcss({
                plugins: [autoprefixer],
                inject: true, // This will inject the CSS into the bundle
                minimize: true
            }),
            babel({
                babelHelpers: 'bundled'
            }),
            replace({
                'process.env.NODE_ENV': JSON.stringify('production'),
                preventAssignment: true
            }),
            terser()
        ]
    },
    {
        input: path.resolve('assets/scss/main.scss'),
        output: {
            file: 'dist/main.min.css'
        },
        plugins: [
            postcss({
                plugins: [autoprefixer],
                extract: true,
                minimize: true,
                sourceMap: true
            })
        ]
    }
];