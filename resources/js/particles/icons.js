import icons from 'virtual:particle-icons';

/**
 * Swaps the "icon:<name>" sources of the themes for the bundled SVGs, with their size: without it
 * tsparticles draws every recolored SVG into a square.
 */
export function resolveParticleIcons(options) {
    if (!options) {
        return options;
    }

    return JSON.parse(JSON.stringify(options), (key, value) => {
        if (typeof value?.src !== 'string' || !value.src.startsWith('icon:')) {
            return value;
        }
        const icon = icons[value.src.slice('icon:'.length)];
        if (!icon) {
            console.warn(`Unknown particle icon ${value.src}`);

            return value;
        }

        // The dev server tags the URL with ?import&no-inline; tsparticles only recolors a source ending in "svg".
        return { ...value, src: icon.url.replace(/\?.*$/, ''), width: icon.width, height: icon.height };
    });
}
