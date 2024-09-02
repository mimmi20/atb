import { defineConfig } from 'tsup';
import * as path from 'path';
import { resolveToEsbuildTarget } from 'esbuild-plugin-browserslist';
import browserslist from 'browserslist';
import { BuildOptions } from 'esbuild';

const target = resolveToEsbuildTarget(browserslist('defaults'), {
  printUnknownTargets: false,
});

export default defineConfig((options) => {
  return {
    entry: {
      form: path.resolve(__dirname, 'public/js/form.ts'),
      atb: path.resolve(__dirname, 'public/js/atb.ts'),
      dialog: path.resolve(__dirname, 'public/js/dialog.ts'),
      versbeginn: path.resolve(__dirname, 'public/js/versbeginn.ts'),
    },
    splitting: false,
    sourcemap: options.sourcemap,
    clean: true,
    format: ['esm', 'iife'],
    outExtension({ format }) {
      return {
        js: `.${format}.js`,
      };
    },
    outDir: path.resolve(__dirname, 'public/dist'),
    target: target,
    esbuildOptions(buildOptions: BuildOptions) {
      buildOptions.entryNames = '[dir]/[name]-[hash]';
    },
    minify: !options.sourcemap,
  };
});
