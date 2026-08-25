/**
 * The per-plugin Preline bundles publish themselves onto `window` rather than
 * exporting anything, and the package's own `global.d.ts` sits outside its
 * exports map, so it cannot be referenced. Declaring the handful of plugins
 * the app actually loads is both reachable and narrower than Preline's own
 * declaration, which types every plugin as `any`.
 *
 * Each is optional: the bundles are side-effect imports the tests never load.
 */
interface PrelinePlugin {
    /** Binds any `data-hs-*` element not already in the plugin's collection. */
    autoInit?: () => void;
}

interface Window {
    HSCollapse?: PrelinePlugin;
    HSDropdown?: PrelinePlugin;
    HSOverlay?: PrelinePlugin;
    HSTabs?: PrelinePlugin;
    HSTooltip?: PrelinePlugin;
}
