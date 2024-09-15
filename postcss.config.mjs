import postcssColorRgbaFallback from 'postcss-color-rgba-fallback';
import postcssPxtorem from 'postcss-pxtorem';
import postcssPresetEnv from 'postcss-preset-env';
import cssnano from 'cssnano';
import { cssDeclarationSorter } from 'css-declaration-sorter';
import postcssUniqueSelectors from 'postcss-unique-selectors';
import postcssOrderedValues from 'postcss-ordered-values';
import postcssDiscardOverridden from 'postcss-discard-overridden';
import postcssDiscardEmpty from 'postcss-discard-empty';
import postcssDiscardDuplicates from 'postcss-discard-duplicates';
import postcssInputStyle from 'postcss-input-style';
import postcssFlexbugsFixes from 'postcss-flexbugs-fixes';
import postcssPseudoColons from 'postcss-pseudo-element-colons';
import autoprefixer from 'autoprefixer';
import postcssDiscardComments from 'postcss-discard-comments';

export default function (ctx) {
    return {
        map: ctx.options.map,
        parser: ctx.options.parser,
        plugins: [
            // @todo: fix stylelint issues first
            // stylelint({
            //     'quietDeprecationWarnings': true
            // }),
            // postcssImport({root: ctx.file.dirname}),
            postcssInputStyle,
            postcssFlexbugsFixes,
            postcssPseudoColons(
                {
                    'selectors': [
                        'before',
                        'after',
                        'first-letter',
                        'first-line'
                    ],
                    'colon-notation': 'double'
                }
            ),
            postcssPxtorem(
                {
                    propList: ['*'],
                    replace: true,
                    mediaQuery: true,
                    minPixelValue: 0
                }
            ),
            postcssPresetEnv({
                stage: 1,
                minimumVendorImplementations: 0,
                features: {
                    'all-property': {reset: 'all'}, // allows using "all" property with the "initial" keyword
                    'any-link-pseudo-class': {preserve: true}, // allows using ":any-link" pseudo class
                    'blank-pseudo-class': false, // requires js to work
                    'break-properties': true,
                    'cascade-layers': true,
                    'case-insensitive-attributes': false,
                    'clamp': {'precalculate': true}, // allows using "clamp()" function
                    'color-function': {preserve: true}, // allows using "color()" function
                    'color-functional-notation': true,
                    'color-mix': {preserve: true}, // allows using "color-mix()" function
                    'custom-media-queries': {preserve: true},
                    'custom-properties': {preserve: true},
                    'custom-selectors': {preserve: true},
                    'dir-pseudo-class': {preserve: true}, // allows using ":dir" pseudo class
                    'display-two-values': {preserve: true},
                    'double-position-gradients': {preserve: true, enableProgressiveCustomProperties: true},
                    'exponential-functions': {preserve: true},
                    'float-clear-logical-values': false,
                    'focus-visible-pseudo-class': false, // allows using ":focus-visible" pseudo class - requires js to work
                    'focus-within-pseudo-class': false, // allows using ":focus-within" pseudo class - requires js to work
                    'font-format-keywords': {preserve: true},
                    'gamut-mapping': true,
                    'gap-properties': {preserve: true},
                    'gradients-interpolation-method': {preserve: true, enableProgressiveCustomProperties: true},
                    'has-pseudo-class': false, // allows using ":has()" pseudo class - requires js to work
                    'hexadecimal-alpha-notation': {preserve: true},
                    'hwb-function': {preserve: true}, // allows using "hwb()" function
                    'ic-unit': {preserve: true}, // allows using "ic" lenth unit
                    'image-set-function': {preserve: true, onInvalid: 'warning'}, // allows using "image-set()" function
                    'is-pseudo-class': false, // allows using ":is" pseudo class - requires js to work
                    'lab-function': {preserve: true, enableProgressiveCustomProperties: true}, // allows using "lab()" function
                    'light-dark-function': {preserve: true, enableProgressiveCustomProperties: true}, // allows using "light-dark()" function
                    'logical-overflow': false,
                    'logical-overscroll-behavior': false,
                    'logical-properties-and-values': false,
                    'logical-resize': false,
                    'logical-viewport-units': false,
                    'media-queries-aspect-ratio-number-values': {preserve: true},
                    'media-query-ranges': true,
                    'nested-calc': {preserve: true},
                    'nesting-rules': {noIsPseudoSelector: false},
                    'not-pseudo-class': true, // allows using ":not" pseudo class
                    'oklab-function': {preserve: true, enableProgressiveCustomProperties: true}, // allows using "oklab()" and "oklch()" functions
                    'opacity-percentage': {preserve: true},
                    'overflow-property': {preserve: true},
                    'overflow-wrap-property': {method: 'copy'},
                    'place-properties': {preserve: true}, // lets you use place-* properties as shorthands for align-* and justify-*
                    'prefers-color-scheme-query': false, // lets you use light and dark color schemes in all browsers - requires js to work
                    'rebeccapurple-color': {preserve: false}, // use the "rebeccapurple" color
                    'relative-color-syntax': {preserve: true},
                    'scope-pseudo-class': false, // allows using ":scope()" pseudo class
                    'stepped-value-functions': {preserve: true}, // allows using "round()", "mod()" and "rem()" functions
                    'system-ui-font-family': {preserve: true},
                    'text-decoration-shorthand': {preserve: true},
                    'trigonometric-functions': {preserve: true},
                    'unset-value': {preserve: true}
                },
                autoprefixer: false,
                preserve: true,
                enableClientSidePolyfills: false,
                debug: !ctx.options.compress,
                logical: false
            }),
            postcssColorRgbaFallback,
            autoprefixer({
                add: true,
                remove: true,
                supports: true,
                grid: 'no-autoplace',
            }),
            postcssUniqueSelectors,
            postcssOrderedValues,
            postcssDiscardComments,
            postcssDiscardOverridden,
            postcssDiscardDuplicates,
            postcssDiscardEmpty,
            cssDeclarationSorter({order: 'smacss', keepOverrides: true}),
            ctx.options.compress ? cssnano({
                preset: 'default',
                safe: true,
                calc: false,
                minifyFontWeight: false,
                precision: 2
            }) : false,
        ],
    };
}
