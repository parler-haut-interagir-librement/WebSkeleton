/*
 * The JavaScript of the site, loaded by importmap() in base.html.twig. The stylesheets, phil-ux then the site's own
 * styles/app.css, are linked from the layout, never imported here: AssetMapper turns an imported stylesheet into a
 * data: module of the import map, which the Content Security Policy (script-src 'self' and a nonce) refuses, and the
 * whole module, Stimulus and Turbo with it, would never run.
 */
import './bootstrap.js';

// "optin": no Turbo forms unless a form asks with data-turbo="true".
Turbo.config.forms.mode = 'optin';
