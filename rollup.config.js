import commonjs from '@rollup/plugin-commonjs';
import resolve from '@rollup/plugin-node-resolve';
import babel from '@rollup/plugin-babel';
import replace from '@rollup/plugin-replace';
import terser from '@rollup/plugin-terser';
import postcss from 'rollup-plugin-postcss';
import autoprefixer from 'autoprefixer';
import gzipPlugin from 'rollup-plugin-gzip';
import brotli from 'rollup-plugin-brotli';

export default [
    {
        input: 'assets/js/index.js',
        treeshake: true,  // ✅ Helps remove unused code
        output: {
            dir: 'dist',  // 🔥 Output files separately instead of a single bundle
            format: 'es',
            entryFileNames: '[name]-[hash].js',  // 🔥 Cache-busting filenames
            chunkFileNames: '[name]-[hash].js',
            sourcemap: false
        },
        context: "window",
        onwarn(warning, warn) {
            if (warning.code === 'THIS_IS_UNDEFINED') return;
            warn(warning);
        },
        plugins: [
            resolve({
                browser: true,
                moduleDirectories: ['node_modules']
            }),
            commonjs({
                include: /node_modules/,
                esmExternals: true,
                transformMixedEsModules: true
            }),
            postcss({
                plugins: [autoprefixer],
                extract: 'dist/main.min.css', // ✅ Ensure CSS is extracted
                minimize: true,
                sourceMap: true
            }),
            babel({
                babelHelpers: 'bundled',
                exclude: ['node_modules/three/**', 'node_modules/@vfx-js/core/**']
            }),
            replace({
                'process.env.NODE_ENV': JSON.stringify('production'),
                preventAssignment: true
            }),
            terser({
                format: {
                    comments: false
                },
                compress: {
                    drop_console: true,  // ✅ Removes console logs in production
                    drop_debugger: true,
                    pure_funcs: ['console.log']  // ✅ Ensures tree-shaking of logs
                }
            }),
            gzipPlugin(),  // ✅ Run compression last for minified files
            brotli()       // ✅ Same here
        ]
    },
    {
        input: 'assets/scss/main.scss', // ✅ Make sure this path is correct
        output: {
            file: 'dist/main.min.css'
        },
        plugins: [
            postcss({
                plugins: [autoprefixer],
                extract: true,  // ✅ Ensure CSS is extracted properly
                minimize: true,
                sourceMap: true
            })
        ]
    }
];
