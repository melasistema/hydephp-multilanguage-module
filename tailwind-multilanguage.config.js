/**
 * This is the Tailwind CSS configuration file for the Hyde Multi Language Module package.
 *
 * @package Melasistema\HydeMultilanguageModule
 * @author  Luca Visciola
 * @copyright 2024 Luca Visciola
 * @license MIT License
 *
 * To include this configuration in your project's `tailwind.config.js`,
 * you need to require and merge it with your existing configuration.
 *
 * Example usage in `tailwind.config.js`:
 *
 * const hydeMultilanguageConfig = require('./tailwind-multilanguage.config.js');
 *
 * module.exports = {
 *     darkMode: 'class',
 *     content: [
 *         ...hydeMultilanguageConfig.content, // Merge Hyde Events Module content paths
 *     ],
 *     theme: {
 *         extend: {
 *             ...hydeMultilanguageConfig.theme.extend, // Merge the extend
 *        },
 *     },
 * };
**/

module.exports = {
    darkMode: 'class',
    content: [
        './vendor/melasistema/hyde-multilanguage-module/resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {},
    },
};
