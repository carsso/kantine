import fs from 'node:fs';
import path from 'node:path';

const MODULE_ID = 'virtual:particle-icons';
const RESOLVED_ID = `\0${MODULE_ID}`;
const ICONS_FILE = path.resolve(import.meta.dirname, 'icons.json');

/**
 * Bundles the Font Awesome Pro SVGs listed in icons.json, the icon list ParticleThemes reads too.
 * The repo is public, so the SVGs come from node_modules at build time instead of being committed.
 */
export default function particleIcons() {
    return {
        name: 'particle-icons',

        resolveId(id) {
            return id === MODULE_ID ? RESOLVED_ID : null;
        },

        load(id) {
            if (id !== RESOLVED_ID) {
                return null;
            }
            this.addWatchFile(ICONS_FILE);
            const icons = Object.entries(JSON.parse(fs.readFileSync(ICONS_FILE, 'utf8')));

            // ?no-inline keeps a real .svg URL instead of a data URI.
            return [
                ...icons.map(([name], index) => `import icon${index} from '/node_modules/@fortawesome/fontawesome-pro/svgs/solid/${name}.svg?no-inline';`),
                'export default {',
                ...icons.map(([name, [width, height]], index) => `    ${JSON.stringify(name)}: { url: icon${index}, width: ${width}, height: ${height} },`),
                '};',
            ].join('\n');
        },
    };
}
