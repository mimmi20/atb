module.exports = function (ctx) {
  const root = process.cwd();

  return {
    plugins: [
      require('postcss-import')({ root: root }),
      require('postcss-color-rgba-fallback'),
      require('postcss-preset-env')({
        'stage': 2,
        'minimumVendorImplementations': 0,
        'features': {
          'any-link-pseudo-class': true,
          'cascade-layers': true,
          'clamp': {'precalculate': true},
          'color-function': true,
          'custom-media-queries': true,
          'custom-properties': true,
          'custom-selectors': true,
          'dir-pseudo-class': true,
          'display-two-values': true,
          'focus-visible-pseudo-class': true,
          'focus-within-pseudo-class': true,
          'font-format-keywords': true,
          'gap-properties': true,
          'hexadecimal-alpha-notation': true,
          'image-set-function': {'onInvalid': 'warning'},
          'is-pseudo-class': false,
          'media-query-ranges': true,
          'nested-calc': true,
          'nesting-rules': {'noIsPseudoSelector': false},
          'not-pseudo-class': true,
          'opacity-percentage': true,
          'overflow-property': true,
          'overflow-wrap-property': {'method': 'copy'},
          'rebeccapurple-color': {'preserve': false},
          'system-ui-font-family': true,
          'text-decoration-shorthand': true,
          'trigonometric-functions': true,
          'unset-value': true
        },
        'autoprefixer': {'grid': true},
        'preserve': true
      }),
      require('postcss-pxtorem')({ mediaQuery: true, propList: ['*'] }),
      require('css-declaration-sorter')({ keepOverrides: true, order: 'smacss' }),
      ctx.env === 'production'
        ? require('cssnano')({
            preset: 'default',
            safe: true,
            calc: false,
            minifyFontWeight: false,
            precision: 2,
          })
        : false,
    ],
  };
};
