import zlib from 'zlib';
import { defineConfig } from 'vite';
import * as path from 'path';
import viteImagemin from '@vheemstra/vite-plugin-imagemin';
import imageminJpegtran from 'imagemin-jpegtran';
import imageminPngquant from '@localnerve/imagemin-pngquant';
import imageminGif from '@localnerve/imagemin-gifsicle';
import imageminWebp from 'imagemin-webp';
import imageminGifToWebp from 'imagemin-gif2webp';
import imageminAviv from '@vheemstra/imagemin-avifenc';
import imageminSvgo from 'imagemin-svgo';
import { resolveToEsbuildTarget } from 'esbuild-plugin-browserslist';
import browserslist from 'browserslist';
import { compression } from 'vite-plugin-compression2';
import autoOrigin from 'vite-plugin-auto-origin';
import { viteStaticCopy } from 'vite-plugin-static-copy';

const target = resolveToEsbuildTarget(browserslist('defaults'), {
  printUnknownTargets: false,
});

const SvgoOpts = {
  multipass: true,
  plugins: [
    {
      name: 'preset-default',
      params: {
        overrides: {
          removeViewBox: false,
          removeComments: true,
          cleanupNumericValues: {
            floatPrecision: 2,
          },
          convertColors: {
            shortname: false,
          },
        },
      },
    },
  ],
};

const br = compression({
  algorithm: 'brotliCompress',
  deleteOriginalAssets: false,
  skipIfLargerOrEqual: false,
  compressionOptions: {
    params: {
      [zlib.constants.BROTLI_PARAM_QUALITY]: 11,
      [zlib.constants.BROTLI_PARAM_MODE]: 1,
      [zlib.constants.BROTLI_PARAM_LGWIN]: 24,
    },
  },
  include: /\.(html|css|js|cjs|mjs|svg|woff|woff2|json|jpeg|jpg|gif|png|webp|avif)$/,
});

const deflate = compression({
  algorithm: 'deflate',
  deleteOriginalAssets: false,
  skipIfLargerOrEqual: false,
  compressionOptions: {
    level: 9,
  },
  include: /\.(html|css|js|cjs|mjs|svg|woff|woff2|json|jpeg|jpg|gif|png|webp|avif)$/,
});

const gzip = compression({
  algorithm: 'gzip',
  deleteOriginalAssets: false,
  skipIfLargerOrEqual: false,
  compressionOptions: {
    level: 9,
  },
  include: /\.(html|css|js|cjs|mjs|svg|woff|woff2|json|jpeg|jpg|gif|png|webp|avif)$/,
});

const imageMin = viteImagemin({
  plugins: {
    jpg: imageminJpegtran(),
    png: imageminPngquant({
      quality: [0.8, 1],
    }),
    gif: imageminGif(),
    svg: imageminSvgo(SvgoOpts),
  },
  onlyAssets: true,
  skipIfLarger: true,
  clearCache: true,
  makeWebp: {
    plugins: {
      jpg: imageminWebp({ quality: 100 }),
      png: imageminWebp({ quality: 100 }),
      gif: imageminGifToWebp({ quality: 82 }),
    },
    skipIfLargerThan: 'optimized',
  },
  makeAvif: {
    plugins: {
      jpg: imageminAviv({ lossless: true }),
      png: imageminAviv({ lossless: true }),
    },
    skipIfLargerThan: 'optimized',
  },
});

const copy = viteStaticCopy({
  targets: [
    {
      src: 'src/assets/',
      dest: './',
    },
  ],
});

const isProduction = process.env.NODE_ENV === 'production';

export default defineConfig({
  appType: 'custom',
  root: __dirname,
  publicDir: 'public',
  base: '/dist/',
  plugins: [copy, imageMin, br, gzip, deflate, autoOrigin()],
  server: {
    host: 'localhost',
    port: 8082,
    strictPort: true,
    hmr: {
      host: 'localhost',
      clientPort: 8082,
      overlay: true,
    },
    watch: {
      usePolling: true,
    },
    origin: 'http://localhost:8082',
  },
  build: {
    target: target,
    outDir: 'public/dist', // relative to the `root` folder
    assetsDir: 'assets/',
    emptyOutDir: true,
    copyPublicDir: false,
    minify: isProduction ? 'esbuild' : false,
    sourcemap: !isProduction,
    manifest: true,
    assetsInlineLimit: 0,
    rollupOptions: {
      // https://rollupjs.org/configuration-options/
      input: [
        // general js
        path.resolve(__dirname, 'public/js/form.ts'),
        path.resolve(__dirname, 'public/js/atb.ts'),
        path.resolve(__dirname, 'public/js/dialog.ts'),
        path.resolve(__dirname, 'public/js/versbeginn.ts'),
        // glass theme
        path.resolve(__dirname, 'public/css/themes/glass.css'),
        // general css
        path.resolve(__dirname, 'public/css/404.css'),
        path.resolve(__dirname, 'public/css/help.css'),
        path.resolve(__dirname, 'public/css/styles.css'),
        path.resolve(__dirname, 'public/css/atb.css'),
        path.resolve(__dirname, 'public/css/navigation.css'),
        path.resolve(__dirname, 'public/css/accordion.css'),
        path.resolve(__dirname, 'public/css/validation.css'),
        // Bootstrap
        path.resolve(__dirname, 'node_modules/bootstrap/dist/css/bootstrap.css'),
        // path.resolve(__dirname, 'node_modules/bootstrap/dist/js/bootstrap.esm.js'),
        // Bootstrap Icons
        path.resolve(__dirname, 'node_modules/bootstrap-icons/font/bootstrap-icons.css'),
        // Themes
        path.resolve(__dirname, 'node_modules/bootswatch/dist/morph/bootstrap.css'),
        path.resolve(__dirname, 'node_modules/bootswatch/dist/quartz/bootstrap.css'),
        // path.resolve(__dirname, 'node_modules/bootswatch/dist/materia/bootstrap.css'),
      ],
      output: {
        // dir: 'public/dist',
        format: 'es',
        generatedCode: {
          arrowFunctions: true,
          constBindings: true,
        },
        strict: true,
      },
    },
    modulePreload: {
      polyfill: false,
    },
  },
  css: {
    devSourcemap: true,
    transformer: 'postcss',
    preprocessorOptions: {
      scss: {
        api: 'modern-compiler',
        outputStyle: 'expanded',
        alertAscii: true,
        alertColor: true,
        verbose: true,
      },
    },
  },
  test: {
    include: ['test/**/*.test.ts'],
    setupFiles: ['test/setup.ts'],
    clearMocks: true,

    reporters: ['default', 'junit'],
    outputFile: {
      junit: './junit-report.xml',
      html: './json-report.html',
    },

    coverage: {
      provider: 'istanbul',
      enabled: true,
      reporter: ['clover', 'text', 'html', 'lcov', 'lcovonly'],
      reportsDirectory: '.reports',
      include: ['src'],
    },
    testTimeout: 20000,
  },
});
