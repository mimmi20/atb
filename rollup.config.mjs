import * as path from 'path';
import postcss from 'rollup-plugin-postcss';
import { externalAssets } from 'rollup-plugin-external-assets';

export default {
  input: [
    // general js
    // 'public/js/form.ts',
    // 'public/js/atb.ts',
    // 'public/js/dialog.ts',
    // 'public/js/versbeginn.ts',
    // glass theme
    'public/css/themes/glass.css',
    // general css
    'public/css/404.css',
    'public/css/help.css',
    'public/css/styles.css',
    'public/css/atb.css',
    'public/css/navigation.css',
    'public/css/accordion.css',
    'public/css/validation.css',
    // Bootstrap
    'node_modules/bootstrap/dist/css/bootstrap.css',
    // 'node_modules/bootstrap/dist/js/bootstrap.esm.js',
    // Bootstrap Icons
    'node_modules/bootstrap-icons/font/bootstrap-icons.css',
    // Themes
    'node_modules/bootswatch/dist/morph/bootstrap.css',
    'node_modules/bootswatch/dist/quartz/bootstrap.css',
    // 'node_modules/bootswatch/dist/materia/bootstrap.css',
  ],
  output: {
    dir: 'public/dist',
    format: 'es',
    generatedCode: {
      arrowFunctions: true,
      constBindings: true,
    },
    strict: true,
  },
  plugins: [
    externalAssets("public/dist/assets/*"),
    postcss(),
  ],
};
