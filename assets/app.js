/*
 * The JavaScript of the site, loaded by importmap() in base.html.twig. The stylesheet phil-ux is linked from the
 * layout; the site's own styles follow it in styles/app.css.
 */
import './bootstrap.js';
import './styles/app.css';

// "optin": no Turbo forms unless a form asks with data-turbo="true".
Turbo.config.forms.mode = 'optin';
