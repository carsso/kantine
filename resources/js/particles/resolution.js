const wrapped = Symbol('resolution');

/**
 * Draws the canvas at a fraction of its CSS size and lets the browser stretch the bitmap back:
 * a quarter of the pixels to clear and paint on every frame at 0.5. The engine already scales
 * sizes, speeds and distances by retina.pixelRatio, so forcing it keeps every theme proportional.
 * Gravity is the only exception, see scaleGravity().
 */
class ResolutionPlugin {
    id = 'resolution';

    // Called on every container init, before the engine computes the pixel ratio and sizes the canvas.
    async getPlugin(container) {
        const retina = container.retina;
        if (!retina[wrapped]) {
            const init = retina.init.bind(retina);
            retina.init = () => {
                init();
                const resolution = container.actualOptions.resolution;
                const element = container.canvas.domElement;
                if (resolution === undefined || resolution === 1) {
                    return;
                }
                retina.pixelRatio = resolution;
                if (element) {
                    container.canvas.size.width = element.offsetWidth * resolution;
                    container.canvas.size.height = element.offsetHeight * resolution;
                }
            };
            retina[wrapped] = true;
        }

        return {};
    }

    loadOptions(_container, options, source) {
        if (source?.resolution !== undefined) {
            options.resolution = source.resolution;
        }
    }

    needsPlugin() {
        return false;
    }
}

export async function loadResolutionPlugin(engine) {
    await engine.pluginManager.register(e => {
        e.pluginManager.addPlugin(new ResolutionPlugin());
    });
}

const DEFAULT_GRAVITY = 9.81;

/**
 * tsparticles applies gravity in canvas pixels without the pixel ratio: scale it by hand.
 */
export function scaleGravity(options, resolution) {
    if (Array.isArray(options)) {
        options.forEach(item => scaleGravity(item, resolution));
    } else if (options && typeof options === 'object') {
        for (const [key, value] of Object.entries(options)) {
            if (key === 'gravity' && value?.enable) {
                value.acceleration = (value.acceleration ?? DEFAULT_GRAVITY) * resolution;
            } else {
                scaleGravity(value, resolution);
            }
        }
    }

    return options;
}
